<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Livewire\InboxConversation;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InboxConversationDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_and_send_document(): void
    {
        Storage::fake('public');
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        WhatsAppInstance::factory()->connected()->create([
            'tenant_id' => $tenant->id,
            'instance_id' => 'test-instance',
        ]);

        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'whatsapps' => ['+351912345678'],
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->set('document', $file)
            ->set('documentCaption', 'Test document caption')
            ->call('sendDocument')
            ->assertHasNoErrors();

        // Verify file was stored with original name
        Storage::disk('public')->assertExists('whatsapp-documents/'.$file->getClientOriginalName());

        // Verify job was dispatched
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($lead) {
            return $job->lead->id === $lead->id
                && $job->type === 'document'
                && $job->message === 'Test document caption';
        });
    }

    public function test_cannot_send_document_to_opted_out_lead(): void
    {
        Storage::fake('public');
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'whatsapps' => ['+351912345678'],
            'opt_out' => true,
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->set('document', $file)
            ->call('sendDocument')
            ->assertHasErrors(['document' => 'Cannot send document. Lead has opted out or is marked as do not contact.']);

        // Verify job was not dispatched
        Queue::assertNotPushed(SendWhatsAppMessage::class);
    }

    public function test_document_validation_rules(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $lead = Lead::factory()->create([
            'tenant_id' => $tenant->id,
            'whatsapps' => ['+351912345678'],
        ]);

        $this->actingAs($user);

        // Test with invalid file type
        $invalidFile = UploadedFile::fake()->create('test.exe', 1024);

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->set('document', $invalidFile)
            ->call('sendDocument')
            ->assertHasErrors(['document']);

        // Test with file too large (> 20MB)
        $largeFile = UploadedFile::fake()->create('large.pdf', 21000);

        Livewire::test(InboxConversation::class, ['leadId' => $lead->id])
            ->set('document', $largeFile)
            ->call('sendDocument')
            ->assertHasErrors(['document']);
    }
}
