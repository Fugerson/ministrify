<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canView('events');
    }

    public function view(User $user, Event $event): bool
    {
        return $user->church_id === $event->church_id && $user->canView('events');
    }

    public function create(User $user): bool
    {
        return $user->canCreate('events');
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->church_id !== $event->church_id) {
            return false;
        }

        // User with edit permission can update any event
        if ($user->canEdit('events')) {
            return true;
        }

        // Ministry leader can update events in their ministry
        if ($user->person && $event->ministry && $event->ministry->leader_id === $user->person->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->church_id === $event->church_id && $user->canDelete('events');
    }

    /**
     * Determine if user can manage service plan
     * Accessible by: users with edit permission, ministry leaders, and assigned volunteers
     */
    public function managePlan(User $user, Event $event): bool
    {
        // Must be same church
        if ($user->church_id !== $event->church_id) {
            return false;
        }

        // Users with edit permission always have access
        if ($user->canEdit('events')) {
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
