<?php

namespace App\Http\Controllers;

use App\Enums\Channel;
use App\Enums\LeadActivityType;
use App\Enums\LeadSource;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\SocialMessageReceived;
use App\Models\FacebookLeadForm;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MetaAccount;
use App\Models\SocialMessage;
use App\Models\Tenant;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    public function __construct(private MetaGraphApiServiceInterface $metaApi) {}

    /**
     * Verify webhook subscription from Meta (GET challenge-response)
     */
    public function verify(Request $request): Response|JsonResponse
    {
        $mode = $request->query('hub_mode');
        $verifyToken = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $result = $this->metaApi->verifyWebhook($mode ?? '', $verifyToken ?? '', $challenge ?? '');

        if ($result === null) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response($result, 200);
    }

    /**
     * Receive incoming messages from Instagram DM or Facebook Messenger
     */
    public function receive(Request $request): JsonResponse
    {
        Log::info('Meta Webhook received', [$request->method() => $request->all()]);
        // Verify X-Hub-Signature-256 header (HMAC-SHA256 of the raw payload)
        $signature = $request->header('X-Hub-Signature-256', '');
        if (! $this->metaApi->verifySignature($request->getContent(), $signature)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        try {
            $object = $request->input('object');

            // Detect channel
            $channel = match ($object) {
                'instagram' => Channel::Instagram,
                'page' => Channel::FacebookMessenger,
                default => null,
            };

            if ($channel === null) {
                return response()->json(['status' => 'skipped', 'reason' => 'unknown object: '.$object]);
            }

            $entries = $request->input('entry', []);

            foreach ($entries as $entry) {
                $entryId = $entry['id'] ?? null;

                // Resolve tenant via MetaAccount:
                // Instagram: entry.id = Instagram Business Account ID → instagram_user_id
                // Facebook Messenger: entry.id = Facebook Page ID → page_id
                $metaAccount = $channel === Channel::Instagram
                    ? MetaAccount::with('tenant')
                        ->where('instagram_user_id', $entryId)
                        ->where('type', $channel->value)
                        ->first()
                    : MetaAccount::with('tenant')
                        ->where('page_id', $entryId)
                        ->where('type', $channel->value)
                        ->first();

                if (! $metaAccount) {
                    Log::warning('Meta Webhook: No account found for entry', [
                        'entry_id' => $entryId,
                        'channel' => $channel->value,
                    ]);

                    continue;
                }

                $tenant = $metaAccount->tenant;

                // Handle leadgen changes (Facebook Lead Ads)
                foreach ($entry['changes'] ?? [] as $change) {
                    if (($change['field'] ?? '') !== 'leadgen') {
                        continue;
                    }

                    $this->handleLeadgenEvent($change['value'] ?? [], $metaAccount);
                }

                foreach ($entry['messaging'] ?? [] as $event) {
                    if (! isset($event['message'])) {
                        continue;
                    }

                    $senderId = $event['sender']['id'] ?? null;
                    $messageData = $event['message'];
                    $externalMessageId = $messageData['mid'] ?? null;
                    $text = $messageData['text'] ?? null;
                    $attachments = $messageData['attachments'] ?? [];

                    // Skip echo (messages sent by the page itself)
                    if ($messageData['is_echo'] ?? false) {
                        continue;
                    }

                    // Determine type and media URL
                    $type = MessageType::Text;
                    $mediaUrl = null;

                    if (! empty($attachments)) {
                        $attachment = $attachments[0];
                        $attachmentType = $attachment['type'] ?? 'file';
                        $mediaUrl = $attachment['payload']['url'] ?? null;

                        $type = match ($attachmentType) {
                            'image' => MessageType::Image,
                            'video' => MessageType::Video,
                            'audio' => MessageType::Audio,
                            'file' => MessageType::Document,
                            default => MessageType::Text,
                        };
                    }

                    // Use the event's Unix timestamp (milliseconds) for last_message_at
                    $messageTimestamp = isset($event['timestamp'])
                        ? Carbon::createFromTimestampMs($event['timestamp'])
                        : now();

                    // Find or create lead by sender_id
                    $lead = $this->findOrCreateLeadBySenderId($tenant, $senderId, $channel, $metaAccount->access_token);

                    // Create social message
                    $socialMessage = SocialMessage::create([
                        'tenant_id' => $tenant->id,
                        'lead_id' => $lead->id,
                        'meta_account_id' => $metaAccount->id,
                        'channel' => $channel,
                        'direction' => MessageDirection::Incoming,
                        'type' => $type,
                        'content' => $text ?? '',
                        'media_url' => $mediaUrl,
                        'status' => MessageStatus::Delivered,
                        'external_message_id' => $externalMessageId,
                        'sender_id' => $senderId,
                    ]);

                    // Update lead last message fields
                    $lead->update([
                        'last_message_at' => $messageTimestamp,
                        'last_message_channel' => $channel,
                    ]);

                    // Broadcast event for real-time updates
                    broadcast(new SocialMessageReceived($socialMessage));

                    // Register activity
                    LeadActivity::create([
                        'tenant_id' => $tenant->id,
                        'lead_id' => $lead->id,
                        'type' => LeadActivityType::MessageReceived,
                        'description' => ucfirst($channel->value).' message received',
                        'metadata' => [
                            'message_id' => $socialMessage->id,
                            'channel' => $channel->value,
                            'content_preview' => substr($text ?? '', 0, 50),
                        ],
                    ]);
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Meta Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function handleLeadgenEvent(array $value, MetaAccount $metaAccount): void
    {
        $leadgenId = (string) ($value['leadgen_id'] ?? '');
        $formId = (string) ($value['form_id'] ?? '');

        if (! $leadgenId || ! $formId) {
            return;
        }

        $form = FacebookLeadForm::withoutGlobalScopes()
            ->where('meta_account_id', $metaAccount->id)
            ->where('form_id', $formId)
            ->where('active', true)
            ->first();

        if (! $form) {
            Log::info('Lead Ads: lead received for unregistered or inactive form', [
                'form_id' => $formId,
                'meta_account_id' => $metaAccount->id,
            ]);

            return;
        }

        $leadData = $this->metaApi->getLeadData($leadgenId, $metaAccount->access_token);

        if (empty($leadData)) {
            return;
        }

        $fields = collect($leadData['field_data'] ?? [])->pluck('values.0', 'name');

        $fullName = $fields->get('full_name')
            ?? trim(($fields->get('first_name') ?? '').' '.($fields->get('last_name') ?? ''))
            ?: 'Lead sem nome';

        $existing = Lead::withoutGlobalScopes()
            ->where('tenant_id', $metaAccount->tenant_id)
            ->whereJsonContains('custom_fields->facebook_lead_id', $leadgenId)
            ->first();

        if ($existing) {
            return;
        }

        $lead = Lead::create([
            'tenant_id' => $metaAccount->tenant_id,
            'full_name' => $fullName,
            'email' => $fields->get('email') ?? $fields->get('email_address'),
            'phone' => $fields->get('phone_number') ?? $fields->get('phone'),
            'source' => LeadSource::FacebookLeadAd,
            'consent_status' => 'pending',
            'custom_fields' => [
                'facebook_lead_id' => $leadgenId,
                'facebook_form_id' => $formId,
            ],
        ]);

        LeadActivity::create([
            'tenant_id' => $metaAccount->tenant_id,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead criado via Facebook Lead Ads (formulário: '.$form->form_name.')',
        ]);
    }

    private function findOrCreateLeadBySenderId(Tenant $tenant, string $senderId, Channel $channel, string $pageAccessToken): Lead
    {
        $channelField = $channel === Channel::Instagram ? 'instagram_sender_id' : 'facebook_psid';

        // Look for lead with this sender ID in custom_fields
        $lead = Lead::where('tenant_id', $tenant->id)
            ->whereJsonContains("custom_fields->{$channelField}", $senderId)
            ->first();

        if ($lead) {
            return $lead;
        }

        // Fetch sender profile from Meta Graph API
        $profile = $this->metaApi->getSenderProfile($senderId, $pageAccessToken, $channel);
        $fullName = $profile['name'] ?? 'Unknown Contact ('.$channel->value.')';

        // Create new lead
        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'full_name' => $fullName,
            'source' => LeadSource::from($channel->value),
            'consent_status' => 'pending',
            'custom_fields' => [$channelField => $senderId],
        ]);

        LeadActivity::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead created automatically from incoming '.ucfirst($channel->value).' message',
        ]);

        return $lead;
    }
}
