<?php

namespace App\Helpers;

use App\Jobs\DispatchWebhook;
use App\Models\Tenant;

class WebhookHelper
{
    public static function dispatch(string $event, array $data, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id ?? tenant()?->id ?? null;

        if (! $tenantId) {
            return;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant || ! isset($tenant->settings['webhooks'])) {
            return;
        }

        $webhooks = $tenant->settings['webhooks'];

        if (! is_array($webhooks)) {
            return;
        }

        foreach ($webhooks as $webhook) {
            if (! isset($webhook['url']) || ! isset($webhook['events'])) {
                continue;
            }

            // Check if this webhook is configured for this event
            if (! in_array($event, $webhook['events'])) {
                continue;
            }

            // Dispatch the webhook job
            DispatchWebhook::dispatch(
                url: $webhook['url'],
                event: $event,
                data: $data,
                tenantId: $tenantId
            );
        }
    }
}
