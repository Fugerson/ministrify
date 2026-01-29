<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\Church;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // Status Helpers
    // ==================

    public function test_is_active(): void
    {
        $group = Group::factory()->forChurch($this->church)->active()->create();

        $this->assertTrue($group->isActive());
    }

    public function test_is_not_active_when_paused(): void
    {
        $group = Group::factory()->forChurch($this->church)->paused()->create();

        $this->assertFalse($group->isActive());
    }

    public function test_status_label(): void
    {
        $active = Group::factory()->forChurch($this->church)->active()->create();
        $this->assertEquals('Активна', $active->status_label);

        $paused = Group::factory()->forChurch($this->church)->paused()->create();
        $this->assertEquals('На паузі', $paused->status_label);

        $vacation = Group::factory()->forChurch($this->church)->vacation()->create();
        $this->assertEquals('У відпустці', $vacation->status_label);
    }

    public function test_status_color(): void
    {
        $active = Group::factory()->forChurch($this->church)->active()->create();
        $this->assertEquals('green', $active->status_color);

        $paused = Group::factory()->forChurch($this->church)->paused()->create();
        $this->assertEquals('yellow', $paused->status_color);

        $vacation = Group::factory()->forChurch($this->church)->vacation()->create();
        $this->assertEquals('blue', $vacation->status_color);
    }

    // ==================
    // Attendance
    // ==================

    public function test_average_attendance_with_records(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        Attendance::create([
            'church_id' => $this->church->id,
            'attendable_type' => Group::class,
            'attendable_id' => $group->id,
            'type' => Attendance::TYPE_GROUP,
            'date' => now()->subDays(7),
            'members_present' => 10,
            'total_count' => 12,
        ]);
        Attendance::create([
            'church_id' => $this->church->id,
            'attendable_type' => Group::class,
            'attendable_id' => $group->id,
            'type' => Attendance::TYPE_GROUP,
            'date' => now()->subDays(14),
            'members_present' => 8,
            'total_count' => 10,
        ]);

        $this->assertEquals(9.0, $group->average_attendance);
    }

    public function test_average_attendance_empty(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        $this->assertEquals(0, $group->average_attendance);
    }

    public function test_attendance_trend_stable_with_few_records(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        Attendance::create([
            'church_id' => $this->church->id,
            'attendable_type' => Group::class,
            'attendable_id' => $group->id,
            'type' => Attendance::TYPE_GROUP,
            'date' => now(),
            'members_present' => 10,
            'total_count' => 10,
        ]);

        $this->assertEquals('stable', $group->attendance_trend);
    }

    public function test_create_attendance(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        $attendance = $group->createAttendance([
            'date' => now(),
            'total_count' => 15,
            'members_present' => 12,
            'guests_count' => 3,
        ]);

        $this->assertInstanceOf(Attendance::class, $attendance);
        $this->assertEquals($this->church->id, $attendance->church_id);
        $this->assertEquals(Group::class, $attendance->attendable_type);
        $this->assertEquals($group->id, $attendance->attendable_id);
        $this->assertEquals(Attendance::TYPE_GROUP, $attendance->type);
        $this->assertEquals(12, $attendance->members_present);
    }

    // ==================
    // Batch Loading
    // ==================

    public function test_load_attendance_stats(): void
    {
        $group1 = Group::factory()->forChurch($this->church)->create();
        $group2 = Group::factory()->forChurch($this->church)->create();

        Attendance::create([
            'church_id' => $this->church->id,
            'attendable_type' => Group::class,
            'attendable_id' => $group1->id,
            'type' => Attendance::TYPE_GROUP,
            'date' => now(),
            'members_present' => 10,
            'total_count' => 12,
        ]);

        $groups = collect([$group1, $group2]);
        Group::loadAttendanceStats($groups);

        $this->assertEquals(10.0, $group1->average_attendance);
        $this->assertEquals(0, $group2->average_attendance);
    }

    // ==================
    // Relationships
    // ==================

    public function test_leader_relationship(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $group = Group::factory()->forChurch($this->church)->withLeader($person)->create();

        $this->assertEquals($person->id, $group->leader->id);
    }

    public function test_members_relationship(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();

        $group->members()->attach($person, ['role' => Group::ROLE_MEMBER]);

        $this->assertCount(1, $group->members);
        $this->assertEquals(Group::ROLE_MEMBER, $group->members->first()->pivot->role);
    }
}
