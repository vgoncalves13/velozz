<?php

namespace Tests\Feature;

use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\WhatsAppWidget;
use App\Services\ZApi\ZApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_whatsapp_widget_can_be_created(): void
    {
        $tenant = Tenant::factory()->create();
        $widget = WhatsAppWidget::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertDatabaseHas('whatsapp_widgets', [
            'id' => $widget->id,
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_whatsapp_widget_can_be_soft_deleted(): void
    {
        $widget = WhatsAppWidget::factory()->create();
        $widget->delete();

        $this->assertSoftDeleted('whatsapp_widgets', ['id' => $widget->id]);
    }

    public function test_embed_script_returns_javascript_for_active_widget(): void
    {
        $widget = WhatsAppWidget::factory()->create(['status' => 'active']);

        $response = $this->get('/embed/whatsapp-'.$widget->id.'.js');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript');
        $response->assertSee('var widgetId = '.$widget->id, false);
    }

    public function test_embed_script_returns_404_for_inactive_widget(): void
    {
        $widget = WhatsAppWidget::factory()->inactive()->create();

        $response = $this->get('/embed/whatsapp-'.$widget->id.'.js');

        $response->assertStatus(404);
    }

    public function test_widget_submission_creates_lead(): void
    {
        $this->mock(ZApiServiceInterface::class)
            ->shouldReceive('sendMessage')
            ->andReturn([]);

        $widget = WhatsAppWidget::factory()->create(['status' => 'active']);

        $response = $this->postJson('/api/widgets/whatsapp/'.$widget->id.'/submit', [
            'nome' => 'João Silva',
            'telefone' => '+351912345678',
            'email' => 'joao@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'João Silva',
            'email' => 'joao@example.com',
            'source' => 'whatsapp_widget',
            'tenant_id' => $widget->tenant_id,
        ]);
    }

    public function test_widget_submission_validates_required_fields(): void
    {
        $widget = WhatsAppWidget::factory()->create(['status' => 'active']);

        $response = $this->postJson('/api/widgets/whatsapp/'.$widget->id.'/submit', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nome', 'telefone']);
    }

    public function test_widget_submission_sets_lead_source_to_whatsapp_widget(): void
    {
        $this->mock(ZApiServiceInterface::class)
            ->shouldReceive('sendMessage')
            ->andReturn([]);

        $widget = WhatsAppWidget::factory()->create(['status' => 'active']);

        $this->postJson('/api/widgets/whatsapp/'.$widget->id.'/submit', [
            'nome' => 'Test User',
            'telefone' => '+351912345678',
        ]);

        $lead = Lead::withoutGlobalScopes()->where('tenant_id', $widget->tenant_id)->first();

        $this->assertNotNull($lead);
        $this->assertEquals(LeadSource::WhatsappWidget, $lead->source);
    }

    public function test_widget_submission_returns_404_for_inactive_widget(): void
    {
        $widget = WhatsAppWidget::factory()->inactive()->create();

        $response = $this->postJson('/api/widgets/whatsapp/'.$widget->id.'/submit', [
            'nome' => 'Test',
            'telefone' => '+351912345678',
        ]);

        $response->assertStatus(404);
    }

    public function test_widget_submission_email_is_optional(): void
    {
        $this->mock(ZApiServiceInterface::class)
            ->shouldReceive('sendMessage')
            ->andReturn([]);

        $widget = WhatsAppWidget::factory()->create(['status' => 'active']);

        $response = $this->postJson('/api/widgets/whatsapp/'.$widget->id.'/submit', [
            'nome' => 'Test User',
            'telefone' => '+351912345678',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_whatsapp_widget_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $widget = WhatsAppWidget::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $widget->tenant);
        $this->assertEquals($tenant->id, $widget->tenant->id);
    }

    public function test_widgets_are_scoped_by_tenant(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        WhatsAppWidget::factory()->create(['tenant_id' => $tenant1->id]);
        WhatsAppWidget::factory()->create(['tenant_id' => $tenant2->id]);

        $tenant1Widgets = WhatsAppWidget::where('tenant_id', $tenant1->id)->get();
        $tenant2Widgets = WhatsAppWidget::where('tenant_id', $tenant2->id)->get();

        $this->assertCount(1, $tenant1Widgets);
        $this->assertCount(1, $tenant2Widgets);
    }
}
