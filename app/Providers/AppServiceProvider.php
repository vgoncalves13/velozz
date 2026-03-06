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

        // Bind Meta Graph API Service - Real when enabled, Mock otherwise
        $this->app->bind(
            \App\Services\Meta\MetaGraphApiServiceInterface::class,
            config('services.meta.enabled')
                ? \App\Services\Meta\MetaGraphApiService::class
                : \App\Services\Meta\MetaGraphApiMockService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function (\App\Models\User $user, string $ability): ?bool {
            if ($user->isAdminMaster()) {
                return true;
            }

            return null;
        });

        \App\Models\Lead::observe(\App\Observers\LeadObserver::class);

        // Listen to Stripe webhook events to update tenant plan
        \Illuminate\Support\Facades\Event::listen(
            \Laravel\Cashier\Events\WebhookReceived::class,
            \App\Listeners\UpdateTenantPlanOnSubscription::class,
        );

        // Listen to authentication events for audit logging
        \Illuminate\Support\Facades\Event::subscribe(
            \App\Listeners\LogAuthenticationEvents::class
        );
    }
}
