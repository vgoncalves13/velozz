<?php

namespace Database\Factories;

use App\Models\FacebookLeadForm;
use App\Models\MetaAccount;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacebookLeadForm>
 */
class FacebookLeadFormFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'meta_account_id' => MetaAccount::factory()->facebookMessenger(),
            'form_id' => (string) fake()->numerify('##########'),
            'form_name' => fake()->words(3, true),
            'active' => true,
            'field_mapping' => null,
            'last_synced_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * @param  array<string, string>  $mapping  e.g. ['name' => 'full_name', 'email' => 'email']
     */
    public function withFieldMapping(array $mapping): static
    {
        return $this->state(fn (array $attributes) => [
            'field_mapping' => $mapping,
        ]);
    }
}
