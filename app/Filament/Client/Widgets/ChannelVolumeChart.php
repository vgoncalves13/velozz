<?php

namespace App\Filament\Client\Widgets;

use App\Models\SocialMessage;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ChannelVolumeChart extends ApexChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $chartId = 'channelVolumeChart';

    protected static ?string $heading = 'Omnichannel Volume';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Últimos 7 dias',
            '30' => 'Últimos 30 dias',
            '90' => 'Últimos 90 dias',
        ];
    }

    protected function getOptions(): array
    {
        $days = (int) ($this->filter ?? 30);
        $tenantId = auth()->user()->tenant_id;
        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $dateRange = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateRange->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $whatsapp = WhatsAppMessage::where('tenant_id', $tenantId)
            ->where('direction', 'outgoing')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date');

        $instagram = SocialMessage::where('tenant_id', $tenantId)
            ->where('channel', 'instagram')
            ->where('direction', 'outgoing')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date');

        $facebook = SocialMessage::where('tenant_id', $tenantId)
            ->where('channel', 'facebook_messenger')
            ->where('direction', 'outgoing')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date');

        $whatsappData = $dateRange->map(fn ($date) => $whatsapp->get($date, 0))->values()->toArray();
        $instagramData = $dateRange->map(fn ($date) => $instagram->get($date, 0))->values()->toArray();
        $facebookData = $dateRange->map(fn ($date) => $facebook->get($date, 0))->values()->toArray();
        $categories = $dateRange->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values()->toArray();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
            ],
            'series' => [
                ['name' => 'WhatsApp', 'data' => $whatsappData],
                ['name' => 'Instagram', 'data' => $instagramData],
                ['name' => 'Facebook', 'data' => $facebookData],
            ],
            'colors' => ['#25D366', '#E1306C', '#1877F2'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'opacityFrom' => 0.5,
                    'opacityTo' => 0.0,
                ],
            ],
            'stroke' => ['curve' => 'smooth', 'width' => 2],
            'dataLabels' => ['enabled' => false],
            'legend' => ['position' => 'top'],
            'tooltip' => ['shared' => true, 'intersect' => false],
            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'yaxis' => [
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'grid' => ['borderColor' => '#f1f1f1'],
        ];
    }
}
