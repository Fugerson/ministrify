<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Services\DashboardCacheService;

class AttendanceObserver
{
    public function __construct(
        private DashboardCacheService $cacheService
    ) {}

    public function created(Attendance $attendance): void
    {
        $this->clearCache($attendance);
    }

    public function updated(Attendance $attendance): void
    {
        $this->clearCache($attendance);
    }

    public function deleted(Attendance $attendance): void
    {
        $this->clearCache($attendance);
    }

    private function clearCache(Attendance $attendance): void
    {
        if ($attendance->church_id) {
            $this->cacheService->forgetGroupRelated($attendance->church_id);
            $this->cacheService->forget('attendance', $attendance->church_id);
            $this->cacheService->forget('stats', $attendance->church_id);
        }
    }
}
