<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_lead_can_be_created_with_required_fields(): void
    {
        $tenant = Tenant::factory()->create();

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'full_name' => 'John Doe',
            'source' => 'manual',
            'priority' => 'medium',
            'consent_status' => 'pending',
        ]);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'John Doe',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_lead_can_be_updated(): void
    {
        $lead = Lead::factory()->create(['full_name' => 'Original Name']);

        $lead->update(['full_name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $lead->fresh()->full_name);
    }

    public function test_lead_can_be_soft_deleted(): void
    {
        $lead = Lead::factory()->create();

        $lead->delete();

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    public function test_lead_can_have_multiple_phone_numbers(): void
    {
        $lead = Lead::factory()->create([
            'phones' => [
                '+351912345678',
                '+351987654321',
            ],
        ]);

        $this->assertCount(2, $lead->phones);
        $this->assertContains('+351912345678', $lead->phones);
    }

    public function test_lead_can_have_multiple_whatsapp_numbers(): void
    {
        $lead = Lead::factory()->create([
            'whatsapps' => [
                '+351912345678',
                '+351987654321',
            ],
            'primary_whatsapp_index' => 0,
        ]);

        $this->assertCount(2, $lead->whatsapps);
        $this->assertEquals(0, $lead->primary_whatsapp_index);
        $this->assertEquals('+351912345678', $lead->primary_whatsapp);
    }

    public function test_lead_can_be_assigned_to_user(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);

        $lead->update(['assigned_user_id' => $user->id]);

        $this->assertEquals($user->id, $lead->fresh()->assigned_user_id);
        $this->assertInstanceOf(User::class, $lead->assignedUser);
    }

    public function test_lead_can_have_pipeline_stage(): void
    {
        $tenant = Tenant::factory()->create();
        $stage = PipelineStage::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);

        $lead->update(['pipeline_stage_id' => $stage->id]);

        $this->assertEquals($stage->id, $lead->fresh()->pipeline_stage_id);
        $this->assertInstanceOf(PipelineStage::class, $lead->pipelineStage);
    }

    public function test_lead_with_opt_out_flag(): void
    {
        $lead = Lead::factory()->optedOut()->create();

        $this->assertTrue($lead->opt_out);
        $this->assertNotNull($lead->opt_out_date);
    }

    public function test_lead_with_do_not_contact_flag(): void
    {
        $lead = Lead::factory()->doNotContact()->create();

        $this->assertTrue($lead->do_not_contact);
    }

    public function test_lead_can_have_custom_fields(): void
    {
        $lead = Lead::factory()->create([
            'custom_fields' => [
                'cpf' => '123.456.789-00',
                'birthday' => '1990-01-01',
            ],
        ]);

        $this->assertEquals('123.456.789-00', $lead->custom_fields['cpf']);
        $this->assertEquals('1990-01-01', $lead->custom_fields['birthday']);
    }

    public function test_lead_can_have_tags(): void
    {
        $lead = Lead::factory()->create([
            'tags' => ['vip', 'hot-lead', 'follow-up'],
        ]);

        $this->assertCount(3, $lead->tags);
        $this->assertContains('vip', $lead->tags);
    }

    public function test_lead_priority_levels(): void
    {
        $lowLead = Lead::factory()->create(['priority' => 'low']);
        $urgentLead = Lead::factory()->highPriority()->create();

        $this->assertEquals('low', $lowLead->priority);
        $this->assertEquals('urgent', $urgentLead->priority);
    }

    public function test_lead_consent_status(): void
    {
        $lead = Lead::factory()->create(['consent_status' => 'granted']);

        $this->assertEquals('granted', $lead->consent_status);
    }

    public function test_leads_are_isolated_by_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $lead1 = Lead::factory()->create(['tenant_id' => $tenant1->id, 'full_name' => 'Lead 1']);
        $lead2 = Lead::factory()->create(['tenant_id' => $tenant2->id, 'full_name' => 'Lead 2']);

        $tenant1Leads = Lead::where('tenant_id', $tenant1->id)->get();
        $tenant2Leads = Lead::where('tenant_id', $tenant2->id)->get();

        $this->assertCount(1, $tenant1Leads);
        $this->assertCount(1, $tenant2Leads);
        $this->assertEquals('Lead 1', $tenant1Leads->first()->full_name);
        $this->assertEquals('Lead 2', $tenant2Leads->first()->full_name);
    }

    public function test_lead_has_activities_relationship(): void
    {
        $lead = Lead::factory()->create();

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'type' => 'created',
            'description' => 'Lead was created',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $lead->activities);
        $this->assertCount(1, $lead->activities);
    }

    public function test_lead_address_fields(): void
    {
        $lead = Lead::factory()->create([
            'street_name' => 'Main Street',
            'number' => '123',
            'city' => 'Lisbon',
            'postal_code' => '1000-001',
            'country' => 'Portugal',
        ]);

        $this->assertEquals('Main Street', $lead->street_name);
        $this->assertEquals('123', $lead->number);
        $this->assertEquals('Lisbon', $lead->city);
        $this->assertEquals('Portugal', $lead->country);
    }

    public function test_lead_source_types(): void
    {
        $importLead = Lead::factory()->create(['source' => \App\Enums\LeadSource::Import]);
        $manualLead = Lead::factory()->create(['source' => \App\Enums\LeadSource::Manual]);
        $apiLead = Lead::factory()->create(['source' => \App\Enums\LeadSource::Api]);
        $formLead = Lead::factory()->create(['source' => \App\Enums\LeadSource::Form]);

        $this->assertEquals(\App\Enums\LeadSource::Import, $importLead->source);
        $this->assertEquals(\App\Enums\LeadSource::Manual, $manualLead->source);
        $this->assertEquals(\App\Enums\LeadSource::Api, $apiLead->source);
        $this->assertEquals(\App\Enums\LeadSource::Form, $formLead->source);
    }
}
