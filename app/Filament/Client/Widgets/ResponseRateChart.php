<?php

namespace App\Filament\Client\Widgets;

use App\Models\WhatsAppMessage;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ResponseRateChart extends ChartWidget
{
    protected ?string $heading = 'Response Rate (Last 7 Days)';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $data = [];
        $labels = [];

        // Get last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('d/m');

            // Count outgoing messages
            $outgoing = WhatsAppMessage::whereHas('lead', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
                ->where('direction', 'outgoing')
                ->whereDate('created_at', $date)
                ->count();

            // Count incoming messages (responses)
            $incoming = WhatsAppMessage::whereHas('lead', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
                ->where('direction', 'incoming')
                ->whereDate('created_at', $date)
                ->count();

            // Calculate response rate
            $rate = $outgoing > 0 ? round(($incoming / $outgoing) * 100, 1) : 0;
            $data[] = $rate;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Response Rate (%)',
                    'data' => $data,
                    'fill' => true,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%"; }',
                    ],
                ],
            ],
        ];
    }
}
