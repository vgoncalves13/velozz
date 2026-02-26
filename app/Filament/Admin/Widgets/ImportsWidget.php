<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Import;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ImportsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Imports today
        $importsToday = Import::whereDate('created_at', today())->count();

        // Total imported leads today
        $leadsImportedToday = Import::whereDate('created_at', today())
            ->sum('imported');

        // Failed imports today
        $failedToday = Import::whereDate('created_at', today())
            ->where('status', 'failed')
            ->count();

        return [
            Stat::make('Imports Today', $importsToday)
                ->description('Total import operations today')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('info'),

            Stat::make('Leads Imported Today', number_format($leadsImportedToday, 0, ',', '.'))
                ->description('Successfully imported records')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('success'),

            Stat::make('Failed Imports', $failedToday)
                ->description('Failed import operations today')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color($failedToday > 0 ? 'danger' : 'success'),
        ];
    }
}
