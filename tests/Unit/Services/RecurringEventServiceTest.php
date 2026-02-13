<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Services\RecurringEventService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringEventServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecurringEventService $service;
    private Church $church;
    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecurringEventService();
        $this->church = Church::factory()->create();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    // ==================
    // calculateRecurringDates
    // ==================

    public function test_daily_recurrence(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'daily',
            'count',
            4,
            null
        );

        $this->assertCount(3, $dates); // endCount - 1 = 3
        $this->assertEquals('2025-06-02', $dates[0]->toDateString());
        $this->assertEquals('2025-06-03', $dates[1]->toDateString());
        $this->assertEquals('2025-06-04', $dates[2]->toDateString());
    }

    public function test_weekly_recurrence(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'weekly',
            'count',
            5,
            null
        );

        $this->assertCount(4, $dates);
        $this->assertEquals('2025-06-08', $dates[0]->toDateString());
        $this->assertEquals('2025-06-15', $dates[1]->toDateString());
    }

    public function test_biweekly_recurrence(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'biweekly',
            'count',
            3,
            null
        );

        $this->assertCount(2, $dates);
        $this->assertEquals('2025-06-15', $dates[0]->toDateString());
        $this->assertEquals('2025-06-29', $dates[1]->toDateString());
    }

    public function test_monthly_recurrence(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-15'),
            'monthly',
            'count',
            4,
            null
        );

        $this->assertCount(3, $dates);
        $this->assertEquals('2025-07-15', $dates[0]->toDateString());
        $this->assertEquals('2025-08-15', $dates[1]->toDateString());
        $this->assertEquals('2025-09-15', $dates[2]->toDateString());
    }

    public function test_yearly_recurrence(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'yearly',
            'count',
            3,
            null
        );

        $this->assertCount(2, $dates);
        $this->assertEquals('2026-06-01', $dates[0]->toDateString());
        $this->assertEquals('2027-06-01', $dates[1]->toDateString());
    }

    public function test_weekdays_recurrence(): void
    {
        // Start on Friday 2025-06-06
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-06'),
            'weekdays',
            'count',
            4,
            null
        );

        $this->assertCount(3, $dates);
        // Next weekday after Friday is Monday
        $this->assertEquals('2025-06-09', $dates[0]->toDateString()); // Monday
        $this->assertEquals('2025-06-10', $dates[1]->toDateString()); // Tuesday
    }

    public function test_custom_recurrence(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'custom:3:week',
            'count',
            3,
            null
        );

        $this->assertCount(2, $dates);
        $this->assertEquals('2025-06-22', $dates[0]->toDateString());
    }

    // ==================
    // End Conditions
    // ==================

    public function test_end_by_count(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'weekly',
            'count',
            3,
            null
        );

        $this->assertCount(2, $dates); // count - 1
    }

    public function test_end_by_date(): void
    {
        $dates = $this->service->calculateRecurringDates(
            Carbon::parse('2025-06-01'),
            'weekly',
            'date',
            999,
            '2025-06-22'
        );

        $this->assertCount(3, $dates);
        $this->assertTrue($dates[2]->lte(Carbon::parse('2025-06-22')));
    }

    // ==================
    // generateRecurringEvents
    // ==================

    public function test_generate_recurring_events(): void
    {
        $parent = Event::factory()->forMinistry($this->ministry)->create([
            'title' => 'Weekly Service',
            'date' => Carbon::parse('2025-06-01'),
            'time' => '10:00',
            'is_service' => false,
            'track_attendance' => false,
        ]);

        $events = $this->service->generateRecurringEvents($parent, 'weekly', 'count', 3);

        $this->assertCount(2, $events);
        $this->assertEquals('Weekly Service', $events[0]->title);
        $this->assertEquals($parent->id, $events[0]->parent_event_id);
    }

    // ==================
    // deleteFutureRecurringEvents
    // ==================

    public function test_delete_future_recurring_events(): void
    {
        $parent = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->subMonth(),
            'is_service' => false,
        ]);

        Event::factory()->forMinistry($this->ministry)->create([
            'parent_event_id' => $parent->id,
            'date' => now()->addWeek(),
            'is_service' => false,
        ]);
        Event::factory()->forMinistry($this->ministry)->create([
            'parent_event_id' => $parent->id,
            'date' => now()->addWeeks(2),
            'is_service' => false,
        ]);

        $deleted = $this->service->deleteFutureRecurringEvents($parent);

        $this->assertEquals(2, $deleted);
    }
}
