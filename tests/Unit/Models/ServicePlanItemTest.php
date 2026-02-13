<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\ServicePlanItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePlanItemTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $church = Church::factory()->create();
        $ministry = Ministry::factory()->forChurch($church)->create();
        $this->event = Event::factory()->forMinistry($ministry)->create(['date' => now()]);
    }

    // ==================
    // Type Labels/Icons/Colors
    // ==================

    public function test_type_label_for_worship(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->worship()->create();
        $this->assertEquals('Прославлення', $item->type_label);
    }

    public function test_type_label_for_sermon(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->sermon()->create();
        $this->assertEquals('Проповідь', $item->type_label);
    }

    public function test_type_label_null_when_no_type(): void
    {
        // type column is NOT NULL in SQLite, so test the accessor directly
        $item = new ServicePlanItem(['type' => null]);
        $this->assertNull($item->type_label);
    }

    public function test_type_icon(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->worship()->create();
        $this->assertEquals('music', $item->type_icon);
    }

    public function test_type_color(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->worship()->create();
        $this->assertEquals('#8b5cf6', $item->type_color);
    }

    // ==================
    // Status Labels
    // ==================

    public function test_status_label(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'status' => ServicePlanItem::STATUS_PLANNED,
        ]);
        $this->assertEquals('Заплановано', $item->status_label);
    }

    public function test_status_label_confirmed(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'status' => ServicePlanItem::STATUS_CONFIRMED,
        ]);
        $this->assertEquals('Підтверджено', $item->status_label);
    }

    // ==================
    // Duration
    // ==================

    public function test_duration_minutes_from_times(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)
            ->withTimes('10:00', '10:45')->create();

        $this->assertEquals(45, $item->duration_minutes);
    }

    public function test_duration_minutes_null_without_times(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'start_time' => null,
            'end_time' => null,
        ]);

        $this->assertNull($item->duration_minutes);
    }

    public function test_default_duration_for_type(): void
    {
        $this->assertEquals(30, ServicePlanItem::getDefaultDuration(ServicePlanItem::TYPE_WORSHIP));
        $this->assertEquals(40, ServicePlanItem::getDefaultDuration(ServicePlanItem::TYPE_SERMON));
        $this->assertEquals(10, ServicePlanItem::getDefaultDuration('unknown'));
    }

    // ==================
    // Formatted Time
    // ==================

    public function test_formatted_time_range(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)
            ->withTimes('10:00', '10:30')->create();

        $this->assertEquals('10:00 - 10:30', $item->formatted_time_range);
    }

    public function test_formatted_time_range_start_only(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'start_time' => '10:00',
            'end_time' => null,
        ]);

        $this->assertEquals('10:00', $item->formatted_time_range);
    }

    public function test_formatted_time_range_null(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'start_time' => null,
            'end_time' => null,
        ]);

        $this->assertNull($item->formatted_time_range);
    }

    public function test_formatted_duration_minutes(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)
            ->withTimes('10:00', '10:45')->create();

        $this->assertEquals('45 хв', $item->formatted_duration);
    }

    public function test_formatted_duration_hours(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)
            ->withTimes('10:00', '11:30')->create();

        $this->assertEquals('1 год 30 хв', $item->formatted_duration);
    }

    // ==================
    // Person Status / Confirmation
    // ==================

    public function test_get_person_status(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'responsible_statuses' => [1 => 'confirmed', 2 => 'pending'],
        ]);

        $this->assertEquals('confirmed', $item->getPersonStatus(1));
        $this->assertEquals('pending', $item->getPersonStatus(2));
        $this->assertNull($item->getPersonStatus(999));
    }

    public function test_set_person_status(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'responsible_statuses' => [],
        ]);

        $item->setPersonStatus(5, 'confirmed');
        $item->refresh();

        $this->assertEquals('confirmed', $item->getPersonStatus(5));
    }

    public function test_get_confirmation_stats(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'responsible_statuses' => [
                1 => 'confirmed',
                2 => 'confirmed',
                3 => 'declined',
                4 => 'pending',
            ],
        ]);

        $stats = $item->getConfirmationStats();

        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['confirmed']);
        $this->assertEquals(1, $stats['declined']);
        $this->assertEquals(1, $stats['pending']);
    }

    public function test_get_confirmation_stats_empty(): void
    {
        $item = ServicePlanItem::factory()->forEvent($this->event)->create([
            'responsible_statuses' => null,
        ]);

        $stats = $item->getConfirmationStats();

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['confirmed']);
    }
}
