<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // Polymorphism
    // ==================

    public function test_attendable_for_event(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $event = Event::factory()->forMinistry($ministry)->create(['date' => now()]);

        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => Event::class,
            'attendable_id' => $event->id,
            'type' => Attendance::TYPE_EVENT,
        ]);

        $this->assertInstanceOf(Event::class, $attendance->attendable);
        $this->assertEquals($event->id, $attendance->attendable->id);
    }

    public function test_attendable_for_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => Group::class,
            'attendable_id' => $group->id,
            'type' => Attendance::TYPE_GROUP,
        ]);

        $this->assertInstanceOf(Group::class, $attendance->attendable);
    }

    // ==================
    // Accessors
    // ==================

    public function test_present_count_from_records(): void
    {
        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'total_count' => 0,
        ]);
        $person1 = Person::factory()->forChurch($this->church)->create();
        $person2 = Person::factory()->forChurch($this->church)->create();

        AttendanceRecord::create([
            'attendance_id' => $attendance->id,
            'person_id' => $person1->id,
            'status' => 'present',
        ]);
        AttendanceRecord::create([
            'attendance_id' => $attendance->id,
            'person_id' => $person2->id,
            'status' => 'present',
        ]);

        $this->assertEquals(2, $attendance->present_count);
    }

    public function test_type_label_for_service(): void
    {
        $attendance = Attendance::factory()->forChurch($this->church)->service()->create();
        $this->assertEquals('Богослужіння', $attendance->type_label);
    }

    public function test_type_label_for_group(): void
    {
        $attendance = Attendance::factory()->forChurch($this->church)->group()->create();
        $this->assertEquals('Мала група', $attendance->type_label);
    }

    public function test_attendance_rate_with_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        // Add 4 members to the group
        $members = Person::factory()->forChurch($this->church)->count(4)->create();
        $group->members()->attach($members->pluck('id'));

        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => Group::class,
            'attendable_id' => $group->id,
            'type' => Attendance::TYPE_GROUP,
            'members_present' => 2,
        ]);

        // Mark 2 present
        foreach ($members->take(2) as $member) {
            AttendanceRecord::create([
                'attendance_id' => $attendance->id,
                'person_id' => $member->id,
                'present' => true,
            ]);
        }

        $this->assertEquals(50.0, $attendance->attendance_rate);
    }

    public function test_entity_name_returns_event_title(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $event = Event::factory()->forMinistry($ministry)->create([
            'title' => 'Воскресне служіння',
            'date' => now(),
        ]);

        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => Event::class,
            'attendable_id' => $event->id,
            'type' => Attendance::TYPE_EVENT,
        ]);

        $this->assertEquals('Воскресне служіння', $attendance->entity_name);
    }

    // ==================
    // Methods
    // ==================

    public function test_mark_present_creates_record(): void
    {
        $attendance = Attendance::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();

        $attendance->markPresent($person);

        $this->assertDatabaseHas('attendance_records', [
            'attendance_id' => $attendance->id,
            'person_id' => $person->id,
            'present' => true,
        ]);
    }

    public function test_mark_absent_creates_record(): void
    {
        $attendance = Attendance::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();

        $attendance->markAbsent($person);

        $this->assertDatabaseHas('attendance_records', [
            'attendance_id' => $attendance->id,
            'person_id' => $person->id,
            'present' => false,
        ]);
    }

    public function test_recalculate_counts_updates_totals(): void
    {
        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'total_count' => 0,
            'members_present' => 0,
            'guests_count' => 0,
        ]);
        $person1 = Person::factory()->forChurch($this->church)->create();
        $person2 = Person::factory()->forChurch($this->church)->create();

        AttendanceRecord::create([
            'attendance_id' => $attendance->id,
            'person_id' => $person1->id,
            'status' => 'present',
        ]);
        AttendanceRecord::create([
            'attendance_id' => $attendance->id,
            'person_id' => $person2->id,
            'status' => 'present',
        ]);

        $attendance->recalculateCounts();
        $attendance->refresh();

        $this->assertEquals(2, $attendance->members_present);
    }

    // ==================
    // Scopes
    // ==================

    public function test_for_month_scope(): void
    {
        Attendance::factory()->forChurch($this->church)->create(['date' => '2025-03-15']);
        Attendance::factory()->forChurch($this->church)->create(['date' => '2025-04-15']);

        $march = Attendance::forMonth(2025, 3)->get();
        $this->assertCount(1, $march);
    }

    public function test_services_scope(): void
    {
        Attendance::factory()->forChurch($this->church)->service()->create();
        Attendance::factory()->forChurch($this->church)->group()->create();

        $services = Attendance::services()->get();
        $this->assertCount(1, $services);
    }

    public function test_for_entity_scope(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $event = Event::factory()->forMinistry($ministry)->create(['date' => now()]);

        Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => Event::class,
            'attendable_id' => $event->id,
        ]);
        Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => 'App\\Models\\Group',
            'attendable_id' => 999,
        ]);

        $eventAttendances = Attendance::forEntity(Event::class, $event->id)->get();
        $this->assertCount(1, $eventAttendances);
    }
}
