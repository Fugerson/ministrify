<?php

namespace App\Observers;

use App\Models\Group;
use App\Services\DashboardCacheService;

class GroupObserver
{
    public function __construct(
        private DashboardCacheService $cacheService
    ) {}

    public function created(Group $group): void
    {
        $this->clearCache($group);
    }

    public function updated(Group $group): void
    {
        $this->clearCache($group);
    }

    public function deleted(Group $group): void
    {
        $this->clearCache($group);
    }

    public function restored(Group $group): void
    {
        $this->clearCache($group);
    }

    private function clearCache(Group $group): void
    {
        if ($group->church_id) {
            $this->cacheService->forgetGroupRelated($group->church_id);
        }
    }
}
