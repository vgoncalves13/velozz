<?php

namespace App\Livewire;

use App\Enums\Channel;
use App\Enums\LeadActivityType;
use App\Jobs\SendSocialMessage;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SocialMessage;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Collection;
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

    public bool $showMergeModal = false;

    public ?int $mergeTargetLeadId = null;

    public ?string $preferredChannelOverride = null;

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

        $social = SocialMessage::where('lead_id', $this->leadId)
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

        $channel = $this->resolveChannel();

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

        $channel = $this->resolveChannel();

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

        $channel = $this->resolveChannel();

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

    public function openMergeModal(): void
    {
        $this->showMergeModal = true;
    }

    public function closeMergeModal(): void
    {
        $this->showMergeModal = false;
        $this->mergeTargetLeadId = null;
    }

    public function confirmMerge(): void
    {
        if ($this->mergeTargetLeadId === $this->leadId) {
            $this->addError('mergeTargetLeadId', __('inbox.errors.cannot_merge_same'));

            return;
        }

        $secondary = Lead::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($this->mergeTargetLeadId);

        $primary = $this->lead;

        WhatsAppMessage::where('lead_id', $secondary->id)->update(['lead_id' => $primary->id]);
        SocialMessage::where('lead_id', $secondary->id)->update(['lead_id' => $primary->id]);
        LeadActivity::where('lead_id', $secondary->id)->update(['lead_id' => $primary->id]);

        $primaryFields = $primary->custom_fields ?? [];
        $secondaryFields = $secondary->custom_fields ?? [];

        if (empty($primaryFields['facebook_psid']) && ! empty($secondaryFields['facebook_psid'])) {
            $primaryFields['facebook_psid'] = $secondaryFields['facebook_psid'];
        }

        if (empty($primaryFields['instagram_sender_id']) && ! empty($secondaryFields['instagram_sender_id'])) {
            $primaryFields['instagram_sender_id'] = $secondaryFields['instagram_sender_id'];
        }

        $primaryWhatsapps = $primary->whatsapps ?? [];
        $secondaryWhatsapps = $secondary->whatsapps ?? [];
        $mergedWhatsapps = array_values(array_unique(array_merge($primaryWhatsapps, $secondaryWhatsapps)));

        $primaryLastAt = $primary->last_message_at;
        $secondaryLastAt = $secondary->last_message_at;

        $latestAt = $primaryLastAt;
        $latestChannel = $primary->last_message_channel;

        if ($secondaryLastAt && (! $primaryLastAt || $secondaryLastAt->gt($primaryLastAt))) {
            $latestAt = $secondaryLastAt;
            $latestChannel = $secondary->last_message_channel;
        }

        $primary->update([
            'custom_fields' => $primaryFields,
            'whatsapps' => $mergedWhatsapps ?: null,
            'last_message_at' => $latestAt,
            'last_message_channel' => $latestChannel?->value,
        ]);

        LeadActivity::create([
            'tenant_id' => $primary->tenant_id,
            'lead_id' => $primary->id,
            'type' => LeadActivityType::Updated,
            'description' => __('inbox.activities.lead_merged', ['name' => $secondary->full_name]),
            'user_id' => auth()->id(),
        ]);

        $secondary->delete();

        $this->lead->refresh();
        $this->closeMergeModal();

        $this->dispatch('conversation-updated');
    }

    public function selectChannel(string $channelValue): void
    {
        $this->preferredChannelOverride = $channelValue;
    }

    public function getAvailableChannelsProperty(): array
    {
        $channels = [];

        if (! empty($this->lead->whatsapps)) {
            $channels[] = Channel::Whatsapp;
        }

        if ($this->lead->custom_fields['facebook_psid'] ?? null) {
            $channels[] = Channel::FacebookMessenger;
        }

        if ($this->lead->custom_fields['instagram_sender_id'] ?? null) {
            $channels[] = Channel::Instagram;
        }

        return $channels;
    }

    public function getOperatorsProperty(): Collection
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', '!=', auth()->id())
            ->get();
    }

    public function getLeadsForMergeProperty(): Collection
    {
        return Lead::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', '!=', $this->leadId)
            ->where(fn ($q) => $q->whereHas('whatsappMessages')->orWhereHas('socialMessages'))
            ->get(['id', 'full_name', 'source']);
    }

    public function getLeadViewUrlProperty(): string
    {
        try {
            return \App\Filament\Client\Resources\Leads\LeadResource::getUrl('view', ['record' => $this->lead->id]);
        } catch (\Exception) {
            return '#';
        }
    }

    private function resolveChannel(): ?Channel
    {
        if ($this->preferredChannelOverride !== null) {
            return Channel::from($this->preferredChannelOverride);
        }

        return $this->lead->last_message_channel;
    }

    public function render()
    {
        return view('livewire.inbox-conversation');
    }
}
