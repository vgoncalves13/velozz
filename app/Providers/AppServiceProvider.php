<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind ZApi Service - Real when enabled, Mock otherwise
        $this->app->bind(
            \App\Services\ZApi\ZApiServiceInterface::class,
            config('services.zapi.enabled')
                ? \App\Services\ZApi\ZApiRealService::class
                : \App\Services\ZApi\ZApiMockService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Lead::observe(\App\Observers\LeadObserver::class);

        // Listen to Stripe webhook events to update tenant plan
        \Illuminate\Support\Facades\Event::listen(
            \Laravel\Cashier\Events\WebhookReceived::class,
            \App\Listeners\UpdateTenantPlanOnSubscription::class,
        );
    }
}
