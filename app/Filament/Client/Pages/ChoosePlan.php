<?php

namespace App\Filament\Client\Pages;

use App\Models\Plan;
use App\Models\Tenant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ChoosePlan extends Page
{
    protected string $view = 'filament.client.pages.choose-plan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Choose Plan';

    protected static string|null|\UnitEnum $navigationGroup = 'System';

    protected static ?string $title = 'Choose Your Plan';

    protected static ?int $navigationSort = 99;

    public function getPlans()
    {
        return Plan::orderBy('price')->get();
    }

    public function subscribe(int $planId)
    {
        $plan = Plan::findOrFail($planId);
        $tenant = Tenant::find(auth()->user()->tenant_id);

        if (! $tenant) {
            $this->addError('subscription', 'Tenant not found');

            return;
        }

        $stripePriceIds = [
            1 => 'price_1T5Def1cYiRGeJ1WvIp82tFp', // Starter
            2 => 'price_1T5DfG1cYiRGeJ1WX6QOK2QN', // Professional
            3 => 'price_1T5DgU1cYiRGeJ1W7vfUc9iZ', // Enterprise
        ];

        try {
            $checkout = $tenant
                ->newSubscription('default', $stripePriceIds[$planId])
                ->checkout([
                    'success_url' => route('filament.client.pages.dashboard'),
                    'cancel_url' => route('filament.client.pages.choose-plan'),
                ]);

            return redirect($checkout->url);
        } catch (\Exception $e) {
            $this->addError('subscription', 'Error creating checkout: '.$e->getMessage());
        }
    }
}
