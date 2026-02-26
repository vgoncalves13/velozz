<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use App\Models\PipelineStage;
use Filament\Widgets\ChartWidget;

class KanbanFunnelWidget extends ChartWidget
{
    protected ?string $heading = 'Pipeline Funnel';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $tenantId = auth()->user()->tenant_id;

        // Get all pipeline stages ordered
        $stages = PipelineStage::where('tenant_id', $tenantId)
            ->orderBy('order')
            ->get();

        $data = [];
        $labels = [];

        foreach ($stages as $stage) {
            $count = Lead::where('tenant_id', $tenantId)
                ->where('pipeline_stage_id', $stage->id)
                ->count();

            $labels[] = $stage->name;
            $data[] = $count;
        }

        // Add count for leads without stage
        $withoutStage = Lead::where('tenant_id', $tenantId)
            ->whereNull('pipeline_stage_id')
            ->count();

        if ($withoutStage > 0) {
            $labels[] = 'No Stage';
            $data[] = $withoutStage;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
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
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
