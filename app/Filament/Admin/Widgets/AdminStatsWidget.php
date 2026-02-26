<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Active Tenants
        $activeTenants = Tenant::where('status', 'active')->count();

        // Total Leads (all tenants)
        $totalLeads = Lead::count();

        // Messages Today (all tenants)
        $messagesToday = WhatsAppMessage::whereDate('created_at', today())->count();

        // Disconnected WhatsApp Instances
        $disconnectedInstances = WhatsAppInstance::whereIn('status', ['disconnected', 'error'])
            ->distinct('tenant_id')
            ->count('tenant_id');

        return [
            Stat::make('Active Tenants', $activeTenants)
                ->description('Tenants with active status')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('success'),

            Stat::make('Total Leads', number_format($totalLeads, 0, ',', '.'))
                ->description('Across all tenants')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Messages Today', number_format($messagesToday, 0, ',', '.'))
                ->description('Sent today across all tenants')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Disconnected Instances', $disconnectedInstances)
                ->description('Tenants with WhatsApp issues')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($disconnectedInstances > 0 ? 'danger' : 'success'),
        ];
    }
}
