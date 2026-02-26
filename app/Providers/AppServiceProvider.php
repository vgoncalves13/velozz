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
        // Bind ZApi Service (Mock for development, Real for production)
        $this->app->bind(
            \App\Services\ZApi\ZApiServiceInterface::class,
            \App\Services\ZApi\ZApiMockService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Lead::observe(\App\Observers\LeadObserver::class);
    }
}
