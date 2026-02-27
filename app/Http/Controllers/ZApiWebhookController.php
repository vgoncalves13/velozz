<?php

namespace App\Http\Controllers;

use App\Enums\LeadActivityType;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\MessageReceived;
use App\Helpers\WebhookHelper;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Tenant;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZApiWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Z-API
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Log the incoming webhook for debugging
            Log::info('Z-API Webhook received', [
                'type' => $request->input('type'),
                'phone' => $request->input('phone'),
                'fromMe' => $request->input('fromMe'),
            ]);

            // Get webhook type
            $type = $request->input('type');

            // Handle message delivery status (sent, delivered, read)
            if ($type === 'DeliveryCallback') {
                return $this->handleMessageStatus($request);
            }

            // Validate required fields for ReceivedCallback
            $request->validate([
                'instanceId' => 'required|string',
                'phone' => 'required|string',
                'fromMe' => 'required|boolean',
            ]);

            // Skip if message is from us (only for ReceivedCallback)
            if ($type === 'ReceivedCallback' && $request->input('fromMe')) {
                return response()->json(['status' => 'skipped', 'reason' => 'message from me']);
            }

            // Skip if not a received message
            if ($type !== 'ReceivedCallback') {
                return response()->json(['status' => 'skipped', 'reason' => 'unknown type: '.$type]);
            }

            // Find WhatsApp instance and tenant
            $instanceId = $request->input('instanceId');
            $instance = WhatsAppInstance::where('instance_id', $instanceId)->firstOrFail();
            $tenant = $instance->tenant;

            // Extract phone number (clean format)
            $phone = $request->input('phone');

            // Find or create lead
            $lead = $this->findOrCreateLead($tenant, $phone, $request->input('chatName'));

            // Create message
            $message = WhatsAppMessage::create([
                'tenant_id' => $tenant->id,
                'lead_id' => $lead->id,
                'whatsapp_instance_id' => $instance->id,
                'type' => $this->determineMessageType($request),
                'direction' => MessageDirection::Incoming,
                'content' => $request->input('text.message', ''),
                'media_url' => $request->input('image.imageUrl') ?? $request->input('video.videoUrl') ?? $request->input('audio.audioUrl') ?? null,
                'status' => MessageStatus::Delivered,
                'remote_message_id' => $request->input('messageId'),
            ]);

            // Broadcast event for real-time updates
            broadcast(new MessageReceived($message));

            // Register activity
            LeadActivity::create([
                'tenant_id' => $tenant->id,
                'lead_id' => $lead->id,
                'type' => LeadActivityType::MessageReceived,
                'description' => 'WhatsApp message received',
                'metadata' => [
                    'message_id' => $message->id,
                    'content_preview' => substr($message->content, 0, 50),
                ],
            ]);

            // Dispatch webhook for message received
            WebhookHelper::dispatch('message_received', [
                'message_id' => $message->id,
                'lead_id' => $lead->id,
                'lead_name' => $lead->full_name,
                'phone' => $phone,
                'content' => $message->content,
                'type' => $message->type->value,
                'received_at' => $message->created_at->toIso8601String(),
            ], $tenant->id);

            // TODO: Notify assigned operator if exists
            // if ($lead->assigned_user_id) {
            //     Notification::send($lead->assignedUser, new NewMessageNotification($message));
            // }

            return response()->json([
                'status' => 'success',
                'message_id' => $message->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Z-API Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find or create lead by phone number
     */
    private function findOrCreateLead(Tenant $tenant, string $phone, ?string $chatName = null): Lead
    {
        // Try to find lead by any whatsapp field
        $lead = Lead::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($phone) {
                $whatsapps = is_array($phone) ? $phone : [$phone];
                foreach ($whatsapps as $whatsapp) {
                    $query->orWhereJsonContains('whatsapps', $whatsapp);
                }
            })
            ->first();

        if ($lead) {
            return $lead;
        }

        // Create new lead if not found
        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'full_name' => $chatName ?: 'Unknown Contact ('.substr($phone, -4).')',
            'whatsapps' => [$phone],
            'primary_whatsapp_index' => 0,
            'source' => 'whatsapp',
            'consent_status' => 'pending',
        ]);

        LeadActivity::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead created automatically from incoming WhatsApp message',
        ]);

        return $lead;
    }

    /**
     * Determine message type from payload
     */
    private function determineMessageType(Request $request): MessageType
    {
        if ($request->has('image')) {
            return MessageType::Image;
        }

        if ($request->has('audio')) {
            return MessageType::Audio;
        }

        if ($request->has('video')) {
            return MessageType::Image; // Treat video as image for now
        }

        if ($request->has('document')) {
            return MessageType::Document;
        }

        return MessageType::Text;
    }

    /**
     * Handle message status updates (sent, delivered, read)
     * DeliveryCallback format from Z-API
     */
    private function handleMessageStatus(Request $request): JsonResponse
    {
        try {
            $messageId = $request->input('messageId');
            $type = $request->input('type'); // DeliveryCallback

            Log::info('Delivery callback received', [
                'messageId' => $messageId,
                'type' => $type,
                'phone' => $request->input('phone'),
            ]);

            if (! $messageId) {
                return response()->json(['status' => 'skipped', 'reason' => 'no message ID']);
            }

            // Find message by remote_message_id
            $message = WhatsAppMessage::where('remote_message_id', $messageId)->first();

            if (! $message) {
                Log::warning('Message not found for delivery callback', [
                    'messageId' => $messageId,
                ]);

                return response()->json(['status' => 'not_found']);
            }

            // DeliveryCallback means message was delivered
            $oldStatus = $message->status;
            $message->update(['status' => MessageStatus::Delivered]);

            Log::info('Message status updated via delivery callback', [
                'message_id' => $message->id,
                'old_status' => $oldStatus,
                'new_status' => 'delivered',
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error handling delivery callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
