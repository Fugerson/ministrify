<?php

namespace App\Observers;

use App\Models\Person;
use App\Services\DashboardCacheService;
use App\Services\VisitorFollowupService;

class PersonObserver
{
    public function __construct(
        private VisitorFollowupService $followupService,
        private DashboardCacheService $cacheService
    ) {}

    /**
     * Handle the Person "created" event.
     */
    public function created(Person $person): void
    {
        $this->clearDashboardCache($person);
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

        $this->clearDashboardCache($person);
    }

    /**
     * Handle the Person "deleted" event.
     */
    public function deleted(Person $person): void
    {
        $this->clearDashboardCache($person);
    }

    /**
     * Handle the Person "restored" event.
     */
    public function restored(Person $person): void
    {
        $this->clearDashboardCache($person);
    }

    /**
     * Handle the Person "force deleted" event.
     */
    public function forceDeleted(Person $person): void
    {
        $this->clearDashboardCache($person);
    }

    private function clearDashboardCache(Person $person): void
    {
        if ($person->church_id) {
            $this->cacheService->forgetPeopleRelated($person->church_id);
        }
    }
}
