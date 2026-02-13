<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Services\DashboardCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardCacheService $service;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardCacheService();
        $this->church = Church::factory()->create();
    }

    public function test_get_cache_key_format(): void
    {
        $key = $this->service->getCacheKey('stats', $this->church->id);
        $this->assertEquals("dashboard_stats_{$this->church->id}", $key);
    }

    public function test_remember_caches_result(): void
    {
        $calls = 0;
        $callback = function () use (&$calls) {
            $calls++;
            return 'computed_value';
        };

        $result1 = $this->service->remember('stats', $this->church, $callback);
        $result2 = $this->service->remember('stats', $this->church, $callback);

        $this->assertEquals('computed_value', $result1);
        $this->assertEquals('computed_value', $result2);
        $this->assertEquals(1, $calls); // Should only compute once
    }

    public function test_forget_clears_cache(): void
    {
        Cache::put("dashboard_stats_{$this->church->id}", 'cached_value', 3600);

        $this->service->forget('stats', $this->church->id);

        $this->assertNull(Cache::get("dashboard_stats_{$this->church->id}"));
    }

    public function test_forget_people_related(): void
    {
        $types = ['stats', 'demographics', 'new_members', 'funnel', 'need_attention', 'family', 'shepherd'];
        foreach ($types as $type) {
            Cache::put("dashboard_{$type}_{$this->church->id}", 'value', 3600);
        }
        Cache::put("dashboard_events_{$this->church->id}", 'events_value', 3600);

        $this->service->forgetPeopleRelated($this->church->id);

        foreach ($types as $type) {
            $this->assertNull(Cache::get("dashboard_{$type}_{$this->church->id}"), "Cache for {$type} should be cleared");
        }
        // events should NOT be cleared
        $this->assertEquals('events_value', Cache::get("dashboard_events_{$this->church->id}"));
    }

    public function test_forget_financial_related(): void
    {
        $types = ['stats', 'financial', 'budgets', 'giving_trends', 'online_donations'];
        foreach ($types as $type) {
            Cache::put("dashboard_{$type}_{$this->church->id}", 'value', 3600);
        }

        $this->service->forgetFinancialRelated($this->church->id);

        foreach ($types as $type) {
            $this->assertNull(Cache::get("dashboard_{$type}_{$this->church->id}"));
        }
    }

    public function test_forget_event_related(): void
    {
        $types = ['stats', 'events', 'calendar', 'registrations', 'volunteer'];
        foreach ($types as $type) {
            Cache::put("dashboard_{$type}_{$this->church->id}", 'value', 3600);
        }

        $this->service->forgetEventRelated($this->church->id);

        foreach ($types as $type) {
            $this->assertNull(Cache::get("dashboard_{$type}_{$this->church->id}"));
        }
    }

    public function test_forget_group_related(): void
    {
        $types = ['stats', 'group_health', 'group_compare', 'attendance'];
        foreach ($types as $type) {
            Cache::put("dashboard_{$type}_{$this->church->id}", 'value', 3600);
        }

        $this->service->forgetGroupRelated($this->church->id);

        foreach ($types as $type) {
            $this->assertNull(Cache::get("dashboard_{$type}_{$this->church->id}"));
        }
    }

    public function test_forget_ministry_related(): void
    {
        $types = ['stats', 'budgets', 'goals'];
        foreach ($types as $type) {
            Cache::put("dashboard_{$type}_{$this->church->id}", 'value', 3600);
        }

        $this->service->forgetMinistryRelated($this->church->id);

        foreach ($types as $type) {
            $this->assertNull(Cache::get("dashboard_{$type}_{$this->church->id}"));
        }
    }

    public function test_forget_all(): void
    {
        Cache::put("dashboard_stats_{$this->church->id}", 'v1', 3600);
        Cache::put("dashboard_events_{$this->church->id}", 'v2', 3600);

        $this->service->forgetAll($this->church->id);

        $this->assertNull(Cache::get("dashboard_stats_{$this->church->id}"));
        $this->assertNull(Cache::get("dashboard_events_{$this->church->id}"));
    }

    public function test_get_all_cache_keys(): void
    {
        $keys = $this->service->getAllCacheKeys($this->church->id);

        $this->assertIsArray($keys);
        $this->assertArrayHasKey('stats', $keys);
        $this->assertArrayHasKey('events', $keys);
    }
}
