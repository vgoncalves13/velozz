<?php

namespace Database\Factories;

use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phones' => [
                fake()->phoneNumber(),
            ],
            'whatsapps' => [
                fake()->e164PhoneNumber(),
            ],
            'primary_whatsapp_index' => 0,
            'street_name' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => 'Portugal',
            'source' => fake()->randomElement(['import', 'manual', 'api', 'form']),
            'assigned_user_id' => null,
            'pipeline_stage_id' => null,
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'consent_status' => fake()->randomElement(['pending', 'granted', 'refused']),
            'consent_date' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'opt_out' => false,
            'do_not_contact' => false,
            'tags' => fake()->optional()->randomElements(['vip', 'hot-lead', 'follow-up', 'interested'], 2),
            'notes' => fake()->optional()->paragraph(),
            'custom_fields' => [],
        ];
    }

    public function withAssignedUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_user_id' => User::factory(),
        ]);
    }

    public function withStage(): static
    {
        return $this->state(fn (array $attributes) => [
            'pipeline_stage_id' => PipelineStage::factory(),
        ]);
    }

    public function optedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'opt_out' => true,
            'opt_out_date' => now(),
            'opt_out_reason' => fake()->sentence(),
        ]);
    }

    public function doNotContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'do_not_contact' => true,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }
}
