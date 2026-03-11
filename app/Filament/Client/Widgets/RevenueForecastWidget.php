<?php

namespace App\Filament\Client\Widgets;

use App\Models\Opportunity;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueForecastWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $predictedRevenue = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->get()
            ->sum(function ($opportunity) {
                return $opportunity->value * ($opportunity->probability / 100);
            });

        $closedRevenue = Opportunity::where('tenant_id', $tenantId)
            ->where('stage', 'closed_won')
            ->sum('value');

        $openOpportunities = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->count();

        $totalValue = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->sum('value');

        return [
            Stat::make(__('dashboard.predicted_revenue'), '€'.number_format($predictedRevenue, 2, ',', '.'))
                ->description(__('dashboard.predicted_revenue_desc'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make(__('dashboard.closed_revenue'), '€'.number_format($closedRevenue, 2, ',', '.'))
                ->description(__('dashboard.closed_revenue_desc'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(__('dashboard.open_opportunities'), $openOpportunities)
                ->description(__('dashboard.open_opportunities_desc'))
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('warning'),

            Stat::make(__('dashboard.total_pipeline_value'), '€'.number_format($totalValue, 2, ',', '.'))
                ->description(__('dashboard.total_pipeline_value_desc'))
                ->descriptionIcon('heroicon-o-currency-euro')
                ->color('primary'),
        ];
    }
}
