<?php

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Jobs\SyncFacebookLeadFormLeads;
use App\Models\FacebookLeadForm;
use App\Models\MetaAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Meta\MetaGraphApiMockService;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class FacebookLeadAdsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected MetaAccount $metaAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(MetaGraphApiServiceInterface::class, new MetaGraphApiMockService);

        $this->tenant = Tenant::factory()->create(['slug' => 'test-tenant']);
        $this->metaAccount = MetaAccount::factory()->facebookMessenger()->create([
            'tenant_id' => $this->tenant->id,
            'page_id' => '113017214917670',
        ]);
    }

    public function test_leadgen_webhook_creates_lead_for_subscribed_form(): void
    {
        FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => '551111744595541',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => '113017214917670',
                    'time' => now()->timestamp,
                    'changes' => [
                        [
                            'field' => 'leadgen',
                            'value' => [
                                'leadgen_id' => 'mock_lead_1',
                                'form_id' => '551111744595541',
                                'page_id' => '113017214917670',
                                'created_time' => now()->timestamp,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'source' => LeadSource::FacebookLeadAd->value,
            'full_name' => 'Mock Lead',
            'email' => 'mock@example.com',
        ]);

        $this->assertDatabaseHas('lead_activities', ['tenant_id' => $this->tenant->id]);
    }

    public function test_leadgen_webhook_ignores_unregistered_form(): void
    {
        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => '113017214917670',
                    'changes' => [
                        [
                            'field' => 'leadgen',
                            'value' => [
                                'leadgen_id' => 'some_lead_id',
                                'form_id' => 'unregistered_form_id',
                                'page_id' => '113017214917670',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhook/meta', $payload)->assertOk();
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_leadgen_webhook_is_idempotent(): void
    {
        FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => '551111744595541',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => '113017214917670',
                    'changes' => [
                        [
                            'field' => 'leadgen',
                            'value' => [
                                'leadgen_id' => 'mock_lead_1',
                                'form_id' => '551111744595541',
                                'page_id' => '113017214917670',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhook/meta', $payload);
        $this->postJson('/api/webhook/meta', $payload);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_sync_job_creates_leads_from_form(): void
    {
        $form = FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Mock Lead Form',
            'active' => true,
        ]);

        (new SyncFacebookLeadFormLeads($form))->handle(new MetaGraphApiMockService);

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'source' => LeadSource::FacebookLeadAd->value,
            'full_name' => 'Mock Lead',
            'email' => 'mock@example.com',
        ]);

        $this->assertNotNull($form->refresh()->last_synced_at);
    }

    public function test_sync_job_is_idempotent(): void
    {
        $form = FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Mock Lead Form',
            'active' => true,
        ]);

        $job = new SyncFacebookLeadFormLeads($form);
        $job->handle(new MetaGraphApiMockService);
        $job->handle(new MetaGraphApiMockService);

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_toggle_lead_form_creates_subscription_and_opens_mapping_modal(): void
    {
        Queue::fake();

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        Livewire::test(\App\Filament\Client\Pages\MetaAccountSettings::class)
            ->call('toggleLeadForm', $this->metaAccount->id, 'mock_form_1', 'Test Form')
            ->assertDispatched('open-modal', id: 'lead-form-field-mapping')
            ->assertSet('mappingFormName', 'Test Form');

        $this->assertDatabaseHas('facebook_lead_forms', [
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        Queue::assertNotPushed(SyncFacebookLeadFormLeads::class);
    }

    public function test_toggle_lead_form_removes_existing_subscription(): void
    {
        Queue::fake();

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        Livewire::test(\App\Filament\Client\Pages\MetaAccountSettings::class)
            ->call('toggleLeadForm', $this->metaAccount->id, 'mock_form_1', 'Test Form');

        $this->assertDatabaseMissing('facebook_lead_forms', [
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
        ]);

        Queue::assertNotPushed(SyncFacebookLeadFormLeads::class);
    }

    public function test_save_mapping_and_sync_dispatches_job_and_saves_mapping(): void
    {
        Queue::fake();

        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $form = FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        Livewire::test(\App\Filament\Client\Pages\MetaAccountSettings::class)
            ->set('mappingFormDbId', $form->id)
            ->set('mappedNameField', 'full_name')
            ->set('mappedEmailField', 'email')
            ->set('mappedPhoneField', 'phone_number')
            ->call('saveMappingAndSync')
            ->assertDispatched('close-modal', id: 'lead-form-field-mapping');

        Queue::assertPushed(SyncFacebookLeadFormLeads::class);

        $this->assertDatabaseHas('facebook_lead_forms', [
            'id' => $form->id,
        ]);

        $form->refresh();
        $this->assertEquals('full_name', $form->field_mapping['name']);
        $this->assertEquals('email', $form->field_mapping['email']);
        $this->assertEquals('phone_number', $form->field_mapping['phone']);
    }

    public function test_sync_job_respects_field_mapping(): void
    {
        $form = FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Mock Lead Form',
            'active' => true,
            'field_mapping' => [
                'name' => 'full_name',
                'email' => 'email',
                'phone' => 'phone_number',
            ],
        ]);

        (new SyncFacebookLeadFormLeads($form))->handle(new MetaGraphApiMockService);

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'source' => LeadSource::FacebookLeadAd->value,
            'full_name' => 'Mock Lead',
            'email' => 'mock@example.com',
        ]);
    }

    public function test_open_mapping_modal_populates_available_fields(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($user);

        $form = FacebookLeadForm::create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'mock_form_1',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        $livewire = Livewire::test(\App\Filament\Client\Pages\MetaAccountSettings::class)
            ->call('openMappingModal', $form->id)
            ->assertDispatched('open-modal', id: 'lead-form-field-mapping')
            ->assertSet('mappingFormName', 'Test Form');

        $this->assertNotEmpty($livewire->get('availableFormFields'));
    }
}
