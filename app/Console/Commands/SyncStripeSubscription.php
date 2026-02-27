<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Laravel\Cashier\Subscription;
use Stripe\StripeClient;

class SyncStripeSubscription extends Command
{
    protected $signature = 'stripe:sync {tenant_id}';

    protected $description = 'Sync Stripe subscription to local database for a tenant';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant #{$tenantId} not found");

            return Command::FAILURE;
        }

        if (! $tenant->stripe_id) {
            $this->error("Tenant #{$tenantId} doesn't have a Stripe customer ID");

            return Command::FAILURE;
        }

        $this->info("Fetching subscriptions from Stripe for customer: {$tenant->stripe_id}");

        try {
            $stripe = new StripeClient(config('cashier.secret'));
            $stripeSubscriptions = $stripe->subscriptions->all([
                'customer' => $tenant->stripe_id,
                'limit' => 10,
            ]);

            if ($stripeSubscriptions->count() === 0) {
                $this->warn('No subscriptions found on Stripe');

                return Command::SUCCESS;
            }

            foreach ($stripeSubscriptions->data as $stripeSub) {
                $this->info("Processing subscription: {$stripeSub->id}");

                // Check if already exists
                $existing = Subscription::where('stripe_id', $stripeSub->id)->first();

                if ($existing) {
                    $this->warn("Subscription {$stripeSub->id} already exists locally");

                    continue;
                }

                // Create subscription locally
                $subscription = $tenant->subscriptions()->create([
                    'type' => 'default',
                    'stripe_id' => $stripeSub->id,
                    'stripe_status' => $stripeSub->status,
                    'stripe_price' => $stripeSub->items->data[0]->price->id ?? null,
                    'quantity' => $stripeSub->items->data[0]->quantity ?? 1,
                    'trial_ends_at' => $stripeSub->trial_end ? date('Y-m-d H:i:s', $stripeSub->trial_end) : null,
                    'ends_at' => $stripeSub->ended_at ? date('Y-m-d H:i:s', $stripeSub->ended_at) : null,
                ]);

                $this->info("Created subscription locally with ID: {$subscription->id}");

                // Update tenant plan_id based on price
                $priceToPlan = [
                    'price_1T5Def1cYiRGeJ1WvIp82tFp' => 1, // Starter
                    'price_1T5DfG1cYiRGeJ1WX6QOK2QN' => 2, // Professional
                    'price_1T5DgU1cYiRGeJ1W7vfUc9iZ' => 3, // Enterprise
                ];

                $priceId = $subscription->stripe_price;
                if (isset($priceToPlan[$priceId])) {
                    $tenant->update(['plan_id' => $priceToPlan[$priceId]]);
                    $this->info("Updated tenant plan_id to: {$priceToPlan[$priceId]}");
                }
            }

            $this->info('Sync completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error syncing: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
