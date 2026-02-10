<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canView('groups');
    }

    public function view(User $user, Group $group): bool
    {
        return $user->church_id === $group->church_id && $user->canView('groups');
    }

    public function create(User $user): bool
    {
        return $user->canCreate('groups');
    }

    public function update(User $user, Group $group): bool
    {
        if ($user->church_id !== $group->church_id) {
            return false;
        }

        // User with edit permission can update any group
        if ($user->canEdit('groups')) {
            return true;
        }

        // Group leader can update their own group
        if ($user->person && $group->leader_id === $user->person->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Group $group): bool
    {
        if ($user->church_id !== $group->church_id) {
            return false;
        }

        if ($user->canDelete('groups')) {
            return true;
        }

        // Group leader can delete their own group
        if ($user->person && $group->leader_id === $user->person->id) {
            return true;
        }

        return false;
    }
}
