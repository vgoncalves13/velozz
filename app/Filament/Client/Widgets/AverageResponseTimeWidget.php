<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use App\Models\WhatsAppMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AverageResponseTimeWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Calculate average response time
        $leads = Lead::where('tenant_id', $tenantId)
            ->whereHas('whatsappMessages', function ($query) {
                $query->where('direction', 'outgoing');
            })
            ->get();

        $totalMinutes = 0;
        $count = 0;

        foreach ($leads as $lead) {
            // Get first outgoing message
            $firstOutgoing = WhatsAppMessage::where('lead_id', $lead->id)
                ->where('direction', 'outgoing')
                ->orderBy('created_at')
                ->first();

            if ($firstOutgoing) {
                // Calculate minutes between lead creation and first message
                $minutes = $lead->created_at->diffInMinutes($firstOutgoing->created_at);
                $totalMinutes += $minutes;
                $count++;
            }
        }

        $averageMinutes = $count > 0 ? round($totalMinutes / $count) : 0;

        // Format time
        if ($averageMinutes < 60) {
            $timeFormatted = $averageMinutes . ' min';
        } elseif ($averageMinutes < 1440) {
            $hours = round($averageMinutes / 60, 1);
            $timeFormatted = $hours . ' hours';
        } else {
            $days = round($averageMinutes / 1440, 1);
            $timeFormatted = $days . ' days';
        }

        return [
            Stat::make('Average Response Time', $timeFormatted)
                ->description('Time from lead creation to first message')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
