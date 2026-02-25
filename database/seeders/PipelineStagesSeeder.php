<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PipelineStagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $stages = [
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'New Lead',
                    'color' => '#3b82f6', // blue
                    'order' => 0,
                    'icon' => 'heroicon-o-inbox',
                    'sla_hours' => 24,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Contacted',
                    'color' => '#10b981', // green
                    'order' => 1,
                    'icon' => 'heroicon-o-phone',
                    'sla_hours' => 48,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Negotiation',
                    'color' => '#f59e0b', // amber
                    'order' => 2,
                    'icon' => 'heroicon-o-chat-bubble-left-right',
                    'sla_hours' => 72,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Proposal Sent',
                    'color' => '#8b5cf6', // purple
                    'order' => 3,
                    'icon' => 'heroicon-o-currency-dollar',
                    'sla_hours' => 120,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Won',
                    'color' => '#059669', // emerald
                    'order' => 4,
                    'icon' => 'heroicon-o-check-circle',
                    'sla_hours' => null,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Lost',
                    'color' => '#dc2626', // red
                    'order' => 5,
                    'icon' => 'heroicon-o-x-circle',
                    'sla_hours' => null,
                ],
            ];

            foreach ($stages as $stage) {
                PipelineStage::create($stage);
            }
        }
    }
}
