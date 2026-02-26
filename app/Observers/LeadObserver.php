<?php

namespace App\Observers;

use App\Enums\LeadActivityType;
use App\Models\Lead;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        \App\Models\LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'type' => LeadActivityType::Creation,
            'description' => 'Lead created',
            'metadata' => [
                'source' => $lead->source,
                'assigned_user_id' => $lead->assigned_user_id,
            ],
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        $changes = $lead->getDirty();

        // Ignore timestamp changes
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        // Track specific important changes
        foreach ($changes as $field => $newValue) {
            $oldValue = $lead->getOriginal($field);

            // Special handling for assigned user change
            if ($field === 'assigned_user_id' && $oldValue !== $newValue) {
                // Load the assigned user if needed
                if ($newValue && ! $lead->relationLoaded('assignedUser')) {
                    $lead->load('assignedUser');
                }

                \App\Models\LeadActivity::create([
                    'tenant_id' => $lead->tenant_id,
                    'lead_id' => $lead->id,
                    'type' => LeadActivityType::Assigned,
                    'description' => $newValue && $lead->assignedUser
                        ? "Lead assigned to {$lead->assignedUser->name}"
                        : 'Lead unassigned',
                    'metadata' => [
                        'old_user_id' => $oldValue,
                        'new_user_id' => $newValue,
                    ],
                    'user_id' => auth()->id(),
                ]);

                continue;
            }

            // Special handling for pipeline stage change
            if ($field === 'pipeline_stage_id' && $oldValue !== $newValue) {
                \App\Models\LeadActivity::create([
                    'tenant_id' => $lead->tenant_id,
                    'lead_id' => $lead->id,
                    'type' => LeadActivityType::StageChanged,
                    'description' => $newValue
                        ? "Lead moved to {$lead->pipelineStage->name}"
                        : 'Lead removed from pipeline',
                    'metadata' => [
                        'old_stage_id' => $oldValue,
                        'new_stage_id' => $newValue,
                    ],
                    'user_id' => auth()->id(),
                ]);

                continue;
            }

            // Generic field update
            \App\Models\LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $lead->id,
                'type' => LeadActivityType::FieldUpdated,
                'description' => "Field '{$field}' updated",
                'metadata' => [
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ],
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "restored" event.
     */
    public function restored(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "force deleted" event.
     */
    public function forceDeleted(Lead $lead): void
    {
        //
    }
}
