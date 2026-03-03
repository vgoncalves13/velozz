<?php

namespace Database\Factories;

use App\Enums\Channel;
use App\Enums\MessageDirection;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Lead;
use App\Models\MetaAccount;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMessage>
 */
class SocialMessageFactory extends Factory
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
            'lead_id' => Lead::factory(),
            'meta_account_id' => MetaAccount::factory(),
            'channel' => fake()->randomElement([Channel::Instagram->value, Channel::FacebookMessenger->value]),
            'direction' => fake()->randomElement([MessageDirection::Incoming->value, MessageDirection::Outgoing->value]),
            'type' => MessageType::Text->value,
            'content' => fake()->sentence(),
            'media_url' => null,
            'status' => MessageStatus::Delivered->value,
            'external_message_id' => fake()->uuid(),
            'external_thread_id' => null,
            'sender_id' => (string) fake()->numerify('##########'),
            'sent_by_user_id' => null,
            'error_message' => null,
        ];
    }

    public function incoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => MessageDirection::Incoming->value,
            'sent_by_user_id' => null,
        ]);
    }

    public function outgoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => MessageDirection::Outgoing->value,
        ]);
    }

    public function instagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => Channel::Instagram->value,
        ]);
    }

    public function facebookMessenger(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => Channel::FacebookMessenger->value,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MessageStatus::Failed->value,
            'error_message' => fake()->sentence(),
        ]);
    }
}
