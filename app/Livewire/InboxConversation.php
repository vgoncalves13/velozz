<?php

namespace App\Livewire;

use App\Enums\Channel;
use App\Enums\LeadActivityType;
use App\Jobs\SendSocialMessage;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class InboxConversation extends Component
{
    use WithFileUploads;

    public int $leadId;

    public Lead $lead;

    public string $newMessage = '';

    public $image = null;

    public string $imageCaption = '';

    public $document = null;

    public string $documentCaption = '';

    public string $internalNote = '';

    public bool $isInternalNoteMode = false;

    public bool $showTransferModal = false;

    public ?int $transferToUserId = null;

    public int $refreshKey = 0;

    private ?int $previousIncomingCount = null;

    public function mount(int $leadId): void
    {
        $this->leadId = $leadId;
        $this->lead = Lead::with('assignedUser', 'whatsappMessages', 'socialMessages')->findOrFail($leadId);

        $this->previousIncomingCount = $this->getIncomingMessageCount();
    }

    public function getMessagesProperty()
    {
        return $this->lead->allMessages();
    }

    private function getIncomingMessageCount(): int
    {
        $whatsapp = WhatsAppMessage::where('lead_id', $this->leadId)
            ->where('direction', 'incoming')
            ->count();

        $social = \App\Models\SocialMessage::where('lead_id', $this->leadId)
            ->where('direction', 'incoming')
            ->count();

        return $whatsapp + $social;
    }

    public function hydrate(): void
    {
        $currentIncomingCount = $this->getIncomingMessageCount();

        if ($this->previousIncomingCount !== null && $currentIncomingCount > $this->previousIncomingCount) {
            $this->dispatch('new-incoming-message');
        }

        $this->previousIncomingCount = $currentIncomingCount;
    }

    public function getListeners(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return [
            'conversation-updated' => 'refreshConversationList',
            "echo-private:tenant.{$tenantId}.inbox,MessageReceived" => 'onMessageReceived',
            "echo-private:tenant.{$tenantId}.inbox,MessageSent" => 'onMessageSent',
            "echo-private:tenant.{$tenantId}.inbox,SocialMessageReceived" => 'onSocialMessageReceived',
            "echo-private:tenant.{$tenantId}.inbox,SocialMessageSent" => 'onSocialMessageSent',
        ];
    }

    public function onMessageReceived($event): void
    {
        $this->refreshKey++;
    }

    public function onMessageSent($event): void
    {
        $this->refreshKey++;
    }

    public function onSocialMessageReceived($event): void
    {
        $this->refreshKey++;
    }

    public function onSocialMessageSent($event): void
    {
        $this->refreshKey++;
    }

    public function refreshConversationList(): void
    {
        $this->refreshKey++;
    }

    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => 'required|string|min:1',
        ]);

        if ($this->lead->opt_out || $this->lead->do_not_contact) {
            $this->addError('newMessage', __('inbox.errors.cannot_send_opted_out'));

            return;
        }

        // Determine which channel to send on based on last_message_channel
        $channel = $this->lead->last_message_channel;

        if ($channel === Channel::Instagram || $channel === Channel::FacebookMessenger) {
            SendSocialMessage::dispatch($this->lead, $channel, $this->newMessage, auth()->id());
        } else {
            SendWhatsAppMessage::dispatch($this->lead, $this->newMessage, auth()->id());
        }

        $this->newMessage = '';

        $this->dispatch('message-sent');
    }

    public function sendImage(): void
    {
        $this->validate([
            'image' => 'required|image|max:10240',
            'imageCaption' => 'nullable|string|max:1024',
        ]);

        if ($this->lead->opt_out || $this->lead->do_not_contact) {
            $this->addError('image', __('inbox.errors.cannot_send_image_opted_out'));

            return;
        }

        $originalName = $this->image->getClientOriginalName();
        $path = $this->image->storeAs('whatsapp-images', $originalName, 'public');
        $imageUrl = url(Storage::url($path));

        $channel = $this->lead->last_message_channel;

        if ($channel === Channel::Instagram || $channel === Channel::FacebookMessenger) {
            SendSocialMessage::dispatch($this->lead, $channel, $this->imageCaption ?: '', auth()->id(), 'image', $imageUrl);
        } else {
            SendWhatsAppMessage::dispatch($this->lead, $this->imageCaption ?: '', auth()->id(), 'image', $imageUrl);
        }

        $this->image = null;
        $this->imageCaption = '';

        $this->dispatch('message-sent');
    }

    public function sendDocument(): void
    {
        $this->validate([
            'document' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,txt,csv,zip,rar',
            'documentCaption' => 'nullable|string|max:1024',
        ]);

        if ($this->lead->opt_out || $this->lead->do_not_contact) {
            $this->addError('document', __('inbox.errors.cannot_send_document_opted_out'));

            return;
        }

        $originalName = $this->document->getClientOriginalName();
        $path = $this->document->storeAs('whatsapp-documents', $originalName, 'public');
        $documentUrl = url(Storage::url($path));

        $channel = $this->lead->last_message_channel;

        if ($channel === Channel::Instagram || $channel === Channel::FacebookMessenger) {
            SendSocialMessage::dispatch($this->lead, $channel, $this->documentCaption ?: '', auth()->id(), 'document', $documentUrl);
        } else {
            SendWhatsAppMessage::dispatch($this->lead, $this->documentCaption ?: '', auth()->id(), 'document', $documentUrl);
        }

        $this->document = null;
        $this->documentCaption = '';

        $this->dispatch('message-sent');
    }

    public function addInternalNote(): void
    {
        $this->validate([
            'internalNote' => 'required|string|min:1',
        ]);

        WhatsAppMessage::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'type' => 'internal_note',
            'direction' => 'outgoing',
            'content' => $this->internalNote,
            'status' => 'sent',
            'sent_by_user_id' => auth()->id(),
        ]);

        LeadActivity::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'type' => LeadActivityType::Note,
            'description' => __('inbox.activities.internal_note_added'),
            'metadata' => ['note' => $this->internalNote],
            'user_id' => auth()->id(),
        ]);

        $this->internalNote = '';
    }

    public function assumeConversation(): void
    {
        $this->lead->update([
            'assigned_user_id' => auth()->id(),
        ]);

        LeadActivity::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'type' => LeadActivityType::Transfer,
            'description' => __('inbox.activities.conversation_assumed', ['name' => auth()->user()->name]),
            'user_id' => auth()->id(),
        ]);

        $this->lead->refresh();

        $this->dispatch('conversation-assumed');
    }

    public function openTransferModal(): void
    {
        $this->showTransferModal = true;
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->transferToUserId = null;
    }

    public function transferConversation(): void
    {
        $this->validate([
            'transferToUserId' => 'required|exists:users,id',
        ]);

        $newUser = User::findOrFail($this->transferToUserId);

        $this->lead->update([
            'assigned_user_id' => $this->transferToUserId,
        ]);

        LeadActivity::create([
            'tenant_id' => $this->lead->tenant_id,
            'lead_id' => $this->lead->id,
            'type' => LeadActivityType::Transfer,
            'description' => __('inbox.activities.conversation_transferred', ['name' => $newUser->name]),
            'user_id' => auth()->id(),
        ]);

        $this->lead->refresh();
        $this->closeTransferModal();

        $this->dispatch('conversation-transferred');
    }

    public function getOperatorsProperty()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', '!=', auth()->id())
            ->get();
    }

    public function render()
    {
        return view('livewire.inbox-conversation');
    }
}
