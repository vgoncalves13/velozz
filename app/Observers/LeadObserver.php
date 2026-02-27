<?php

namespace App\Observers;

use App\Enums\LeadActivityType;
use App\Helpers\AuditHelper;
use App\Helpers\WebhookHelper;
use App\Models\Lead;
use App\Models\User;

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

        // Dispatch webhook for lead creation
        WebhookHelper::dispatch('lead_created', [
            'lead_id' => $lead->id,
            'full_name' => $lead->full_name,
            'email' => $lead->email,
            'phones' => $lead->phones,
            'whatsapps' => $lead->whatsapps,
            'source' => $lead->source,
            'assigned_user_id' => $lead->assigned_user_id,
            'created_at' => $lead->created_at->toIso8601String(),
        ], $lead->tenant_id);
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

                $oldUser = $oldValue ? User::find($oldValue) : null;
                $newUser = $newValue ? $lead->assignedUser : null;

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

                // Log lead transfer for audit
                AuditHelper::log(
                    'lead_transfer',
                    'lead',
                    $lead->id,
                    $oldUser ? ['assigned_user_id' => $oldValue, 'user_name' => $oldUser->name] : null,
                    $newUser ? ['assigned_user_id' => $newValue, 'user_name' => $newUser->name] : null
                );

                // Dispatch webhook for lead transfer
                WebhookHelper::dispatch('lead_transferred', [
                    'lead_id' => $lead->id,
                    'full_name' => $lead->full_name,
                    'old_user' => $oldUser ? ['id' => $oldUser->id, 'name' => $oldUser->name] : null,
                    'new_user' => $newUser ? ['id' => $newUser->id, 'name' => $newUser->name] : null,
                ], $lead->tenant_id);

                continue;
            }

            // Special handling for pipeline stage change
            if ($field === 'pipeline_stage_id' && $oldValue !== $newValue) {
                // Load stage if needed
                if ($newValue && ! $lead->relationLoaded('pipelineStage')) {
                    $lead->load('pipelineStage');
                }

                $stageName = $newValue && $lead->pipelineStage ? $lead->pipelineStage->name : null;

                \App\Models\LeadActivity::create([
                    'tenant_id' => $lead->tenant_id,
                    'lead_id' => $lead->id,
                    'type' => LeadActivityType::StageChanged,
                    'description' => $newValue
                        ? "Lead moved to {$stageName}"
                        : 'Lead removed from pipeline',
                    'metadata' => [
                        'old_stage_id' => $oldValue,
                        'new_stage_id' => $newValue,
                    ],
                    'user_id' => auth()->id(),
                ]);

                // Dispatch webhook for stage change
                WebhookHelper::dispatch('stage_changed', [
                    'lead_id' => $lead->id,
                    'full_name' => $lead->full_name,
                    'old_stage_id' => $oldValue,
                    'new_stage_id' => $newValue,
                    'stage_name' => $stageName,
                ], $lead->tenant_id);

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
