<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Can view attendance list
     */
    public function viewAny(User $user): bool
    {
        return $user->canView('attendance');
    }

    /**
     * Can view specific attendance record
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->church_id !== $attendance->church_id) {
            return false;
        }

        // Leaders can view attendance for their entities
        if ($user->isLeader() && $user->person) {
            // For groups
            if ($attendance->attendable_type === \App\Models\Group::class) {
                $group = $attendance->attendable;
                if ($group && $group->leader_id === $user->person->id) {
                    return true;
                }
            }

            // For ministry events
            if ($attendance->attendable_type === \App\Models\Event::class) {
                $event = $attendance->attendable;
                if ($event && $event->ministry && $event->ministry->leader_id === $user->person->id) {
                    return true;
                }
            }
        }

        return $user->canView('attendance');
    }

    /**
     * Can create attendance record
     */
    public function create(User $user): bool
    {
        return $user->canCreate('attendance');
    }

    /**
     * Can record attendance for a group
     */
    public function recordForGroup(User $user, \App\Models\Group $group): bool
    {
        if ($user->church_id !== $group->church_id) {
            return false;
        }

        // Group leader can record attendance
        if ($user->person && $group->leader_id === $user->person->id) {
            return true;
        }

        // Group assistants can record attendance
        if ($user->person && $group->assistants()->where('person_id', $user->person->id)->exists()) {
            return true;
        }

        return $user->canCreate('attendance');
    }

    /**
     * Can record attendance for an event
     */
    public function recordForEvent(User $user, \App\Models\Event $event): bool
    {
        if ($user->church_id !== $event->church_id) {
            return false;
        }

        // Ministry leader can record attendance
        if ($user->person && $event->ministry && $event->ministry->leader_id === $user->person->id) {
            return true;
        }

        return $user->canCreate('attendance');
    }

    /**
     * Can update attendance record
     */
    public function update(User $user, Attendance $attendance): bool
    {
        if ($user->church_id !== $attendance->church_id) {
            return false;
        }

        // Original recorder can update
        if ($attendance->recorded_by === $user->id) {
            return true;
        }

        // Leaders can update their entities' attendance
        if ($user->isLeader() && $user->person) {
            if ($attendance->attendable_type === \App\Models\Group::class) {
                $group = $attendance->attendable;
                if ($group && $group->leader_id === $user->person->id) {
                    return true;
                }
            }

            if ($attendance->attendable_type === \App\Models\Event::class) {
                $event = $attendance->attendable;
                if ($event && $event->ministry && $event->ministry->leader_id === $user->person->id) {
                    return true;
                }
            }
        }

        return $user->canUpdate('attendance');
    }

    /**
     * Can delete attendance record
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        if ($user->church_id !== $attendance->church_id) {
            return false;
        }

        return $user->canDelete('attendance');
    }

    /**
     * Can view attendance statistics
     */
    public function viewStats(User $user): bool
    {
        return $user->canView('attendance');
    }
}
