<?php

namespace App\Filament\Client\Widgets;

use App\Models\SocialMessage;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ResponseTimeChart extends ApexChartWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $chartId = 'responseTimeChart';

    public ?string $filter = '7';

    protected function getHeading(): string
    {
        return __('dashboard.response_time_heading');
    }

    protected function getSubheading(): string
    {
        return __('dashboard.response_time_subheading');
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => __('dashboard.filter_7_days'),
            '14' => __('dashboard.filter_14_days'),
            '30' => __('dashboard.filter_30_days'),
        ];
    }

    protected function getOptions(): array
    {
        $days = (int) $this->filter;
        $tenantId = auth()->user()->tenant_id;
        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $dateRange = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateRange->push(Carbon::now()->subDays($i)->format('Y-m-d'));
        }

        $dailyResponseTimes = array_fill_keys($dateRange->toArray(), []);

        // WhatsApp
        $outgoingWA = WhatsAppMessage::where('tenant_id', $tenantId)
            ->where('direction', 'outgoing')
            ->where('created_at', '>=', $start)
            ->orderBy('created_at')
            ->get(['lead_id', 'created_at'])
            ->groupBy('lead_id');

        $incomingWA = WhatsAppMessage::where('tenant_id', $tenantId)
            ->where('direction', 'incoming')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get(['lead_id', 'created_at']);

        foreach ($incomingWA as $msg) {
            $next = $outgoingWA->get($msg->lead_id, collect())
                ->first(fn ($o) => $o->created_at > $msg->created_at);

            if (! $next) {
                continue;
            }

            $minutes = (int) $msg->created_at->diffInMinutes($next->created_at);
            $date = $msg->created_at->format('Y-m-d');

            if ($minutes <= 1440 && array_key_exists($date, $dailyResponseTimes)) {
                $dailyResponseTimes[$date][] = $minutes;
            }
        }

        // Social (Instagram + Facebook)
        $outgoingSocial = SocialMessage::where('tenant_id', $tenantId)
            ->where('direction', 'outgoing')
            ->where('created_at', '>=', $start)
            ->orderBy('created_at')
            ->get(['lead_id', 'created_at'])
            ->groupBy('lead_id');

        $incomingSocial = SocialMessage::where('tenant_id', $tenantId)
            ->where('direction', 'incoming')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get(['lead_id', 'created_at']);

        foreach ($incomingSocial as $msg) {
            $next = $outgoingSocial->get($msg->lead_id, collect())
                ->first(fn ($o) => $o->created_at > $msg->created_at);

            if (! $next) {
                continue;
            }

            $minutes = (int) $msg->created_at->diffInMinutes($next->created_at);
            $date = $msg->created_at->format('Y-m-d');

            if ($minutes <= 1440 && array_key_exists($date, $dailyResponseTimes)) {
                $dailyResponseTimes[$date][] = $minutes;
            }
        }

        $dayMap = [
            'Sun' => __('dashboard.day_sun'),
            'Mon' => __('dashboard.day_mon'),
            'Tue' => __('dashboard.day_tue'),
            'Wed' => __('dashboard.day_wed'),
            'Thu' => __('dashboard.day_thu'),
            'Fri' => __('dashboard.day_fri'),
            'Sat' => __('dashboard.day_sat'),
        ];

        $data = array_values(array_map(
            fn ($times) => count($times) > 0 ? (int) round(array_sum($times) / count($times)) : null,
            $dailyResponseTimes
        ));

        $categories = $dateRange->map(function ($date) use ($days, $dayMap) {
            $carbon = Carbon::parse($date);

            return $days <= 14
                ? $dayMap[$carbon->format('D')].' '.$carbon->format('d/m')
                : $carbon->format('d/m');
        })->values()->toArray();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 280,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                ['name' => __('dashboard.response_time_series'), 'data' => $data],
            ],
            'colors' => ['#f59e0b'],
            'stroke' => ['curve' => 'smooth', 'width' => 3],
            'dataLabels' => ['enabled' => true],
            'markers' => ['size' => 5],
            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'yaxis' => [
                'min' => 0,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'grid' => ['borderColor' => '#f1f1f1'],
            'tooltip' => ['shared' => true, 'intersect' => false],
        ];
    }
}
