<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return $user->church_id === $event->church_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'leader']);
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->church_id !== $event->church_id) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Ministry leader can update events in their ministry
        if ($user->isLeader() && $user->person && $event->ministry) {
            return $event->ministry->leader_id === $user->person->id;
        }

        return false;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->church_id === $event->church_id && $user->isAdmin();
    }

    /**
     * Determine if user can manage service plan
     * Accessible by: admins, ministry leaders, and assigned volunteers
     */
    public function managePlan(User $user, Event $event): bool
    {
        // Must be same church
        if ($user->church_id !== $event->church_id) {
            return false;
        }

        // Admins always have access
        if ($user->isAdmin()) {
            return true;
        }

        // No person profile - no access
        if (!$user->person) {
            return false;
        }

        // Ministry leader has access
        if ($event->ministry && $event->ministry->leader_id === $user->person->id) {
            return true;
        }

        // Check if user is assigned to this event
        $isAssigned = $event->assignments()
            ->where('person_id', $user->person->id)
            ->exists();

        if ($isAssigned) {
            return true;
        }

        // Check if user is responsible for any plan item
        $isResponsible = $event->planItems()
            ->where('responsible_id', $user->person->id)
            ->exists();

        return $isResponsible;
    }
}
