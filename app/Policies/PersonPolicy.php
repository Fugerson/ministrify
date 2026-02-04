<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

class PersonPolicy
{
    /**
     * Can view people list
     */
    public function viewAny(User $user): bool
    {
        return $user->canView('people');
    }

    /**
     * Can view specific person
     */
    public function view(User $user, Person $person): bool
    {
        if ($user->church_id !== $person->church_id) {
            return false;
        }

        // Can always view own profile
        if ($user->person && $user->person->id === $person->id) {
            return true;
        }

        // Leaders can view people in their ministries
        if ($user->isLeader() && $user->person) {
            $leaderMinistryIds = $user->person->leadingMinistries()->pluck('id');
            if ($person->ministries()->whereIn('ministry_id', $leaderMinistryIds)->exists()) {
                return true;
            }
        }

        return $user->canView('people');
    }

    /**
     * Can create new person
     */
    public function create(User $user): bool
    {
        return $user->canCreate('people');
    }

    /**
     * Can update person
     */
    public function update(User $user, Person $person): bool
    {
        if ($user->church_id !== $person->church_id) {
            return false;
        }

        // Can update own profile
        if ($user->person && $user->person->id === $person->id) {
            return true;
        }

        // Leaders can update people in their ministries
        if ($user->isLeader() && $user->person) {
            $leaderMinistryIds = $user->person->leadingMinistries()->pluck('id');
            if ($person->ministries()->whereIn('ministry_id', $leaderMinistryIds)->exists()) {
                return true;
            }
        }

        return $user->canEdit('people');
    }

    /**
     * Can delete person
     */
    public function delete(User $user, Person $person): bool
    {
        if ($user->church_id !== $person->church_id) {
            return false;
        }

        // Cannot delete self
        if ($user->person && $user->person->id === $person->id) {
            return false;
        }

        return $user->canDelete('people');
    }

    /**
     * Can manage own profile (for volunteers)
     */
    public function manageOwn(User $user, Person $person): bool
    {
        return $user->person && $user->person->id === $person->id;
    }

    /**
     * Can view person's financial history
     */
    public function viewFinances(User $user, Person $person): bool
    {
        if ($user->church_id !== $person->church_id) {
            return false;
        }

        // Can view own finances
        if ($user->person && $user->person->id === $person->id) {
            return true;
        }

        return $user->canView('finances');
    }

    /**
     * Can manage person's ministry assignments
     */
    public function manageAssignments(User $user, Person $person): bool
    {
        if ($user->church_id !== $person->church_id) {
            return false;
        }

        // Leaders can manage assignments for people in their ministries
        if ($user->isLeader() && $user->person) {
            $leaderMinistryIds = $user->person->leadingMinistries()->pluck('id');
            if ($person->ministries()->whereIn('ministry_id', $leaderMinistryIds)->exists()) {
                return true;
            }
        }

        return $user->canEdit('people');
    }

    /**
     * Can send messages to person
     */
    public function sendMessage(User $user, Person $person): bool
    {
        if ($user->church_id !== $person->church_id) {
            return false;
        }

        return $user->canView('people');
    }
}
