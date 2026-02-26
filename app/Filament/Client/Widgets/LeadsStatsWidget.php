<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use App\Models\WhatsAppMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Total Leads
        $totalLeads = Lead::where('tenant_id', $tenantId)->count();

        // Leads Today
        $leadsToday = Lead::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count();

        // Contact Rate (leads with at least 1 outgoing message / total leads)
        $leadsWithMessages = Lead::where('tenant_id', $tenantId)
            ->whereHas('whatsappMessages', function ($query) {
                $query->where('direction', 'outgoing');
            })
            ->count();

        $contactRate = $totalLeads > 0
            ? round(($leadsWithMessages / $totalLeads) * 100, 1)
            : 0;

        // Messages Sent
        $messagesSent = WhatsAppMessage::whereHas('lead', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->where('direction', 'outgoing')
            ->count();

        return [
            Stat::make('Total Leads', number_format($totalLeads, 0, ',', '.'))
                ->description('All leads in the system')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Leads Today', $leadsToday)
                ->description('Created today')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('Contact Rate', $contactRate . '%')
                ->description('Leads contacted at least once')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Messages Sent', number_format($messagesSent, 0, ',', '.'))
                ->description('Total outgoing messages')
                ->descriptionIcon('heroicon-o-paper-airplane')
                ->color('warning'),
        ];
    }
}
