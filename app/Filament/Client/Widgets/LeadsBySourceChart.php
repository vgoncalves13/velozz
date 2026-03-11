<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class LeadsBySourceChart extends ApexChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static ?string $chartId = 'leadsBySourceChart';

    protected function getHeading(): string
    {
        return __('dashboard.leads_by_source_heading');
    }

    protected function getOptions(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $labelMap = [
            'import' => 'Import',
            'manual' => 'Manual',
            'api' => 'API',
            'form' => 'Formulário',
            'whatsapp' => 'WhatsApp',
            'instagram' => 'Instagram',
            'facebook_messenger' => 'Facebook Messenger',
            'embedded_form' => 'Form Embutido',
            'whatsapp_widget' => 'Widget WhatsApp',
            'facebook_lead_ad' => 'Facebook Lead Ad',
        ];

        $rows = Lead::where('tenant_id', $tenantId)
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->get()
            ->filter(fn ($row) => $row->total > 0);

        $data = $rows->pluck('total')->map(fn ($v) => (int) $v)->values()->toArray();
        $labels = $rows->map(function ($row) use ($labelMap) {
            $value = $row->source instanceof \BackedEnum ? $row->source->value : (string) $row->source;

            return $labelMap[$value] ?? $value;
        })->values()->toArray();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 350,
            ],
            'series' => $data,
            'labels' => $labels,
            'colors' => ['#25D366', '#E1306C', '#1877F2', '#6366f1', '#f59e0b', '#10b981', '#8b5cf6', '#06b6d4', '#f97316', '#64748b'],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'total' => ['show' => true],
                        ],
                    ],
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'legend' => [
                'position' => 'bottom',
                'labels' => ['colors' => '#9ca3af', 'fontWeight' => 600],
            ],
        ];
    }
}
