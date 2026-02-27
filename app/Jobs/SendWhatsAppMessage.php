<?php

namespace App\Jobs;

use App\Enums\LeadActivityType;
use App\Events\MessageSent;
use App\Helpers\AuditHelper;
use App\Helpers\WebhookHelper;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use App\Services\ZApi\ZApiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Lead $lead,
        public string $message,
        public ?int $userId = null,
        public string $type = 'text',
        public ?string $mediaUrl = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ZApiServiceInterface $zapi): void
    {
        try {
            // Get WhatsApp instance for this tenant
            $instance = WhatsAppInstance::where('tenant_id', $this->lead->tenant_id)
                ->where('status', 'connected')
                ->first();

            if (! $instance) {
                throw new \Exception('No connected WhatsApp instance found for this tenant');
            }

            // Get lead's primary WhatsApp
            $phone = $this->lead->first_whats_app ?? $this->lead->first_phone;

            if (! $phone) {
                throw new \Exception('Lead has no WhatsApp or phone number');
            }

            // Check if lead opted out
            if ($this->lead->opt_out || $this->lead->do_not_contact) {
                Log::warning('Attempted to send message to opted-out lead', [
                    'lead_id' => $this->lead->id,
                    'opt_out' => $this->lead->opt_out,
                    'do_not_contact' => $this->lead->do_not_contact,
                ]);

                return;
            }

            // Create message record
            $whatsappMessage = WhatsAppMessage::create([
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'whatsapp_instance_id' => $instance->id,
                'type' => $this->type,
                'direction' => 'outgoing',
                'content' => $this->message,
                'media_url' => $this->mediaUrl,
                'status' => 'pending',
                'sent_by_user_id' => $this->userId,
            ]);

            // Log message attempt IMMEDIATELY (before anything can fail)
            AuditHelper::log(
                action: 'send_message',
                entity: 'whatsapp_message',
                entityId: $whatsappMessage->id,
                previousData: null,
                newData: [
                    'lead_id' => $this->lead->id,
                    'lead_name' => $this->lead->full_name,
                    'phone' => $phone,
                    'type' => $this->type,
                    'status' => 'pending',
                ],
                tenantId: $this->lead->tenant_id,
                userId: $this->userId
            );

            // Send message via Z-API
            if ($this->type === 'text') {
                $response = $zapi->sendMessage(
                    $instance->instance_id,
                    $phone,
                    $this->message
                );
            } else {
                $response = $zapi->sendMedia(
                    $instance->instance_id,
                    $phone,
                    $this->mediaUrl,
                    $this->message
                );
            }

            // Update message status
            $whatsappMessage->update([
                'status' => 'sent',
                'remote_message_id' => $response['messageId'] ?? null,
            ]);

            // Broadcast event for real-time update
            broadcast(new MessageSent($whatsappMessage->fresh()));

            // Register activity
            LeadActivity::create([
                'tenant_id' => $this->lead->tenant_id,
                'lead_id' => $this->lead->id,
                'type' => LeadActivityType::MessageSent,
                'description' => 'WhatsApp message sent',
                'metadata' => [
                    'message_id' => $whatsappMessage->id,
                    'type' => $this->type,
                ],
                'user_id' => $this->userId,
            ]);

            // Dispatch webhook for message sent
            WebhookHelper::dispatch('message_sent', [
                'message_id' => $whatsappMessage->id,
                'lead_id' => $this->lead->id,
                'lead_name' => $this->lead->full_name,
                'phone' => $phone,
                'content' => $this->message,
                'type' => $this->type,
                'sent_at' => $whatsappMessage->created_at->toIso8601String(),
            ], $this->lead->tenant_id);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
            ]);

            // Update message status to failed if it exists
            if (isset($whatsappMessage)) {
                $whatsappMessage->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }
}
