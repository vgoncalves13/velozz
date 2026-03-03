<?php

namespace Tests\Feature;

use App\Enums\Channel;
use App\Models\Lead;
use App\Models\MetaAccount;
use App\Models\SocialMessage;
use App\Models\Tenant;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboxOmnichannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_all_messages_returns_whatsapp_and_social_messages_merged(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);
        $instance = WhatsAppInstance::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->instagram()->create(['tenant_id' => $tenant->id]);

        $whatsappMsg = WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'whatsapp_instance_id' => $instance->id,
            'type' => 'text',
            'direction' => 'incoming',
            'content' => 'WhatsApp message',
            'status' => 'delivered',
        ]);

        $socialMsg = SocialMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'meta_account_id' => $metaAccount->id,
            'channel' => Channel::Instagram->value,
            'direction' => 'incoming',
            'type' => 'text',
            'content' => 'Instagram message',
            'status' => 'delivered',
            'sender_id' => 'sender_123',
        ]);

        $lead->refresh();
        $allMessages = $lead->allMessages();

        $this->assertCount(2, $allMessages);
        $contents = $allMessages->pluck('content')->toArray();
        $this->assertContains('WhatsApp message', $contents);
        $this->assertContains('Instagram message', $contents);
    }

    public function test_lead_all_messages_are_sorted_by_created_at(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);
        $instance = WhatsAppInstance::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->instagram()->create(['tenant_id' => $tenant->id]);

        $first = WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'whatsapp_instance_id' => $instance->id,
            'type' => 'text',
            'direction' => 'incoming',
            'content' => 'First message',
            'status' => 'delivered',
            'created_at' => now()->subMinutes(10),
        ]);

        $second = SocialMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'meta_account_id' => $metaAccount->id,
            'channel' => Channel::Instagram->value,
            'direction' => 'incoming',
            'type' => 'text',
            'content' => 'Second message',
            'status' => 'delivered',
            'sender_id' => 'sender_123',
            'created_at' => now()->subMinutes(5),
        ]);

        $lead->refresh();
        $allMessages = $lead->allMessages();

        $this->assertEquals('First message', $allMessages->first()->content);
        $this->assertEquals('Second message', $allMessages->last()->content);
    }

    public function test_whatsapp_message_has_channel_set_to_whatsapp_in_all_messages(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);
        $instance = WhatsAppInstance::factory()->create(['tenant_id' => $tenant->id]);

        WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'whatsapp_instance_id' => $instance->id,
            'type' => 'text',
            'direction' => 'incoming',
            'content' => 'WhatsApp message',
            'status' => 'delivered',
        ]);

        $lead->refresh();
        $allMessages = $lead->allMessages();

        $this->assertEquals(Channel::Whatsapp, $allMessages->first()->channel);
    }

    public function test_lead_last_message_channel_is_set_correctly_for_instagram(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'last_message_at' => now(),
            'last_message_channel' => Channel::Instagram->value,
        ]);

        $lead->refresh();

        $this->assertEquals(Channel::Instagram, $lead->last_message_channel);
    }

    public function test_lead_last_message_channel_is_set_correctly_for_whatsapp(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'last_message_at' => now(),
            'last_message_channel' => Channel::Whatsapp->value,
        ]);

        $lead->refresh();

        $this->assertEquals(Channel::Whatsapp, $lead->last_message_channel);
    }

    public function test_lead_has_social_messages_relation(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->instagram()->create(['tenant_id' => $tenant->id]);

        SocialMessage::factory()->count(3)->create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'meta_account_id' => $metaAccount->id,
        ]);

        $this->assertCount(3, $lead->socialMessages);
    }

    public function test_social_message_has_correct_channel_enum_cast(): void
    {
        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->facebookMessenger()->create(['tenant_id' => $tenant->id]);

        $message = SocialMessage::factory()->facebookMessenger()->create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'meta_account_id' => $metaAccount->id,
        ]);

        $this->assertEquals(Channel::FacebookMessenger, $message->channel);
    }
}
