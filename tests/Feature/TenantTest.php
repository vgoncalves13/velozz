<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_tenant_can_be_created(): void
    {
        $plan = Plan::factory()->create();

        $tenant = Tenant::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'domain' => 'test-company.velozz.test',
            'status' => 'active',
            'plan_id' => $plan->id,
            'admin_name' => 'John Doe',
            'admin_email' => 'john@test.com',
            'admin_phone' => '+351912345678',
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Company',
            'slug' => 'test-company',
            'status' => 'active',
        ]);
    }

    public function test_tenant_data_is_isolated(): void
    {
        // Create two tenants
        $tenant1 = Tenant::factory()->create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::factory()->create(['name' => 'Tenant 2']);

        // Create users for each tenant
        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        // Create leads for each tenant
        $lead1 = Lead::factory()->create([
            'tenant_id' => $tenant1->id,
            'full_name' => 'Lead from Tenant 1',
        ]);

        $lead2 = Lead::factory()->create([
            'tenant_id' => $tenant2->id,
            'full_name' => 'Lead from Tenant 2',
        ]);

        // Verify tenant 1 can only see their lead
        $tenant1Leads = Lead::where('tenant_id', $tenant1->id)->get();
        $this->assertCount(1, $tenant1Leads);
        $this->assertEquals('Lead from Tenant 1', $tenant1Leads->first()->full_name);

        // Verify tenant 2 can only see their lead
        $tenant2Leads = Lead::where('tenant_id', $tenant2->id)->get();
        $this->assertCount(1, $tenant2Leads);
        $this->assertEquals('Lead from Tenant 2', $tenant2Leads->first()->full_name);
    }

    public function test_tenant_settings_can_be_updated(): void
    {
        $tenant = Tenant::factory()->create();

        $tenant->update([
            'settings' => [
                'primary_color' => '#10B981',
                'secondary_color' => '#F59E0B',
                'business_hours' => [
                    'start' => '09:00',
                    'end' => '18:00',
                ],
            ],
        ]);

        $this->assertEquals('#10B981', $tenant->fresh()->settings['primary_color']);
        $this->assertEquals('09:00', $tenant->fresh()->settings['business_hours']['start']);
    }

    public function test_tenant_can_have_custom_fields(): void
    {
        $tenant = Tenant::factory()->create();

        $tenant->update([
            'settings' => [
                'custom_fields' => [
                    [
                        'name' => 'cpf',
                        'type' => 'text',
                        'label' => 'CPF',
                    ],
                    [
                        'name' => 'birthday',
                        'type' => 'date',
                        'label' => 'Birthday',
                    ],
                ],
            ],
        ]);

        $customFields = $tenant->fresh()->settings['custom_fields'];
        $this->assertCount(2, $customFields);
        $this->assertEquals('cpf', $customFields[0]['name']);
        $this->assertEquals('date', $customFields[1]['type']);
    }

    public function test_tenant_api_key_can_be_generated(): void
    {
        $tenant = Tenant::factory()->create();

        // Initially no API key
        $this->assertNull($tenant->settings['api_key'] ?? null);

        // Generate API key
        $apiKey = 'vz_'.bin2hex(random_bytes(32));
        $tenant->update([
            'settings' => array_merge($tenant->settings ?? [], [
                'api_key' => $apiKey,
            ]),
        ]);

        // Verify API key was saved
        $this->assertNotNull($tenant->fresh()->settings['api_key']);
        $this->assertStringStartsWith('vz_', $tenant->fresh()->settings['api_key']);
    }

    public function test_tenant_webhooks_can_be_configured(): void
    {
        $tenant = Tenant::factory()->create();

        $tenant->update([
            'settings' => [
                'webhooks' => [
                    [
                        'url' => 'https://example.com/webhook',
                        'events' => ['lead_created', 'message_sent'],
                    ],
                ],
            ],
        ]);

        $webhooks = $tenant->fresh()->settings['webhooks'];
        $this->assertCount(1, $webhooks);
        $this->assertEquals('https://example.com/webhook', $webhooks[0]['url']);
        $this->assertContains('lead_created', $webhooks[0]['events']);
    }

    public function test_tenant_status_transitions(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'trial']);

        // Trial to active
        $tenant->update(['status' => 'active']);
        $this->assertEquals('active', $tenant->fresh()->status);

        // Active to suspended
        $tenant->update(['status' => 'suspended']);
        $this->assertEquals('suspended', $tenant->fresh()->status);

        // Suspended to active
        $tenant->update(['status' => 'active']);
        $this->assertEquals('active', $tenant->fresh()->status);
    }

    public function test_tenant_belongs_to_plan(): void
    {
        $plan = Plan::factory()->create(['name' => 'Pro Plan']);
        $tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

        $this->assertInstanceOf(Plan::class, $tenant->plan);
        $this->assertEquals('Pro Plan', $tenant->plan->name);
    }
}
