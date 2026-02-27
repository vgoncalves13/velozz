<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Starter', 'Professional', 'Enterprise']),
            'price' => fake()->randomFloat(2, 29, 299),
            'currency' => 'EUR',
            'leads_limit_per_month' => fake()->randomElement([100, 500, 1000]),
            'messages_limit_per_day' => fake()->randomElement([50, 200, 500]),
            'operators_limit' => fake()->randomElement([3, 10, 50]),
            'whatsapp_instances_limit' => fake()->randomElement([1, 3, 10]),
            'trial_days' => 14,
        ];
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Starter',
            'price' => 29.99,
            'leads_limit_per_month' => 100,
            'messages_limit_per_day' => 50,
            'operators_limit' => 3,
            'whatsapp_instances_limit' => 1,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Professional',
            'price' => 99.99,
            'leads_limit_per_month' => 500,
            'messages_limit_per_day' => 200,
            'operators_limit' => 10,
            'whatsapp_instances_limit' => 3,
        ]);
    }

    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Enterprise',
            'price' => 299.99,
            'leads_limit_per_month' => null,
            'messages_limit_per_day' => null,
            'operators_limit' => null,
            'whatsapp_instances_limit' => 10,
        ]);
    }
}
