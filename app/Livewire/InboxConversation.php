<?php

namespace App\Livewire;

use App\Enums\LeadActivityType;
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

    public string $internalNote = '';

    public bool $isInternalNoteMode = false;

    public bool $showTransferModal = false;

    public ?int $transferToUserId = null;

    public int $refreshKey = 0;

    private ?int $previousIncomingCount = null;

    public function mount(int $leadId): void
    {
        $this->leadId = $leadId;
        $this->lead = Lead::with('assignedUser', 'whatsappMessages')->findOrFail($leadId);

        // Initialize incoming count on mount
        $this->previousIncomingCount = $this->getIncomingMessageCount();
    }

    public function getMessagesProperty()
    {
        return WhatsAppMessage::where('lead_id', $this->leadId)
            ->orderBy('created_at')
            ->get();
    }

    private function getIncomingMessageCount(): int
    {
        return WhatsAppMessage::where('lead_id', $this->leadId)
            ->where('direction', 'incoming')
            ->count();
    }

    public function hydrate(): void
    {
        // Check for new incoming messages after every render
        $currentIncomingCount = $this->getIncomingMessageCount();

        if ($this->previousIncomingCount !== null && $currentIncomingCount > $this->previousIncomingCount) {
            // New incoming message detected - dispatch browser event
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
        ];
    }

    public function onMessageReceived($event): void
    {
        // Update the conversation list
        $this->refreshKey++;
    }

    public function onMessageSent($event): void
    {
        // Update the conversation list
        $this->refreshKey++;
    }

    public function refreshConversationList(): void
    {
        // Called by child component when a message is sent/received
        // This updates the conversation list sidebar
        $this->refreshKey++;
    }

    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => 'required|string|min:1',
        ]);

        if ($this->lead->opt_out || $this->lead->do_not_contact) {
            $this->addError('newMessage', 'Cannot send message. Lead has opted out or is marked as do not contact.');

            return;
        }

        SendWhatsAppMessage::dispatch($this->lead, $this->newMessage, auth()->id());

        $this->newMessage = '';

        $this->dispatch('message-sent');
    }

    public function sendImage(): void
    {
        $this->validate([
            'image' => 'required|image|max:10240', // Max 10MB
            'imageCaption' => 'nullable|string|max:1024',
        ]);

        if ($this->lead->opt_out || $this->lead->do_not_contact) {
            $this->addError('image', 'Cannot send image. Lead has opted out or is marked as do not contact.');

            return;
        }

        // Store image in storage/app/public/whatsapp-images
        $path = $this->image->store('whatsapp-images', 'public');

        // Generate full public URL (Z-API accepts both URL and base64)
        $imageUrl = url(Storage::url($path));

        SendWhatsAppMessage::dispatch(
            $this->lead,
            $this->imageCaption ?: '',
            auth()->id(),
            'image',
            $imageUrl
        );

        $this->image = null;
        $this->imageCaption = '';

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
            'description' => 'Internal note added',
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
            'description' => 'Conversation assumed by '.auth()->user()->name,
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
            'description' => "Conversation transferred to {$newUser->name}",
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
