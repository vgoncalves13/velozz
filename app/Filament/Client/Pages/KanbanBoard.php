<?php

namespace App\Filament\Client\Pages;

use App\Enums\ClientNavigationGroup;
use App\Enums\LeadActivityType;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\PipelineStage;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class KanbanBoard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('kanban.navigation');
    }

    public function getTitle(): string
    {
        return __('kanban.title');
    }

    protected static string|\UnitEnum|null $navigationGroup = ClientNavigationGroup::Crm;

    protected string $view = 'filament.client.pages.kanban-board';

    public Collection $stages;

    public array $records = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        // TenantScope automatically filters by tenant_id
        $this->stages = PipelineStage::query()
            ->orderBy('order')
            ->get();

        $this->records = [];

        foreach ($this->stages as $stage) {
            $this->records[$stage->id] = Lead::query()
                ->where('pipeline_stage_id', $stage->id)
                ->with(['assignedUser'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }
    }

    public function moveCard(int $leadId, int $newStageId): void
    {
        // TenantScope automatically ensures tenant isolation
        $lead = Lead::findOrFail($leadId);
        $oldStageId = $lead->pipeline_stage_id;

        if ($oldStageId === $newStageId) {
            return;
        }

        $newStage = PipelineStage::findOrFail($newStageId);
        $oldStage = PipelineStage::find($oldStageId);

        // Update lead
        $lead->update(['pipeline_stage_id' => $newStageId]);

        // Register activity
        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::StageChanged,
            'description' => __('kanban.activities.stage_changed', [
                'old_stage' => $oldStage?->name,
                'new_stage' => $newStage->name,
            ]),
            'metadata' => [
                'old_stage_id' => $oldStageId,
                'old_stage_name' => $oldStage?->name,
                'new_stage_id' => $newStageId,
                'new_stage_name' => $newStage->name,
            ],
            'user_id' => auth()->id(),
        ]);

        // TODO: Dispatch automations (Phase 4 completion)
        // dispatch(new ProcessStageAutomation($lead, $newStage, 'entrada'));
        // dispatch(new ProcessStageAutomation($lead, $oldStage, 'saida'));

        $this->loadData();

        Notification::make()
            ->title(__('kanban.notifications.moved_title'))
            ->body(__('kanban.notifications.moved_body', ['stage' => $newStage->name]))
            ->success()
            ->send();
    }
}
