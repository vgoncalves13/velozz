<?php

namespace App\Filament\Client\Widgets;

use App\Models\Opportunity;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OpportunitiesFunnelChart extends ApexChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $chartId = 'opportunitiesFunnelChart';

    protected static ?string $heading = 'Oportunidades por Estágio';

    protected function getOptions(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $stageOrder = ['proposal', 'negotiation', 'closed_won', 'closed_lost'];
        $stageLabels = [
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'closed_won' => 'Ganho',
            'closed_lost' => 'Perdido',
        ];

        $rows = Opportunity::where('tenant_id', $tenantId)
            ->selectRaw('stage, count(*) as total, sum(value) as total_value')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $counts = array_map(fn ($stage) => (int) ($rows->get($stage)?->total ?? 0), $stageOrder);
        $values = array_map(fn ($stage) => (float) ($rows->get($stage)?->total_value ?? 0), $stageOrder);
        $categories = array_map(fn ($stage) => $stageLabels[$stage], $stageOrder);

        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'toolbar' => ['show' => false],
            ],
            'plotOptions' => [
                'bar' => ['borderRadius' => 6],
            ],
            'series' => [
                ['name' => 'Oportunidades', 'type' => 'column', 'data' => $counts],
                ['name' => 'Valor (€)', 'type' => 'line', 'data' => $values],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
            ],
            'yaxis' => [
                ['title' => ['text' => 'Qtd'], 'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]]],
                ['opposite' => true, 'title' => ['text' => 'Valor (€)'], 'labels' => ['style' => ['colors' => '#9ca3af', 'fontWeight' => 600]]],
            ],
            'colors' => ['#6366f1', '#f59e0b'],
            'stroke' => ['width' => [0, 3]],
            'dataLabels' => ['enabled' => true, 'enabledOnSeries' => [0]],
            'legend' => ['labels' => ['colors' => '#9ca3af', 'fontWeight' => 600]],
        ];
    }
}
