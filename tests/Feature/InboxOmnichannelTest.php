<?php

namespace Tests\Feature;

use App\Enums\Channel;
use App\Jobs\SendSocialMessage;
use App\Livewire\InboxConversation;
use App\Models\Lead;
use App\Models\MetaAccount;
use App\Models\SocialMessage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
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

    public function test_view_lead_url_is_present_in_conversation_header(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->assertSee(__('inbox.labels.view_lead'));
    }

    public function test_merge_lead_reassigns_messages_and_deletes_secondary(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $instance = WhatsAppInstance::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->instagram()->create(['tenant_id' => $tenant->id]);

        $primary = Lead::factory()->create(['tenant_id' => $tenant->id]);
        $secondary = Lead::factory()->create(['tenant_id' => $tenant->id]);

        WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $secondary->id,
            'whatsapp_instance_id' => $instance->id,
            'type' => 'text',
            'direction' => 'incoming',
            'content' => 'Secondary WhatsApp',
            'status' => 'delivered',
        ]);

        SocialMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $secondary->id,
            'meta_account_id' => $metaAccount->id,
            'channel' => Channel::Instagram->value,
            'direction' => 'incoming',
            'type' => 'text',
            'content' => 'Secondary Instagram',
            'status' => 'delivered',
            'sender_id' => 'sender_123',
        ]);

        $this->actingAs($user);

        Livewire::test(InboxConversation::class, ['leadId' => $primary->id])
            ->set('mergeTargetLeadId', $secondary->id)
            ->call('confirmMerge')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('whatsapp_messages', ['lead_id' => $primary->id, 'content' => 'Secondary WhatsApp']);
        $this->assertDatabaseHas('social_messages', ['lead_id' => $primary->id, 'content' => 'Secondary Instagram']);
        $this->assertSoftDeleted('leads', ['id' => $secondary->id]);
    }

    public function test_merge_lead_copies_custom_fields_from_secondary(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->facebookMessenger()->create(['tenant_id' => $tenant->id]);

        $primary = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => [],
        ]);

        $secondary = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['facebook_psid' => 'psid_from_secondary'],
        ]);

        SocialMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $secondary->id,
            'meta_account_id' => $metaAccount->id,
            'channel' => Channel::FacebookMessenger->value,
            'direction' => 'incoming',
            'type' => 'text',
            'content' => 'msg',
            'status' => 'delivered',
            'sender_id' => 'psid_from_secondary',
        ]);

        $this->actingAs($user);

        Livewire::test(InboxConversation::class, ['leadId' => $primary->id])
            ->set('mergeTargetLeadId', $secondary->id)
            ->call('confirmMerge')
            ->assertHasNoErrors();

        $primary->refresh();

        $this->assertEquals('psid_from_secondary', $primary->custom_fields['facebook_psid']);
    }

    public function test_merge_lead_merges_whatsapp_numbers(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $instance = WhatsAppInstance::factory()->create(['tenant_id' => $tenant->id]);

        $primary = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'whatsapps' => ['+351911111111'],
        ]);

        $secondary = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'whatsapps' => ['+351922222222', '+351911111111'],
        ]);

        WhatsAppMessage::create([
            'tenant_id' => $tenant->id,
            'lead_id' => $secondary->id,
            'whatsapp_instance_id' => $instance->id,
            'type' => 'text',
            'direction' => 'incoming',
            'content' => 'msg',
            'status' => 'delivered',
        ]);

        $this->actingAs($user);

        Livewire::test(InboxConversation::class, ['leadId' => $primary->id])
            ->set('mergeTargetLeadId', $secondary->id)
            ->call('confirmMerge')
            ->assertHasNoErrors();

        $primary->refresh();

        $this->assertCount(2, $primary->whatsapps);
        $this->assertContains('+351911111111', $primary->whatsapps);
        $this->assertContains('+351922222222', $primary->whatsapps);
    }

    public function test_channel_selector_appears_when_lead_has_multiple_channels(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'whatsapps' => ['+351911111111'],
            'custom_fields' => ['facebook_psid' => 'psid_abc'],
            'last_message_channel' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(InboxConversation::class, ['leadId' => $lead->id]);

        $availableChannels = $component->instance()->availableChannels;

        $this->assertCount(2, $availableChannels);
        $this->assertContains(Channel::Whatsapp, $availableChannels);
        $this->assertContains(Channel::FacebookMessenger, $availableChannels);
    }

    public function test_select_channel_sets_preferred_channel_override(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['facebook_psid' => 'psid_abc'],
            'last_message_channel' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->call('selectChannel', Channel::FacebookMessenger->value)
            ->assertSet('preferredChannelOverride', Channel::FacebookMessenger->value);
    }

    public function test_send_message_uses_preferred_channel_override(): void
    {
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $metaAccount = MetaAccount::factory()->facebookMessenger()->create(['tenant_id' => $tenant->id]);

        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'custom_fields' => ['facebook_psid' => 'psid_abc'],
            'last_message_channel' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->call('selectChannel', Channel::FacebookMessenger->value)
            ->set('newMessage', 'Hello via Facebook')
            ->call('sendMessage')
            ->assertHasNoErrors();

        Queue::assertPushed(SendSocialMessage::class, function ($job) use ($lead) {
            return $job->lead->id === $lead->id
                && $job->channel === Channel::FacebookMessenger;
        });
    }
}
