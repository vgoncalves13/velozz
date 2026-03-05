<?php

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Models\EmbeddedForm;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbeddedFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_embedded_form_can_be_created(): void
    {
        $tenant = Tenant::factory()->create();
        $form = EmbeddedForm::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertDatabaseHas('embedded_forms', [
            'id' => $form->id,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_embedded_form_can_be_soft_deleted(): void
    {
        $form = EmbeddedForm::factory()->create();
        $form->delete();

        $this->assertSoftDeleted('embedded_forms', ['id' => $form->id]);
    }

    public function test_public_form_show_page_renders_for_active_form(): void
    {
        $form = EmbeddedForm::factory()->create(['status' => 'active']);

        $response = $this->get('/forms/'.$form->slug);

        $response->assertStatus(200);
        $response->assertSee($form->name);
    }

    public function test_public_form_show_page_returns_404_for_inactive_form(): void
    {
        $form = EmbeddedForm::factory()->inactive()->create();

        $response = $this->get('/forms/'.$form->slug);

        $response->assertStatus(404);
    }

    public function test_public_form_show_page_returns_404_for_nonexistent_form(): void
    {
        $response = $this->get('/forms/does-not-exist');

        $response->assertStatus(404);
    }

    public function test_embed_script_returns_javascript_for_active_form(): void
    {
        $form = EmbeddedForm::factory()->create(['status' => 'active']);

        $response = $this->get('/embed/form-'.$form->id.'.js');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript');
        $response->assertSee($form->slug, false);
    }

    public function test_embed_script_returns_404_for_inactive_form(): void
    {
        $form = EmbeddedForm::factory()->inactive()->create();

        $response = $this->get('/embed/form-'.$form->id.'.js');

        $response->assertStatus(404);
    }

    public function test_form_submission_creates_lead(): void
    {
        $form = EmbeddedForm::factory()->create([
            'status' => 'active',
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'required' => true,
                    'options' => [],
                    'order' => 0,
                ],
                [
                    'type' => 'email',
                    'label' => 'Email',
                    'name' => 'email',
                    'required' => false,
                    'options' => [],
                    'order' => 1,
                ],
            ],
        ]);

        $response = $this->postJson('/api/forms/'.$form->slug.'/submit', [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'source' => 'embedded_form',
            'tenant_id' => $form->tenant_id,
        ]);
    }

    public function test_form_submission_validates_required_fields(): void
    {
        $form = EmbeddedForm::factory()->create([
            'status' => 'active',
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'required' => true,
                    'options' => [],
                    'order' => 0,
                ],
            ],
        ]);

        $response = $this->postJson('/api/forms/'.$form->slug.'/submit', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['full_name']);
    }

    public function test_form_submission_sets_lead_source_to_embedded_form(): void
    {
        $form = EmbeddedForm::factory()->create([
            'status' => 'active',
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Full Name',
                    'name' => 'full_name',
                    'required' => true,
                    'options' => [],
                    'order' => 0,
                ],
            ],
        ]);

        $this->postJson('/api/forms/'.$form->slug.'/submit', [
            'full_name' => 'Test User',
        ]);

        $lead = Lead::withoutGlobalScopes()->where('tenant_id', $form->tenant_id)->first();

        $this->assertNotNull($lead);
        $this->assertEquals(LeadSource::EmbeddedForm, $lead->source);
    }

    public function test_form_submission_returns_404_for_inactive_form(): void
    {
        $form = EmbeddedForm::factory()->inactive()->create();

        $response = $this->postJson('/api/forms/'.$form->slug.'/submit', [
            'full_name' => 'Test',
        ]);

        $response->assertStatus(404);
    }

    public function test_embedded_form_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $form = EmbeddedForm::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $form->tenant);
        $this->assertEquals($tenant->id, $form->tenant->id);
    }

    public function test_forms_are_scoped_by_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        EmbeddedForm::factory()->create(['tenant_id' => $tenant1->id]);
        EmbeddedForm::factory()->create(['tenant_id' => $tenant2->id]);

        $tenant1Forms = EmbeddedForm::where('tenant_id', $tenant1->id)->get();
        $tenant2Forms = EmbeddedForm::where('tenant_id', $tenant2->id)->get();

        $this->assertCount(1, $tenant1Forms);
        $this->assertCount(1, $tenant2Forms);
    }
}
