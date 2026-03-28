<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Services\DashboardCacheService;

class AssignmentObserver
{
    public function __construct(
        private DashboardCacheService $cacheService
    ) {}

    public function created(Assignment $assignment): void
    {
        $this->clearCache($assignment);
    }

    public function updated(Assignment $assignment): void
    {
        $this->clearCache($assignment);
    }

    public function deleted(Assignment $assignment): void
    {
        $this->clearCache($assignment);
    }

    private function clearCache(Assignment $assignment): void
    {
        $churchId = $assignment->event?->church_id;
        if ($churchId) {
            $this->cacheService->forget('volunteer', $churchId);
            $this->cacheService->forget('stats', $churchId);
        }
    }
}
