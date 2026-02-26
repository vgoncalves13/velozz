<?php

namespace App\Filament\Client\Widgets;

use App\Models\Opportunity;
use Filament\Widgets\ChartWidget;

class SalesFunnelWidget extends ChartWidget
{
    protected static ?string $heading = 'Sales Funnel';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $stages = [
            'proposal' => 'Proposal',
            'negotiation' => 'Negotiation',
            'closed_won' => 'Closed Won',
            'closed_lost' => 'Closed Lost',
        ];

        $data = [];
        $labels = [];

        foreach ($stages as $key => $label) {
            $count = Opportunity::where('tenant_id', $tenantId)
                ->where('stage', $key)
                ->count();

            $labels[] = $label;
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Opportunities',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)', // blue (proposal)
                        'rgba(245, 158, 11, 0.5)', // amber (negotiation)
                        'rgba(34, 197, 94, 0.5)',  // green (closed won)
                        'rgba(239, 68, 68, 0.5)',  // red (closed lost)
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
