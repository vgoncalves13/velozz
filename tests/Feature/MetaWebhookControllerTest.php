<?php

namespace Tests\Feature;

use App\Enums\Channel;
use App\Enums\MessageDirection;
use App\Events\SocialMessageReceived;
use App\Models\Lead;
use App\Models\MetaAccount;
use App\Models\SocialMessage;
use App\Models\Tenant;
use App\Services\Meta\MetaGraphApiMockService;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MetaWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(MetaGraphApiServiceInterface::class, new MetaGraphApiMockService);

        $this->tenant = Tenant::factory()->create(['slug' => 'test-tenant']);
    }

    public function test_webhook_verify_returns_challenge_with_valid_token(): void
    {
        $token = config('services.meta.webhook_token', 'meta_webhook_token');

        $response = $this->get("/api/webhook/meta?hub_mode=subscribe&hub_verify_token={$token}&hub_challenge=abc123");

        $response->assertOk();
        $response->assertSee('abc123');
    }

    public function test_webhook_verify_fails_with_invalid_token(): void
    {
        $response = $this->get('/api/webhook/meta?hub_mode=subscribe&hub_verify_token=wrong-token&hub_challenge=abc123');

        $response->assertForbidden();
    }

    public function test_instagram_webhook_creates_message_and_lead(): void
    {
        Event::fake([SocialMessageReceived::class]);

        $metaAccount = MetaAccount::factory()->instagram()->create([
            'tenant_id' => $this->tenant->id,
            'instagram_user_id' => '123456789',
        ]);

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '123456789',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'sender_999'],
                            'recipient' => ['id' => '123456789'],
                            'timestamp' => 1458692752478,
                            'message' => [
                                'mid' => 'mid.1234567890',
                                'text' => 'Hello from Instagram!',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();

        $this->assertDatabaseHas('social_messages', [
            'tenant_id' => $this->tenant->id,
            'meta_account_id' => $metaAccount->id,
            'channel' => Channel::Instagram->value,
            'direction' => MessageDirection::Incoming->value,
            'content' => 'Hello from Instagram!',
            'external_message_id' => 'mid.1234567890',
            'sender_id' => 'sender_999',
        ]);

        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'source' => 'instagram',
        ]);

        Event::assertDispatched(SocialMessageReceived::class);
    }

    public function test_facebook_messenger_webhook_creates_message_and_lead(): void
    {
        Event::fake([SocialMessageReceived::class]);

        MetaAccount::factory()->facebookMessenger()->create([
            'tenant_id' => $this->tenant->id,
            'page_id' => '987654321',
        ]);

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => '987654321',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'psid_111'],
                            'recipient' => ['id' => '987654321'],
                            'timestamp' => 1458692752478,
                            'message' => [
                                'mid' => 'mid.fb.9876',
                                'text' => 'Hello from Messenger!',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();

        $this->assertDatabaseHas('social_messages', [
            'tenant_id' => $this->tenant->id,
            'channel' => Channel::FacebookMessenger->value,
            'direction' => MessageDirection::Incoming->value,
            'content' => 'Hello from Messenger!',
            'sender_id' => 'psid_111',
        ]);

        Event::assertDispatched(SocialMessageReceived::class);
    }

    public function test_webhook_skips_echo_messages(): void
    {
        Event::fake([SocialMessageReceived::class]);

        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $this->tenant->id,
            'instagram_user_id' => '123456789',
        ]);

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '123456789',
                    'messaging' => [
                        [
                            'sender' => ['id' => '123456789'],
                            'recipient' => ['id' => 'sender_999'],
                            'message' => [
                                'mid' => 'mid.echo.1',
                                'text' => 'Echo message',
                                'is_echo' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();
        $this->assertDatabaseCount('social_messages', 0);
        Event::assertNotDispatched(SocialMessageReceived::class);
    }

    public function test_webhook_updates_lead_last_message_fields(): void
    {
        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $this->tenant->id,
            'instagram_user_id' => '123456789',
        ]);

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '123456789',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'sender_777'],
                            'recipient' => ['id' => '123456789'],
                            'timestamp' => 1458692752478,
                            'message' => [
                                'mid' => 'mid.update.1',
                                'text' => 'Test message',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhook/meta', $payload);

        $lead = Lead::where('tenant_id', $this->tenant->id)->first();

        $this->assertNotNull($lead->last_message_at);
        $this->assertEquals(Channel::Instagram, $lead->last_message_channel);
    }

    public function test_webhook_uses_event_timestamp_for_last_message_at(): void
    {
        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $this->tenant->id,
            'instagram_user_id' => '123456789',
        ]);

        $timestampMs = 1458692752478;

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '123456789',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'sender_ts'],
                            'recipient' => ['id' => '123456789'],
                            'timestamp' => $timestampMs,
                            'message' => [
                                'mid' => 'mid.ts.1',
                                'text' => 'Timestamp test',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhook/meta', $payload);

        $lead = Lead::where('tenant_id', $this->tenant->id)->first();

        $this->assertEquals(
            \Carbon\Carbon::createFromTimestampMs($timestampMs)->toDateTimeString(),
            $lead->last_message_at->toDateTimeString()
        );
    }

    public function test_webhook_skips_unknown_object_type(): void
    {
        $payload = ['object' => 'unknown', 'entry' => []];

        $response = $this->postJson('/api/webhook/meta', $payload);

        $response->assertOk();
        $response->assertJsonPath('status', 'skipped');
    }

    public function test_instagram_message_reuses_existing_lead(): void
    {
        MetaAccount::factory()->instagram()->create([
            'tenant_id' => $this->tenant->id,
            'instagram_user_id' => '123456789',
        ]);

        $lead = Lead::factory()->create([
            'tenant_id' => $this->tenant->id,
            'custom_fields' => ['instagram_sender_id' => 'existing_sender'],
        ]);

        $payload = [
            'object' => 'instagram',
            'entry' => [
                [
                    'id' => '123456789',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'existing_sender'],
                            'recipient' => ['id' => '123456789'],
                            'message' => [
                                'mid' => 'mid.existing.1',
                                'text' => 'Second message',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhook/meta', $payload);

        $this->assertDatabaseCount('leads', 1);

        $message = SocialMessage::first();
        $this->assertEquals($lead->id, $message->lead_id);
    }

    public function test_webhook_routes_messages_to_correct_tenant(): void
    {
        $tenantA = $this->tenant;
        $tenantB = Tenant::factory()->create();

        MetaAccount::factory()->facebookMessenger()->create([
            'tenant_id' => $tenantA->id,
            'page_id' => 'page_A',
        ]);

        MetaAccount::factory()->facebookMessenger()->create([
            'tenant_id' => $tenantB->id,
            'page_id' => 'page_B',
        ]);

        $payload = [
            'object' => 'page',
            'entry' => [
                [
                    'id' => 'page_B',
                    'messaging' => [
                        [
                            'sender' => ['id' => 'psid_x'],
                            'recipient' => ['id' => 'page_B'],
                            'message' => ['mid' => 'mid.tenant.1', 'text' => 'Tenant B message'],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson('/api/webhook/meta', $payload);

        $this->assertDatabaseHas('social_messages', ['tenant_id' => $tenantB->id]);
        $this->assertDatabaseMissing('social_messages', ['tenant_id' => $tenantA->id]);

        $lead = Lead::where('tenant_id', $tenantB->id)->first();
        $this->assertNotNull($lead);
    }
}
