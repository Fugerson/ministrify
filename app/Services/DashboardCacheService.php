<?php

namespace App\Services;

use App\Models\Church;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    /**
     * Cache TTLs in seconds for different widget types
     */
    private const CACHE_TTLS = [
        'stats' => 1800,           // 30 min - main stats
        'birthdays' => 86400,      // 24 hours - birthdays rarely change
        'events' => 3600,          // 1 hour - events change frequently
        'attendance' => 21600,     // 6 hours - attendance data
        'need_attention' => 43200, // 12 hours - people needing attention
        'budgets' => 7200,         // 2 hours - ministry budgets
        'tasks' => 3600,           // 1 hour - urgent tasks
        'financial' => 21600,      // 6 hours - financial data
        'growth' => 21600,         // 6 hours - growth data
        'prayer' => 7200,          // 2 hours - prayer requests
        'announcements' => 3600,   // 1 hour - announcements
        'campaigns' => 7200,       // 2 hours - donation campaigns
        'goals' => 14400,          // 4 hours - ministry goals
        'sermons' => 7200,         // 2 hours - recent sermons
        'demographics' => 43200,   // 12 hours - demographics rarely change
        'new_members' => 7200,     // 2 hours - new members
        'group_health' => 7200,    // 2 hours - group health
        'giving_trends' => 21600,  // 6 hours - giving trends
        'shepherd' => 14400,       // 4 hours - shepherd overview
        'registrations' => 3600,   // 1 hour - event registrations
        'volunteer' => 3600,       // 1 hour - volunteer schedule
        'activity' => 1800,        // 30 min - recent activity
        'funnel' => 43200,         // 12 hours - membership funnel
        'songs' => 14400,          // 4 hours - popular songs
        'family' => 43200,         // 12 hours - family stats
        'calendar' => 7200,        // 2 hours - calendar events
        'online_donations' => 7200,// 2 hours - online donations
        'group_compare' => 7200,   // 2 hours - group attendance compare
    ];

    /**
     * Get cached data or compute it
     */
    public function remember(string $type, Church $church, callable $callback)
    {
        $ttl = self::CACHE_TTLS[$type] ?? 3600;
        $key = $this->getCacheKey($type, $church->id);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get cache key for a specific type and church
     */
    public function getCacheKey(string $type, int $churchId): string
    {
        return "dashboard_{$type}_{$churchId}";
    }

    /**
     * Invalidate specific cache type for a church
     */
    public function forget(string $type, int $churchId): void
    {
        Cache::forget($this->getCacheKey($type, $churchId));
    }

    /**
     * Invalidate all dashboard caches for a church
     */
    public function forgetAll(int $churchId): void
    {
        foreach (array_keys(self::CACHE_TTLS) as $type) {
            $this->forget($type, $churchId);
        }
    }

    /**
     * Invalidate caches related to people changes
     */
    public function forgetPeopleRelated(int $churchId): void
    {
        $types = ['stats', 'demographics', 'new_members', 'funnel', 'need_attention', 'family', 'shepherd'];
        foreach ($types as $type) {
            $this->forget($type, $churchId);
        }
    }

    /**
     * Invalidate caches related to financial changes
     */
    public function forgetFinancialRelated(int $churchId): void
    {
        $types = ['stats', 'financial', 'budgets', 'giving_trends', 'online_donations'];
        foreach ($types as $type) {
            $this->forget($type, $churchId);
        }
    }

    /**
     * Invalidate caches related to event changes
     */
    public function forgetEventRelated(int $churchId): void
    {
        $types = ['stats', 'events', 'calendar', 'registrations', 'volunteer'];
        foreach ($types as $type) {
            $this->forget($type, $churchId);
        }
    }

    /**
     * Invalidate caches related to group changes
     */
    public function forgetGroupRelated(int $churchId): void
    {
        $types = ['stats', 'group_health', 'group_compare', 'attendance'];
        foreach ($types as $type) {
            $this->forget($type, $churchId);
        }
    }

    /**
     * Invalidate caches related to ministry changes
     */
    public function forgetMinistryRelated(int $churchId): void
    {
        $types = ['stats', 'budgets', 'goals'];
        foreach ($types as $type) {
            $this->forget($type, $churchId);
        }
    }

    /**
     * Get all cache keys for debugging
     */
    public function getAllCacheKeys(int $churchId): array
    {
        $keys = [];
        foreach (array_keys(self::CACHE_TTLS) as $type) {
            $keys[$type] = $this->getCacheKey($type, $churchId);
        }
        return $keys;
    }
}
