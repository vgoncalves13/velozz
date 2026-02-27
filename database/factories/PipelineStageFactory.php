<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stages = ['New Lead', 'Contacted', 'Qualified', 'Proposal Sent', 'Negotiation', 'Closed Won', 'Closed Lost'];
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899'];
        $icons = ['heroicon-o-inbox', 'heroicon-o-phone', 'heroicon-o-check-circle', 'heroicon-o-currency-dollar'];

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement($stages),
            'color' => fake()->randomElement($colors),
            'order' => fake()->numberBetween(0, 10),
            'icon' => fake()->randomElement($icons),
            'sla_hours' => fake()->optional()->numberBetween(24, 168),
        ];
    }
}
