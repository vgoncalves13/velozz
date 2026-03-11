<?php

namespace App\Filament\Client\Widgets;

use App\Models\Opportunity;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OpportunityFunnelChart extends ApexChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $chartId = 'opportunityFunnelChart';

    protected function getHeading(): string
    {
        return __('dashboard.opportunity_funnel_heading');
    }

    protected function getSubheading(): string
    {
        return __('dashboard.opportunity_funnel_subheading');
    }

    protected function getOptions(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $stageOrder = ['proposal', 'negotiation', 'closed_won', 'closed_lost'];
        $stageColors = [
            'proposal' => '#6366f1',
            'negotiation' => '#f59e0b',
            'closed_won' => '#10b981',
            'closed_lost' => '#ef4444',
        ];

        $rows = Opportunity::where('tenant_id', $tenantId)
            ->selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $categories = [];
        $counts = [];
        $colors = [];

        foreach ($stageOrder as $stage) {
            $categories[] = __('dashboard.stage_'.$stage);
            $counts[] = (int) ($rows->get($stage)?->total ?? 0);
            $colors[] = $stageColors[$stage];
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'toolbar' => ['show' => false],
                'dropShadow' => ['enabled' => true],
            ],
            'series' => [
                ['name' => __('dashboard.opportunity_funnel_series'), 'data' => $counts],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 0,
                    'horizontal' => true,
                    'barHeight' => '80%',
                    'isFunnel' => true,
                    'distributed' => true,
                ],
            ],
            'colors' => $colors,
            'dataLabels' => [
                'enabled' => true,
                'dropShadow' => ['enabled' => true],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'legend' => ['show' => false],
        ];
    }
}
