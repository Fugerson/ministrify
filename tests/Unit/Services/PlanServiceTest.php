<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlanServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlanService $service;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->church = Church::factory()->create(['plan' => 'free']);
        $this->service = new PlanService;
    }

    // =============
    // getUsage
    // =============

    public function test_get_usage_returns_correct_counts(): void
    {
        Person::factory()->forChurch($this->church)->count(5)->create();
        Ministry::factory()->forChurch($this->church)->count(2)->create();
        Group::factory()->create(['church_id' => $this->church->id]);

        $ministry = Ministry::factory()->forChurch($this->church)->create();
        Event::factory()->forMinistry($ministry)->count(3)->create([
            'date' => now()->format('Y-m-d'),
        ]);

        $usage = $this->service->getUsage($this->church);

        $this->assertEquals(5, $usage['people']);
        $this->assertEquals(3, $usage['ministries']);
        $this->assertEquals(1, $usage['groups']);
        $this->assertEquals(3, $usage['events_per_month']);
    }

    public function test_get_usage_is_cached(): void
    {
        Person::factory()->forChurch($this->church)->count(3)->create();

        $usage1 = $this->service->getUsage($this->church);
        $this->assertEquals(3, $usage1['people']);

        // Create more people — should still return cached value
        Person::factory()->forChurch($this->church)->count(2)->create();
        $usage2 = $this->service->getUsage($this->church);
        $this->assertEquals(3, $usage2['people']);

        // After cache clear — should return updated count
        PlanService::clearUsageCache($this->church->id);
        $usage3 = $this->service->getUsage($this->church);
        $this->assertEquals(5, $usage3['people']);
    }

    public function test_get_usage_counts_only_current_month_events(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        // Current month
        Event::factory()->forMinistry($ministry)->count(2)->create([
            'date' => now()->format('Y-m-d'),
        ]);

        // Last month
        Event::factory()->forMinistry($ministry)->create([
            'date' => now()->subMonth()->format('Y-m-d'),
        ]);

        $usage = $this->service->getUsage($this->church);
        $this->assertEquals(2, $usage['events_per_month']);
    }

    // =============
    // checkLimit
    // =============

    public function test_check_limit_allows_when_under_limit(): void
    {
        Person::factory()->forChurch($this->church)->count(10)->create();

        $result = $this->service->checkLimit($this->church, 'people');

        $this->assertTrue($result['allowed']);
        $this->assertEquals(10, $result['current']);
        $this->assertEquals(50, $result['limit']); // free plan: 50 people
        $this->assertFalse($result['unlimited']);
        $this->assertEquals(20, $result['percentage']);
    }

    public function test_check_limit_denies_when_at_limit(): void
    {
        Person::factory()->forChurch($this->church)->count(50)->create();

        $result = $this->service->checkLimit($this->church, 'people');

        $this->assertFalse($result['allowed']);
        $this->assertEquals(50, $result['current']);
        $this->assertEquals(100, $result['percentage']);
    }

    public function test_check_limit_denies_when_over_limit(): void
    {
        Person::factory()->forChurch($this->church)->count(55)->create();

        $result = $this->service->checkLimit($this->church, 'people');

        $this->assertFalse($result['allowed']);
        $this->assertEquals(100, $result['percentage']); // clamped to 100
    }

    public function test_check_limit_unlimited_for_pro_plan(): void
    {
        $this->church->update(['plan' => 'pro']);
        Cache::flush();

        Person::factory()->forChurch($this->church)->count(500)->create();

        $result = $this->service->checkLimit($this->church, 'people');

        $this->assertTrue($result['allowed']);
        $this->assertTrue($result['unlimited']);
        $this->assertEquals(0, $result['percentage']);
    }

    public function test_check_limit_unknown_resource_defaults_to_unlimited(): void
    {
        $result = $this->service->checkLimit($this->church, 'nonexistent_resource');

        $this->assertTrue($result['allowed']);
        $this->assertTrue($result['unlimited']);
    }

    // =============
    // canCreate
    // =============

    public function test_can_create_returns_true_when_under_limit(): void
    {
        Person::factory()->forChurch($this->church)->count(5)->create();

        $this->assertTrue($this->service->canCreate($this->church, 'people'));
    }

    public function test_can_create_returns_false_when_at_limit(): void
    {
        Person::factory()->forChurch($this->church)->count(50)->create();

        $this->assertFalse($this->service->canCreate($this->church, 'people'));
    }

    // =============
    // getWarnings
    // =============

    public function test_get_warnings_returns_resources_at_80_percent(): void
    {
        // Free plan: 50 people limit → 80% = 40
        Person::factory()->forChurch($this->church)->count(42)->create();

        $warnings = $this->service->getWarnings($this->church);

        $this->assertCount(1, $warnings);
        $this->assertEquals('people', $warnings[0]['resource']);
        $this->assertEquals(42, $warnings[0]['current']);
        $this->assertEquals(50, $warnings[0]['limit']);
        $this->assertEquals(84, $warnings[0]['percentage']);
        $this->assertFalse($warnings[0]['over']);
    }

    public function test_get_warnings_marks_over_limit(): void
    {
        Person::factory()->forChurch($this->church)->count(50)->create();

        $warnings = $this->service->getWarnings($this->church);

        $people = collect($warnings)->firstWhere('resource', 'people');
        $this->assertNotNull($people);
        $this->assertTrue($people['over']);
    }

    public function test_get_warnings_empty_when_below_80_percent(): void
    {
        Person::factory()->forChurch($this->church)->count(10)->create();

        $warnings = $this->service->getWarnings($this->church);

        $people = collect($warnings)->firstWhere('resource', 'people');
        $this->assertNull($people);
    }

    public function test_get_warnings_empty_for_unlimited_plan(): void
    {
        $this->church->update(['plan' => 'pro']);
        Cache::flush();

        Person::factory()->forChurch($this->church)->count(500)->create();

        $warnings = $this->service->getWarnings($this->church);

        $this->assertEmpty($warnings);
    }

    // =============
    // changePlan
    // =============

    public function test_change_plan_updates_church_and_clears_cache(): void
    {
        // Warm up cache
        $this->service->getUsage($this->church);
        $this->assertTrue(Cache::has("church:{$this->church->id}:plan_usage"));

        $this->service->changePlan($this->church, 'pro');

        $this->church->refresh();
        $this->assertEquals('pro', $this->church->plan);
        $this->assertNotNull($this->church->plan_changed_at);
        $this->assertFalse(Cache::has("church:{$this->church->id}:plan_usage"));
    }

    // =============
    // Static methods
    // =============

    public function test_get_all_plans_returns_config(): void
    {
        $plans = PlanService::getAllPlans();

        $this->assertArrayHasKey('free', $plans);
        $this->assertArrayHasKey('standard', $plans);
        $this->assertArrayHasKey('pro', $plans);
    }

    public function test_get_plan_returns_specific_plan(): void
    {
        $plan = PlanService::getPlan('free');

        $this->assertNotNull($plan);
        $this->assertEquals('Free', $plan['name']);
        $this->assertEquals(0, $plan['price']);
    }

    public function test_get_plan_returns_null_for_unknown(): void
    {
        $this->assertNull(PlanService::getPlan('enterprise'));
    }
}
