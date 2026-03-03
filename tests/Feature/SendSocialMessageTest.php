<?php

namespace Tests\Feature;

use App\Enums\Channel;
use App\Enums\MessageStatus;
use App\Events\SocialMessageSent;
use App\Jobs\SendSocialMessage;
use App\Models\Lead;
use App\Models\MetaAccount;
use App\Models\Tenant;
use App\Services\Meta\MetaGraphApiMockService;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SendSocialMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['logging.default' => 'null']);
        $this->app->instance(MetaGraphApiServiceInterface::class, new MetaGraphApiMockService);
    }

    public function test_send_instagram_message_creates_social_message_and_updates_lead(): void
    {
        Event::fake([SocialMessageSent::class]);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['instagram_sender_id' => 'ig_sender_123'],
        ]);

        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $tenant->id,
            'status' => 'connected',
        ]);

        SendSocialMessage::dispatchSync($lead, Channel::Instagram, 'Hello Instagram!', null);

        $this->assertDatabaseHas('social_messages', [
            'lead_id' => $lead->id,
            'channel' => Channel::Instagram->value,
            'content' => 'Hello Instagram!',
            'status' => MessageStatus::Sent->value,
        ]);

        $lead->refresh();
        $this->assertNotNull($lead->last_message_at);
        $this->assertEquals(Channel::Instagram, $lead->last_message_channel);

        Event::assertDispatched(SocialMessageSent::class);
    }

    public function test_send_facebook_message_creates_social_message(): void
    {
        Event::fake([SocialMessageSent::class]);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['facebook_psid' => 'psid_456'],
        ]);

        MetaAccount::factory()->facebookMessenger()->create([
            'tenant_id' => $tenant->id,
            'status' => 'connected',
        ]);

        SendSocialMessage::dispatchSync($lead, Channel::FacebookMessenger, 'Hello Messenger!', null);

        $this->assertDatabaseHas('social_messages', [
            'lead_id' => $lead->id,
            'channel' => Channel::FacebookMessenger->value,
            'content' => 'Hello Messenger!',
            'status' => MessageStatus::Sent->value,
        ]);

        Event::assertDispatched(SocialMessageSent::class);
    }

    public function test_send_fails_when_no_connected_account(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No connected instagram account found');

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['instagram_sender_id' => 'ig_sender_123'],
        ]);

        // No MetaAccount created
        SendSocialMessage::dispatchSync($lead, Channel::Instagram, 'Hello!', null);
    }

    public function test_send_fails_when_lead_has_no_sender_id(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lead has no instagram_sender_id');

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => [], // No sender IDs
        ]);

        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $tenant->id,
            'status' => 'connected',
        ]);

        SendSocialMessage::dispatchSync($lead, Channel::Instagram, 'Hello!', null);
    }

    public function test_send_skips_opted_out_lead(): void
    {
        Event::fake([SocialMessageSent::class]);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->optedOut()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['instagram_sender_id' => 'ig_sender_123'],
        ]);

        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $tenant->id,
            'status' => 'connected',
        ]);

        SendSocialMessage::dispatchSync($lead, Channel::Instagram, 'Hello!', null);

        $this->assertDatabaseCount('social_messages', 0);
        Event::assertNotDispatched(SocialMessageSent::class);
    }

    public function test_failed_send_updates_message_status_to_failed(): void
    {
        // Use a mock that returns failure
        $failingMock = new class extends MetaGraphApiMockService
        {
            public function sendInstagramMessage(string $recipientId, string $text, string $accessToken): array
            {
                return ['success' => false, 'error' => 'API error'];
            }
        };

        $this->app->instance(MetaGraphApiServiceInterface::class, $failingMock);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['instagram_sender_id' => 'ig_sender_123'],
        ]);

        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $tenant->id,
            'status' => 'connected',
        ]);

        try {
            SendSocialMessage::dispatchSync($lead, Channel::Instagram, 'Hello!', null);
        } catch (\Exception $e) {
            // Expected
        }

        $this->assertDatabaseHas('social_messages', [
            'lead_id' => $lead->id,
            'status' => MessageStatus::Failed->value,
        ]);
    }
}
