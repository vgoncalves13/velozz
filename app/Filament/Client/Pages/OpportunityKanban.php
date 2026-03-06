<?php

namespace App\Filament\Client\Pages;

use App\Enums\ClientNavigationGroup;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class OpportunityKanban extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('opportunity_kanban.navigation');
    }

    public function getTitle(): string
    {
        return __('opportunity_kanban.title');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::Sales;

    protected string $view = 'filament.client.pages.opportunity-kanban';

    public Collection $stages;

    public array $records = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->stages = OpportunityStage::query()
            ->orderBy('order')
            ->get();

        $this->records = [];

        foreach ($this->stages as $stage) {
            $this->records[$stage->id] = Opportunity::query()
                ->where('opportunity_stage_id', $stage->id)
                ->with(['lead', 'product', 'assignedUser'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }
    }

    public function moveCard(int $opportunityId, int $newStageId): void
    {
        $opportunity = Opportunity::findOrFail($opportunityId);

        if ($opportunity->opportunity_stage_id === $newStageId) {
            return;
        }

        $newStage = OpportunityStage::findOrFail($newStageId);

        $opportunity->update(['opportunity_stage_id' => $newStageId]);

        $this->loadData();

        Notification::make()
            ->title(__('opportunity_kanban.notifications.moved_title'))
            ->body(__('opportunity_kanban.notifications.moved_body', ['stage' => $newStage->name]))
            ->success()
            ->send();
    }
}
