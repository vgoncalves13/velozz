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

        $totalLeads = Lead::where('tenant_id', $tenantId)->count();

        $leadsToday = Lead::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count();

        $leadsWithMessages = Lead::where('tenant_id', $tenantId)
            ->whereHas('whatsappMessages', function ($query) {
                $query->where('direction', 'outgoing');
            })
            ->count();

        $contactRate = $totalLeads > 0
            ? round(($leadsWithMessages / $totalLeads) * 100, 1)
            : 0;

        $messagesSent = WhatsAppMessage::whereHas('lead', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->where('direction', 'outgoing')
            ->count();

        return [
            Stat::make(__('dashboard.total_leads'), number_format($totalLeads, 0, ',', '.'))
                ->description(__('dashboard.total_leads_desc'))
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make(__('dashboard.leads_today'), $leadsToday)
                ->description(__('dashboard.leads_today_desc'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make(__('dashboard.contact_rate'), $contactRate.'%')
                ->description(__('dashboard.contact_rate_desc'))
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make(__('dashboard.messages_sent'), number_format($messagesSent, 0, ',', '.'))
                ->description(__('dashboard.messages_sent_desc'))
                ->descriptionIcon('heroicon-o-paper-airplane')
                ->color('warning'),
        ];
    }
}
