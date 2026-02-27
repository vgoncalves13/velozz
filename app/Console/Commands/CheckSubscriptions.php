<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionExpiringMail;
use App\Mail\TrialExpiredMail;
use App\Mail\TrialExpiringMail;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';

    protected $description = 'Check tenant subscriptions and trials, suspend expired accounts';

    public function handle(): int
    {
        $this->info('Checking tenant subscriptions and trials...');

        $this->checkTrials();
        $this->checkSubscriptions();

        $this->info('Subscription check completed!');

        return Command::SUCCESS;
    }

    protected function checkTrials(): void
    {
        $tenantsWithExpiringTrial = Tenant::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->get();

        foreach ($tenantsWithExpiringTrial as $tenant) {
            $daysRemaining = now()->diffInDays($tenant->trial_ends_at);
            $this->warn("Tenant #{$tenant->id} ({$tenant->name}) trial expires in {$daysRemaining} days");

            Log::info("Trial expiring soon for tenant {$tenant->id}", [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'trial_ends_at' => $tenant->trial_ends_at,
                'days_remaining' => $daysRemaining,
            ]);

            Mail::to($tenant->admin_email)->send(new TrialExpiringMail($tenant, $daysRemaining));
        }

        $expiredTrials = Tenant::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $tenant) {
            $tenant->update(['status' => 'suspended']);

            $this->error("Tenant #{$tenant->id} ({$tenant->name}) trial EXPIRED - Account suspended");

            Log::warning('Trial expired - tenant suspended', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'trial_ended_at' => $tenant->trial_ends_at,
            ]);

            Mail::to($tenant->admin_email)->send(new TrialExpiredMail($tenant));
        }
    }

    protected function checkSubscriptions(): void
    {
        $expiringSubscriptions = Tenant::where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->whereBetween('subscription_ends_at', [now(), now()->addDays(7)])
            ->get();

        foreach ($expiringSubscriptions as $tenant) {
            $daysRemaining = now()->diffInDays($tenant->subscription_ends_at);
            $this->warn("Tenant #{$tenant->id} ({$tenant->name}) subscription expires in {$daysRemaining} days");

            Log::info("Subscription expiring soon for tenant {$tenant->id}", [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'subscription_ends_at' => $tenant->subscription_ends_at,
                'days_remaining' => $daysRemaining,
            ]);

            Mail::to($tenant->admin_email)->send(new SubscriptionExpiringMail($tenant, $daysRemaining));
        }

        $expiredSubscriptions = Tenant::where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $tenant) {
            $tenant->update(['status' => 'suspended']);

            $this->error("Tenant #{$tenant->id} ({$tenant->name}) subscription EXPIRED - Account suspended");

            Log::warning('Subscription expired - tenant suspended', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'subscription_ended_at' => $tenant->subscription_ends_at,
            ]);

            Mail::to($tenant->admin_email)->send(new SubscriptionExpiredMail($tenant));
        }
    }
}
