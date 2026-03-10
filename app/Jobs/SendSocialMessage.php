<?php

namespace App\Jobs;

use App\Enums\Channel;
use App\Enums\LeadActivityType;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\SocialMessageSent;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MetaAccount;
use App\Models\SocialMessage;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSocialMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public Lead $lead,
        public Channel $channel,
        public string $content,
        public ?int $userId = null,
        public string $type = 'text',
        public ?string $mediaUrl = null
    ) {}

    public function handle(MetaGraphApiServiceInterface $metaApi): void
    {
        try {
            // Get MetaAccount that received an incoming message from this lead.
            // Page-scoped IDs (PSID/sender_id) are tied to the page that received them,
            // so we must reply using the same page.
            $metaAccount = MetaAccount::where('tenant_id', $this->lead->tenant_id)
                ->where('type', $this->channel->value)
                ->where('status', 'connected')
                ->whereHas('socialMessages', fn ($q) => $q->where('lead_id', $this->lead->id)->where('direction', 'incoming'))
                ->first()
                ?? MetaAccount::where('tenant_id', $this->lead->tenant_id)
                    ->where('type', $this->channel->value)
                    ->where('status', 'connected')
                    ->first();

            if (! $metaAccount) {
                throw new \Exception("No connected {$this->channel->value} account found for this tenant");
            }

            // Get recipient sender_id from lead custom_fields
            $channelField = $this->channel === Channel::Instagram ? 'instagram_sender_id' : 'facebook_psid';
            $recipientId = $this->lead->custom_fields[$channelField] ?? null;

            if (! $recipientId) {
                throw new \Exception("Lead has no {$channelField} for channel {$this->channel->value}");
            }

            // Check if lead opted out
            if ($this->lead->opt_out || $this->lead->do_not_contact) {
                Log::warning('Attempted to send social message to opted-out lead', [
                    'lead_id' => $this->lead->id,
                    'channel' => $this->channel->value,
                ]);

                return;
            }

            // Create message record as pending
            $socialMessage = SocialMessage::create([
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'meta_account_id' => $metaAccount->id,
                'channel' => $this->channel,
                'direction' => MessageDirection::Outgoing,
                'type' => MessageType::from($this->type),
                'content' => $this->content,
                'media_url' => $this->mediaUrl,
                'status' => MessageStatus::Pending,
                'sent_by_user_id' => $this->userId,
            ]);

            // Send via Meta API
            if ($this->mediaUrl !== null) {
                $mediaType = match (MessageType::from($this->type)) {
                    MessageType::Image => 'image',
                    MessageType::Video => 'video',
                    MessageType::Audio => 'audio',
                    MessageType::Document => 'file',
                    default => 'file',
                };

                $response = $metaApi->sendMedia(
                    $metaAccount->page_id,
                    $recipientId,
                    $this->mediaUrl,
                    $mediaType,
                    $metaAccount->access_token
                );
            } elseif ($this->channel === Channel::Instagram) {
                if ($metaAccount->source === 'instagram_business_login') {
                    $response = $metaApi->sendInstagramBusinessMessage(
                        $metaAccount->instagram_user_id,
                        $recipientId,
                        $this->content,
                        $metaAccount->access_token
                    );
                } else {
                    $response = $metaApi->sendInstagramMessage(
                        $recipientId,
                        $this->content,
                        $metaAccount->access_token
                    );
                }
            } else {
                $response = $metaApi->sendFacebookMessage(
                    $metaAccount->page_id,
                    $recipientId,
                    $this->content,
                    $metaAccount->access_token
                );
            }

            if (! ($response['success'] ?? false)) {
                throw new \Exception($response['error'] ?? 'Unknown Meta API error');
            }

            // Update message status to sent
            $socialMessage->update([
                'status' => MessageStatus::Sent,
                'external_message_id' => $response['message_id'] ?? null,
            ]);

            // Update lead last message fields
            $this->lead->update([
                'last_message_at' => now(),
                'last_message_channel' => $this->channel,
            ]);

            // Broadcast event
            broadcast(new SocialMessageSent($socialMessage->fresh()));

            // Register activity
            LeadActivity::create([
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'type' => LeadActivityType::MessageSent,
                'description' => ucfirst($this->channel->value).' message sent',
                'metadata' => [
                    'message_id' => $socialMessage->id,
                    'channel' => $this->channel->value,
                    'type' => $this->type,
                ],
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            if (isset($socialMessage)) {
                $socialMessage->update([
                    'status' => MessageStatus::Failed,
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error('Failed to send social message', [
                'lead_id' => $this->lead->id,
                'channel' => $this->channel->value,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
