<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use App\Models\PipelineStage;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class LeadQualificationFunnelChart extends ApexChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $chartId = 'leadQualificationFunnelChart';

    protected function getHeading(): string
    {
        return __('dashboard.lead_funnel_heading');
    }

    protected function getSubheading(): string
    {
        return __('dashboard.lead_funnel_subheading');
    }

    protected function getOptions(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $stages = PipelineStage::where('tenant_id', $tenantId)->orderBy('order')->get();

        $categories = [];
        $counts = [];

        foreach ($stages as $stage) {
            $count = Lead::where('tenant_id', $tenantId)
                ->where('pipeline_stage_id', $stage->id)
                ->count();
            $categories[] = $stage->name;
            $counts[] = $count;
        }

        $noStageCount = Lead::where('tenant_id', $tenantId)->whereNull('pipeline_stage_id')->count();

        if ($noStageCount > 0) {
            $categories[] = __('dashboard.lead_funnel_no_stage');
            $counts[] = $noStageCount;
        }

        $height = max(300, count($categories) * 55 + 80);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => $height,
                'toolbar' => ['show' => false],
                'dropShadow' => ['enabled' => true],
            ],
            'series' => [
                ['name' => __('dashboard.lead_funnel_series'), 'data' => $counts],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 0,
                    'horizontal' => true,
                    'barHeight' => '80%',
                    'isFunnel' => true,
                ],
            ],
            'colors' => ['#6366f1'],
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
