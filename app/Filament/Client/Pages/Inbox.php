<?php

namespace App\Filament\Client\Pages;

use App\Models\Lead;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Inbox extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'filament.client.pages.inbox';

    protected static ?int $navigationSort = 1;

    public ?int $selectedLeadId = null;

    public int $refreshKey = 0;

    public function mount(): void
    {
        // Select first conversation by default
        $firstLead = $this->getLeadsWithMessages()->first();
        if ($firstLead) {
            $this->selectedLeadId = $firstLead->id;
        }
    }

    public function selectConversation(int $leadId): void
    {
        $this->selectedLeadId = $leadId;

        // Mark all incoming messages as read
        \App\Models\WhatsAppMessage::where('lead_id', $leadId)
            ->where('direction', \App\Enums\MessageDirection::Incoming)
            ->where('status', '!=', \App\Enums\MessageStatus::Read)
            ->update(['status' => \App\Enums\MessageStatus::Read]);

        // Force refresh to update unread count
        $this->refreshKey++;
    }

    public function getLeadsWithMessages()
    {
        return Lead::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('whatsappMessages')
            ->withCount(['whatsappMessages as unread_count' => function ($query) {
                $query->where('direction', 'incoming')
                    ->where('status', '!=', 'read');
            }])
            ->with(['whatsappMessages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->sortByDesc(function ($lead) {
                return $lead->whatsappMessages->first()?->created_at;
            });
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

    public function refreshConversationList(): void
    {
        // Called by child component when a message is sent/received
        // This updates the conversation list sidebar
        $this->refreshKey++;
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
}
