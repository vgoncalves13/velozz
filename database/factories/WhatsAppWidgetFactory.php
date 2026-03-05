<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WhatsAppWidget>
 */
class WhatsAppWidgetFactory extends Factory
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
            'name' => fake()->words(2, true).' Widget',
            'whatsapp_number' => '+351'.fake()->numerify('9########'),
            'auto_message' => 'Hello {{nome}}, how can I help you?',
            'position' => fake()->randomElement(['bottom-right', 'bottom-left', 'top-right', 'top-left']),
            'appearance' => [
                'button_color' => '#25d366',
                'button_size' => '60px',
                'border_radius' => '50%',
                'animation' => 'none',
                'button_text' => '',
                'show_text' => false,
            ],
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }
}
