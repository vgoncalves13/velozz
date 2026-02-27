<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'name' => $companyName,
            'slug' => Str::slug($companyName).'-'.fake()->unique()->numberBetween(1, 9999),
            'domain' => Str::slug($companyName).'.velozz.test',
            'status' => fake()->randomElement(['trial', 'active', 'suspended']),
            'plan_id' => Plan::factory(),
            'trial_ends_at' => now()->addDays(14),
            'subscription_ends_at' => now()->addMonths(1),
            'admin_name' => fake()->name(),
            'admin_email' => fake()->companyEmail(),
            'admin_phone' => fake()->phoneNumber(),
            'settings' => [
                'primary_color' => fake()->hexColor(),
                'secondary_color' => fake()->hexColor(),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
