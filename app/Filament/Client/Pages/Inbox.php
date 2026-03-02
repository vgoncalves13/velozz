<?php

namespace App\Filament\Client\Pages;

use App\Models\Lead;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Inbox extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'filament.client.pages.inbox';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('pages.inbox.navigation');
    }

    public function getTitle(): string
    {
        return __('inbox.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.crm');
    }

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
        $leadsWithMessages = Lead::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('whatsappMessages')
            ->withCount(['whatsappMessages as unread_count' => function ($query) {
                $query->where('direction', 'incoming')
                    ->where('status', '!=', 'read');
            }])
            ->with(['whatsappMessages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get();

        // If a lead is selected but not in the list (no messages yet), add it
        if ($this->selectedLeadId) {
            $selectedLead = Lead::where('id', $this->selectedLeadId)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->withCount(['whatsappMessages as unread_count' => function ($query) {
                    $query->where('direction', 'incoming')
                        ->where('status', '!=', 'read');
                }])
                ->with(['whatsappMessages' => function ($query) {
                    $query->latest()->limit(1);
                }])
                ->first();

            if ($selectedLead && ! $leadsWithMessages->contains('id', $selectedLead->id)) {
                $leadsWithMessages->prepend($selectedLead);
            }
        }

        return $leadsWithMessages->sortByDesc(function ($lead) {
            return $lead->whatsappMessages->first()?->created_at ?? $lead->created_at;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newConversation')
                ->label(__('inbox.actions.new_conversation'))
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    Select::make('lead_id')
                        ->label(__('inbox.labels.select_lead'))
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search): array {
                            return Lead::where('tenant_id', auth()->user()->tenant_id)
                                ->where(function ($query) use ($search) {
                                    $query->where('full_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->orWhereJsonContains('phones', $search)
                                        ->orWhereJsonContains('whatsapps', $search);
                                })
                                ->limit(50)
                                ->pluck('full_name', 'id')
                                ->toArray();
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => Lead::find($value)?->full_name)
                        ->required()
                        ->helperText(__('inbox.labels.search_placeholder')),
                ])
                ->action(function (array $data): void {
                    $this->selectedLeadId = $data['lead_id'];
                    $this->refreshKey++;
                }),
        ];
    }
}
