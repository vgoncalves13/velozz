<?php

namespace App\Policies;

use App\Models\User;

class LeadPolicy
{
    /**
     * Determine whether the user can view any leads.
     * Operators can only see their assigned leads.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_lead');
    }

    /**
     * Determine whether the user can view the lead.
     * Operators can only view leads assigned to them.
     */
    public function view(User $user, $lead): bool
    {
        if (! $user->hasPermissionTo('view_lead')) {
            return false;
        }

        // Operators can only view their assigned leads
        if ($user->isOperator()) {
            return $lead->assigned_user_id === $user->id;
        }

        // Admin and supervisors can view all leads
        return true;
    }

    /**
     * Determine whether the user can create leads.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_lead');
    }

    /**
     * Determine whether the user can update the lead.
     * Operators can only edit their assigned leads.
     */
    public function update(User $user, $lead): bool
    {
        if (! $user->hasPermissionTo('edit_lead')) {
            return false;
        }

        // Operators can only edit their assigned leads
        if ($user->isOperator()) {
            return $lead->assigned_user_id === $user->id;
        }

        // Admin and supervisors can edit all leads
        return true;
    }

    /**
     * Determine whether the user can delete the lead.
     */
    public function delete(User $user, $lead): bool
    {
        return $user->hasPermissionTo('delete_lead');
    }

    /**
     * Determine whether the user can restore the lead.
     */
    public function restore(User $user, $lead): bool
    {
        return $user->hasPermissionTo('delete_lead');
    }

    /**
     * Determine whether the user can permanently delete the lead.
     */
    public function forceDelete(User $user, $lead): bool
    {
        return $user->hasPermissionTo('delete_lead');
    }
}
