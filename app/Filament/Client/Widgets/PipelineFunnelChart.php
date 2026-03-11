<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use App\Models\PipelineStage;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PipelineFunnelChart extends ApexChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $chartId = 'pipelineFunnelChart';

    protected static ?string $heading = 'Pipeline por Etapa';

    protected function getOptions(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $fallbackColors = ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#06b6d4', '#8b5cf6', '#f97316', '#ec4899'];

        $stages = PipelineStage::where('tenant_id', $tenantId)->orderBy('order')->get();

        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($stages as $index => $stage) {
            $labels[] = $stage->name;
            $counts[] = Lead::where('tenant_id', $tenantId)->where('pipeline_stage_id', $stage->id)->count();
            $colors[] = $stage->color ?: $fallbackColors[$index % count($fallbackColors)];
        }

        // Leads without a stage
        $labels[] = 'Sem Etapa';
        $counts[] = Lead::where('tenant_id', $tenantId)->whereNull('pipeline_stage_id')->count();
        $colors[] = '#94a3b8';

        $height = max(200, count($labels) * 50 + 100);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => $height,
                'toolbar' => ['show' => false],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'distributed' => true,
                    'borderRadius' => 6,
                ],
            ],
            'series' => [['name' => 'Leads', 'data' => $counts]],
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'yaxis' => [
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'colors' => $colors,
            'legend' => ['show' => false],
            'dataLabels' => [
                'enabled' => true,
                'textAnchor' => 'start',
                'offsetX' => 5,
            ],
        ];
    }
}
