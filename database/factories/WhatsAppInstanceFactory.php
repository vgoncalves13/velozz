<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WhatsAppInstance>
 */
class WhatsAppInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'instance_id' => fake()->uuid(),
            'token' => fake()->uuid(),
            'status' => 'disconnected',
            'qr_code' => null,
            'phone_number' => null,
            'webhook_url' => null,
            'last_connected_at' => null,
        ];
    }

    public function connected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'connected',
            'phone_number' => '+351'.fake()->numerify('#########'),
            'last_connected_at' => now(),
        ]);
    }
}
