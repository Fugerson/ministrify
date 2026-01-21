<?php

namespace App\Policies;

use App\Models\Ministry;
use App\Models\User;

class MinistryPolicy
{
    /**
     * Anyone in the church can view ministry list
     */
    public function viewAny(User $user): bool
    {
        return $user->canView('ministries');
    }

    /**
     * Can view specific ministry
     */
    public function view(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        // Check visibility settings
        if (!$ministry->canAccess($user)) {
            return false;
        }

        return $user->canView('ministries');
    }

    /**
     * Only admins can create ministries
     */
    public function create(User $user): bool
    {
        return $user->canCreate('ministries');
    }

    /**
     * Admins and ministry leaders can update
     */
    public function update(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        return $user->canManageMinistry($ministry);
    }

    /**
     * Only admins can delete ministries
     */
    public function delete(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        return $user->canDelete('ministries');
    }

    /**
     * Can manage ministry members and positions
     */
    public function manage(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        return $user->canManageMinistry($ministry);
    }

    /**
     * Can manage ministry schedule/events
     */
    public function manageSchedule(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        return $user->canManageMinistry($ministry);
    }

    /**
     * Can view ministry finances (budget, expenses)
     */
    public function viewFinances(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        // Admins and ministry leaders
        return $user->canManageMinistry($ministry);
    }

    /**
     * Can add expenses for ministry
     */
    public function addExpense(User $user, Ministry $ministry): bool
    {
        if ($user->church_id !== $ministry->church_id) {
            return false;
        }

        return $user->canManageMinistry($ministry) || $user->canCreate('expenses');
    }
}
