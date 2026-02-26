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

        // Predicted Revenue (sum of value * probability / 100)
        $predictedRevenue = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->get()
            ->sum(function ($opportunity) {
                return $opportunity->value * ($opportunity->probability / 100);
            });

        // Closed Revenue (closed won)
        $closedRevenue = Opportunity::where('tenant_id', $tenantId)
            ->where('stage', 'closed_won')
            ->sum('value');

        // Open Opportunities
        $openOpportunities = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->count();

        // Total Opportunity Value
        $totalValue = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->sum('value');

        return [
            Stat::make('Predicted Revenue', '€' . number_format($predictedRevenue, 2, ',', '.'))
                ->description('Based on probability weighted value')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Closed Revenue', '€' . number_format($closedRevenue, 2, ',', '.'))
                ->description('Total closed won opportunities')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Open Opportunities', $openOpportunities)
                ->description('In proposal or negotiation')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('warning'),

            Stat::make('Total Pipeline Value', '€' . number_format($totalValue, 2, ',', '.'))
                ->description('Sum of all open opportunities')
                ->descriptionIcon('heroicon-o-currency-euro')
                ->color('primary'),
        ];
    }
}
