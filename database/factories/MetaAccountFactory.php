<?php

namespace Database\Factories;

use App\Enums\Channel;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetaAccount>
 */
class MetaAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([Channel::Instagram->value, Channel::FacebookMessenger->value]);

        return [
            'tenant_id' => Tenant::factory(),
            'type' => $type,
            'page_id' => (string) fake()->numerify('##########'),
            'page_name' => fake()->company(),
            'instagram_user_id' => $type === Channel::Instagram->value ? (string) fake()->numerify('##########') : null,
            'access_token' => fake()->sha256(),
            'status' => 'connected',
        ];
    }

    public function instagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Channel::Instagram->value,
            'instagram_user_id' => (string) fake()->numerify('##########'),
        ]);
    }

    public function facebookMessenger(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Channel::FacebookMessenger->value,
            'instagram_user_id' => null,
        ]);
    }

    public function disconnected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disconnected',
        ]);
    }
}
