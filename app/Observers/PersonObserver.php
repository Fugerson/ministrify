<?php

namespace App\Observers;

use App\Models\Person;
use App\Services\VisitorFollowupService;

class PersonObserver
{
    public function __construct(
        private VisitorFollowupService $followupService
    ) {}

    /**
     * Handle the Person "created" event.
     */
    public function created(Person $person): void
    {
        // Create follow-up tasks for new guests
        if ($person->membership_status === Person::STATUS_GUEST) {
            $this->followupService->createFollowupTasks($person);
        }
    }

    /**
     * Handle the Person "updated" event.
     */
    public function updated(Person $person): void
    {
        // Handle membership status changes
        if ($person->isDirty('membership_status')) {
            $oldStatus = $person->getOriginal('membership_status');
            $newStatus = $person->membership_status;

            // Could trigger notifications or other actions on status change
            // Example: notify when guest becomes member
        }
    }

    /**
     * Handle the Person "deleted" event.
     */
    public function deleted(Person $person): void
    {
        // Cleanup tasks when person is deleted
    }

    /**
     * Handle the Person "restored" event.
     */
    public function restored(Person $person): void
    {
        //
    }

    /**
     * Handle the Person "force deleted" event.
     */
    public function forceDeleted(Person $person): void
    {
        //
    }
}
