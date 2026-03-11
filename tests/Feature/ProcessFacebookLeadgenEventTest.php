<?php

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Jobs\ProcessFacebookLeadgenEvent;
use App\Models\FacebookLeadForm;
use App\Models\Lead;
use App\Models\MetaAccount;
use App\Models\Tenant;
use App\Services\Meta\MetaGraphApiMockService;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessFacebookLeadgenEventTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected MetaAccount $metaAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(MetaGraphApiServiceInterface::class, new MetaGraphApiMockService);

        $this->tenant = Tenant::factory()->create();
        $this->metaAccount = MetaAccount::factory()->facebookMessenger()->create([
            'tenant_id' => $this->tenant->id,
            'page_id' => '111222333',
        ]);
    }

    public function test_job_creates_lead_for_active_form(): void
    {
        $form = FacebookLeadForm::factory()->create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'form_abc',
            'form_name' => 'Test Form',
            'active' => true,
        ]);

        $job = new ProcessFacebookLeadgenEvent('leadgen_001', $form->form_id, $this->metaAccount);
        $job->handle(app(MetaGraphApiServiceInterface::class));

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'source' => LeadSource::FacebookLeadAd->value,
            'full_name' => 'Mock Lead',
            'email' => 'mock@example.com',
        ]);

        $lead = Lead::where('tenant_id', $this->tenant->id)->first();
        $this->assertNotNull($lead);
        $this->assertEquals('leadgen_001', $lead->custom_fields['facebook_lead_id']);
        $this->assertEquals($form->form_id, $lead->custom_fields['facebook_form_id']);
    }

    public function test_job_creates_lead_activity(): void
    {
        $form = FacebookLeadForm::factory()->create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'form_abc',
        ]);

        $job = new ProcessFacebookLeadgenEvent('leadgen_002', $form->form_id, $this->metaAccount);
        $job->handle(app(MetaGraphApiServiceInterface::class));

        $lead = Lead::where('tenant_id', $this->tenant->id)->first();

        $this->assertDatabaseHas('lead_activities', [
            'tenant_id' => $this->tenant->id,
            'lead_id' => $lead->id,
        ]);
    }

    public function test_job_skips_inactive_form(): void
    {
        $form = FacebookLeadForm::factory()->inactive()->create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'form_inactive',
        ]);

        $job = new ProcessFacebookLeadgenEvent('leadgen_003', $form->form_id, $this->metaAccount);
        $job->handle(app(MetaGraphApiServiceInterface::class));

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_job_skips_form_not_in_system(): void
    {
        $job = new ProcessFacebookLeadgenEvent('leadgen_004', 'unknown_form_id', $this->metaAccount);
        $job->handle(app(MetaGraphApiServiceInterface::class));

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_job_does_not_create_duplicate_lead(): void
    {
        $form = FacebookLeadForm::factory()->create([
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $this->metaAccount->id,
            'form_id' => 'form_abc',
        ]);

        Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'custom_fields' => ['facebook_lead_id' => 'leadgen_dup'],
        ]);

        $job = new ProcessFacebookLeadgenEvent('leadgen_dup', $form->form_id, $this->metaAccount);
        $job->handle(app(MetaGraphApiServiceInterface::class));

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_webhook_dispatches_job_for_leadgen_event(): void
    {
        Queue::fake();

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => $this->metaAccount->page_id,
                    'changes' => [
                        [
                            'field' => 'leadgen',
                            'value' => [
                                'leadgen_id' => 'leadgen_777',
                                'form_id' => 'form_xyz',
                                'page_id' => $this->metaAccount->page_id,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();

        Queue::assertPushed(ProcessFacebookLeadgenEvent::class, function ($job) {
            return $job->leadgenId === 'leadgen_777' && $job->formId === 'form_xyz';
        });
    }

    public function test_webhook_ignores_leadgen_for_unknown_page(): void
    {
        Queue::fake();

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => 'unknown_page_id',
                    'changes' => [
                        [
                            'field' => 'leadgen',
                            'value' => [
                                'leadgen_id' => 'leadgen_888',
                                'form_id' => 'form_xyz',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();
        Queue::assertNothingPushed();
    }
}
