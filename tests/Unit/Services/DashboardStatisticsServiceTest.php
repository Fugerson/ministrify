<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Person;
use App\Services\DashboardStatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardStatisticsService $service;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardStatisticsService();
        $this->church = Church::factory()->create();
    }

    public function test_get_people_stats_returns_keys(): void
    {
        $stats = $this->service->getPeopleStats($this->church);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
    }

    public function test_get_ministry_stats_returns_data(): void
    {
        Ministry::factory()->forChurch($this->church)->create();

        $stats = $this->service->getMinistryStats($this->church);

        $this->assertIsArray($stats);
    }

    public function test_get_people_stats_with_people(): void
    {
        Person::factory()->forChurch($this->church)->count(5)->create();

        $stats = $this->service->getPeopleStats($this->church);

        $this->assertEquals(5, $stats['total']);
    }

    public function test_get_event_stats_returns_data(): void
    {
        $stats = $this->service->getEventStats($this->church);

        $this->assertIsArray($stats);
    }
}
