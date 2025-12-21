<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $user->church_id === $group->church_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'leader']);
    }

    public function update(User $user, Group $group): bool
    {
        if ($user->church_id !== $group->church_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Leader can update if they are the group leader
        if ($user->isLeader() && $user->person) {
            return $group->leader_id === $user->person->id;
        }

        return false;
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->church_id === $group->church_id && $user->isAdmin();
    }
}
