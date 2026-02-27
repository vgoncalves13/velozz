<?php

namespace App\Listeners;

use App\Models\Tenant;
use Laravel\Cashier\Events\WebhookReceived;

class UpdateTenantPlanOnSubscription
{
    /**
     * Mapping of Stripe Price IDs to Plan IDs
     */
    protected array $priceToPlan = [
        'price_1T5Def1cYiRGeJ1WvIp82tFp' => 1, // Starter
        'price_1T5DfG1cYiRGeJ1WX6QOK2QN' => 2, // Professional
        'price_1T5DgU1cYiRGeJ1W7vfUc9iZ' => 3, // Enterprise
    ];

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        // Only handle subscription-related webhooks
        if (! in_array($event->payload['type'], [
            'customer.subscription.created',
            'customer.subscription.updated',
        ])) {
            return;
        }

        $stripeSubscription = $event->payload['data']['object'];
        $customerId = $stripeSubscription['customer'];
        $priceId = $stripeSubscription['items']['data'][0]['price']['id'] ?? null;

        if (! $priceId) {
            return;
        }

        // Find tenant by stripe_id
        $tenant = Tenant::where('stripe_id', $customerId)->first();

        if (! $tenant) {
            return;
        }

        // Update tenant plan_id if we have a mapping for this price
        if (isset($this->priceToPlan[$priceId])) {
            $tenant->update(['plan_id' => $this->priceToPlan[$priceId]]);

            \Log::info("Tenant #{$tenant->id} plan updated to #{$this->priceToPlan[$priceId]} via webhook");
        }
    }
}
