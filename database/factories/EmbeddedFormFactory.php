<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmbeddedForm>
 */
class EmbeddedFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'description' => fake()->optional()->sentence(),
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'placeholder' => '',
                    'required' => true,
                    'default_value' => '',
                    'help_text' => '',
                    'options' => [],
                    'validation' => ['min' => null, 'max' => null, 'regex' => null],
                    'order' => 0,
                ],
                [
                    'type' => 'email',
                    'label' => 'Email',
                    'name' => 'email',
                    'placeholder' => '',
                    'required' => false,
                    'default_value' => '',
                    'help_text' => '',
                    'options' => [],
                    'validation' => ['min' => null, 'max' => null, 'regex' => null],
                    'order' => 1,
                ],
            ],
            'styles' => [
                'width' => '100%',
                'alignment' => 'left',
                'button_text' => 'Submit',
                'button_color' => '#3b82f6',
                'button_text_color' => '#ffffff',
            ],
            'status' => 'active',
            'redirect_url' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }
}
