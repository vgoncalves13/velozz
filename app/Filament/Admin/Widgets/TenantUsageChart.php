<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use App\Models\WhatsAppMessage;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TenantUsageChart extends ChartWidget
{
    protected ?string $heading = 'Top 10 Tenants by Message Usage';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Get top 10 tenants by message count
        $topTenants = WhatsAppMessage::select('whatsapp_messages.tenant_id', DB::raw('COUNT(*) as message_count'))
            ->join('tenants', 'whatsapp_messages.tenant_id', '=', 'tenants.id')
            ->groupBy('whatsapp_messages.tenant_id')
            ->orderByDesc('message_count')
            ->limit(10)
            ->get();

        $data = [];
        $labels = [];

        foreach ($topTenants as $item) {
            $tenant = Tenant::find($item->tenant_id);
            $labels[] = $tenant?->name ?? 'Unknown';
            $data[] = $item->message_count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Messages Sent',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
