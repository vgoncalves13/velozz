<?php

namespace App\Filament\Client\Widgets;

use App\Models\Opportunity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueChart extends ApexChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $chartId = 'revenueChart';

    public ?string $filter = '30';

    protected function getHeading(): string
    {
        return __('dashboard.revenue_heading');
    }

    protected function getSubheading(): string
    {
        return __('dashboard.revenue_subheading');
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => __('dashboard.filter_7_days'),
            '30' => __('dashboard.filter_30_days'),
            '90' => __('dashboard.filter_90_days'),
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

        // Revenue closed (won) per day
        $closedRevenue = Opportunity::where('tenant_id', $tenantId)
            ->where('stage', 'closed_won')
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$start, $end])
            ->selectRaw('DATE(closed_at) as date, sum(value) as total')
            ->groupBy(DB::raw('DATE(closed_at)'))
            ->pluck('total', 'date');

        // New pipeline value added per day (proposal or negotiation)
        $pipelineAdded = Opportunity::where('tenant_id', $tenantId)
            ->whereIn('stage', ['proposal', 'negotiation'])
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, sum(value) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date');

        $closedData = $dateRange->map(fn ($date) => (float) ($closedRevenue->get($date, 0)))->values()->toArray();
        $pipelineData = $dateRange->map(fn ($date) => (float) ($pipelineAdded->get($date, 0)))->values()->toArray();
        $categories = $dateRange->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values()->toArray();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
            ],
            'series' => [
                ['name' => __('dashboard.revenue_series_closed'), 'data' => $closedData],
                ['name' => __('dashboard.revenue_series_pipeline'), 'data' => $pipelineData],
            ],
            'colors' => ['#10b981', '#6366f1'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.0,
                ],
            ],
            'stroke' => ['curve' => 'smooth', 'width' => 2],
            'dataLabels' => ['enabled' => false],
            'legend' => ['position' => 'top'],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
            ],
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
