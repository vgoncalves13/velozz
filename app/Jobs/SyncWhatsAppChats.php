<?php

namespace App\Jobs;

use App\Enums\LeadActivityType;
use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\WhatsAppInstance;
use App\Services\ZApi\ZApiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncWhatsAppChats implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public readonly WhatsAppInstance $instance) {}

    public function handle(ZApiServiceInterface $zapi): void
    {
        $cutoff = now()->subDays($this->instance->sync_days)->timestamp;
        $page = 0;
        $pageSize = 100;
        $synced = 0;
        $created = 0;

        Log::info('SyncWhatsAppChats started', [
            'instance_id' => $this->instance->instance_id,
            'sync_days' => $this->instance->sync_days,
        ]);

        do {
            $result = $zapi->getChats($this->instance->instance_id, $page, $pageSize);

            if (! $result['success']) {
                Log::error('SyncWhatsAppChats: getChats failed', [
                    'instance_id' => $this->instance->instance_id,
                    'error' => $result['error'] ?? 'unknown',
                ]);
                break;
            }

            $chats = $result['chats'] ?? [];

            if (empty($chats)) {
                break;
            }

            foreach ($chats as $chat) {
                $lastMessageAt = $chat['last_message_at'] ?? null;

                // Skip chats with no recent activity
                if ($lastMessageAt !== null && $lastMessageAt < $cutoff) {
                    continue;
                }

                $phone = $this->cleanPhone($chat['phone']);

                if (empty($phone)) {
                    continue;
                }

                $isNew = $this->syncContact(
                    phone: $phone,
                    name: $chat['name'] ?? null,
                );

                $synced++;
                if ($isNew) {
                    $created++;
                }
            }

            $page++;

            // Stop paginating if fewer results than page size (last page)
        } while (count($chats) >= $pageSize);

        Log::info('SyncWhatsAppChats completed', [
            'instance_id' => $this->instance->instance_id,
            'synced' => $synced,
            'created' => $created,
        ]);
    }

    private function syncContact(string $phone, ?string $name): bool
    {
        $tenantId = $this->instance->tenant_id;

        $existing = Lead::where('tenant_id', $tenantId)
            ->where(function ($query) use ($phone) {
                $query->whereJsonContains('whatsapps', $phone);
            })
            ->first();

        if ($existing) {
            return false;
        }

        $lead = Lead::create([
            'tenant_id' => $tenantId,
            'full_name' => $name ?: 'Unknown ('.substr($phone, -4).')',
            'whatsapps' => [$phone],
            'primary_whatsapp_index' => 0,
            'source' => LeadSource::Whatsapp,
            'consent_status' => 'pending',
        ]);

        LeadActivity::create([
            'tenant_id' => $tenantId,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead created from WhatsApp chat sync',
        ]);

        return true;
    }

    private function cleanPhone(string $phone): string
    {
        // Remove WhatsApp suffixes like @s.whatsapp.net
        $phone = explode('@', $phone)[0];

        // Remove non-numeric characters
        return preg_replace('/\D/', '', $phone);
    }
}
