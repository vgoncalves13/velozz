<?php

namespace App\Filament\Client\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsByOriginChart extends ChartWidget
{
    protected ?string $heading = 'Leads by Origin';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $origins = [
            'import' => 'Import',
            'manual' => 'Manual',
            'api' => 'API',
            'form' => 'Form',
        ];

        $data = [];
        $labels = [];

        foreach ($origins as $key => $label) {
            $count = Lead::where('tenant_id', $tenantId)
                ->where('source', $key)
                ->count();

            if ($count > 0) {
                $labels[] = $label;
                $data[] = $count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',  // blue
                        'rgba(34, 197, 94, 0.5)',   // green
                        'rgba(245, 158, 11, 0.5)',  // amber
                        'rgba(168, 85, 247, 0.5)',  // purple
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(245, 158, 11)',
                        'rgb(168, 85, 247)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
