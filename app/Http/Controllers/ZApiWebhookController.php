<?php

namespace App\Http\Controllers;

use App\Enums\LeadActivityType;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Events\MessageReceived;
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
                'payload' => $request->all(),
            ]);

            // Validate required fields
            $request->validate([
                'instanceId' => 'required|string',
                'message' => 'required|array',
                'message.fromMe' => 'required|boolean',
                'message.from' => 'required|string',
                'message.body' => 'nullable|string',
            ]);

            // Skip if message is from us
            if ($request->input('message.fromMe')) {
                return response()->json(['status' => 'skipped', 'reason' => 'message from me']);
            }

            // Find WhatsApp instance and tenant
            $instanceId = $request->input('instanceId');
            $instance = WhatsAppInstance::where('instance_id', $instanceId)->firstOrFail();
            $tenant = $instance->tenant;

            // Extract phone number (remove @c.us suffix if present)
            $phone = str_replace('@c.us', '', $request->input('message.from'));

            // Find or create lead
            $lead = $this->findOrCreateLead($tenant, $phone);

            // Create message
            $message = WhatsAppMessage::create([
                'tenant_id' => $tenant->id,
                'lead_id' => $lead->id,
                'whatsapp_instance_id' => $instance->id,
                'type' => $this->determineMessageType($request),
                'direction' => MessageDirection::Incoming,
                'content' => $request->input('message.body', ''),
                'media_url' => $request->input('message.mediaUrl'),
                'status' => MessageStatus::Delivered,
                'remote_message_id' => $request->input('message.id'),
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
    private function findOrCreateLead(Tenant $tenant, string $phone): Lead
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
            'full_name' => 'Unknown Contact',
            'whatsapps' => [$phone],
            'primary_whatsapp_index' => 1,
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
        if ($request->has('message.image')) {
            return MessageType::Image;
        }

        if ($request->has('message.audio')) {
            return MessageType::Audio;
        }

        if ($request->has('message.document')) {
            return MessageType::Document;
        }

        return MessageType::Text;
    }
}
