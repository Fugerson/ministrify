<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\Group;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupAttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->church->update(['attendance_enabled' => true]);
        $this->group = Group::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Attendance disabled
    // ==================

    public function test_attendance_disabled_returns_403_on_index(): void
    {
        $this->church->update(['attendance_enabled' => false]);

        $response = $this->actingAs($this->admin)->get("/groups/{$this->group->id}/attendance");

        $response->assertStatus(403);
    }

    public function test_attendance_disabled_returns_403_on_create(): void
    {
        $this->church->update(['attendance_enabled' => false]);

        $response = $this->actingAs($this->admin)->get("/groups/{$this->group->id}/attendance/create");

        $response->assertStatus(403);
    }

    public function test_attendance_disabled_returns_403_on_store(): void
    {
        $this->church->update(['attendance_enabled' => false]);

        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", [
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(403);
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_group_attendance_index(): void
    {
        $response = $this->actingAs($this->admin)->get("/groups/{$this->group->id}/attendance");

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_group_attendance(): void
    {
        $response = $this->get("/groups/{$this->group->id}/attendance");

        $response->assertRedirect('/login');
    }

    // ==================
    // Create
    // ==================

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get("/groups/{$this->group->id}/attendance/create");

        $response->assertStatus(200);
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_store_group_attendance(): void
    {
        $members = Person::factory()->count(3)->create(['church_id' => $this->church->id]);
        foreach ($members as $member) {
            $this->group->members()->attach($member->id, ['role' => 'member']);
        }

        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", [
            'date' => now()->format('Y-m-d'),
            'notes' => 'Group meeting',
            'present' => $members->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'church_id' => $this->church->id,
            'attendable_type' => Group::class,
            'attendable_id' => $this->group->id,
        ]);

        $attendance = Attendance::where('attendable_id', $this->group->id)
            ->where('attendable_type', Group::class)
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(3, $attendance->records()->count());
        $this->assertEquals(3, $attendance->records()->where('present', true)->count());
    }

    public function test_store_creates_attendance_records_for_present_members(): void
    {
        $members = Person::factory()->count(3)->create(['church_id' => $this->church->id]);
        foreach ($members as $member) {
            $this->group->members()->attach($member->id, ['role' => 'member']);
        }

        // Only mark first 2 as present
        $presentIds = $members->take(2)->pluck('id')->toArray();

        $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", [
            'date' => now()->format('Y-m-d'),
            'present' => $presentIds,
        ]);

        $attendance = Attendance::where('attendable_id', $this->group->id)
            ->where('attendable_type', Group::class)
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(2, $attendance->members_present);
        $this->assertEquals(3, $attendance->records()->count());
        $this->assertEquals(2, $attendance->records()->where('present', true)->count());
        $this->assertEquals(1, $attendance->records()->where('present', false)->count());
    }

    public function test_store_with_guests_count(): void
    {
        $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", [
            'date' => now()->format('Y-m-d'),
            'guests_count' => 5,
        ]);

        $attendance = Attendance::where('attendable_id', $this->group->id)
            ->where('attendable_type', Group::class)
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(5, $attendance->guests_count);
    }

    public function test_store_validates_date_required(): void
    {
        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", []);

        $response->assertSessionHasErrors(['date']);
    }

    public function test_store_prevents_duplicate_for_same_date(): void
    {
        $date = now()->format('Y-m-d');

        // First attendance
        $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", [
            'date' => $date,
        ]);

        // Second attendance for same date — should redirect to edit
        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/attendance", [
            'date' => $date,
        ]);

        $response->assertRedirect();
        $count = Attendance::where('attendable_id', $this->group->id)
            ->where('attendable_type', Group::class)
            ->whereDate('date', $date)
            ->count();
        $this->assertEquals(1, $count);
    }

    // ==================
    // Show
    // ==================

    public function test_admin_can_view_attendance_show(): void
    {
        $attendance = $this->group->createAttendance([
            'date' => now()->format('Y-m-d'),
            'total_count' => 10,
        ]);

        $response = $this->actingAs($this->admin)->get("/groups/{$this->group->id}/attendance/{$attendance->id}");

        $response->assertStatus(200);
    }

    public function test_show_returns_404_for_attendance_from_other_group(): void
    {
        $otherGroup = Group::factory()->forChurch($this->church)->create();
        $attendance = $otherGroup->createAttendance([
            'date' => now()->format('Y-m-d'),
            'total_count' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get("/groups/{$this->group->id}/attendance/{$attendance->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_group_attendance(): void
    {
        $attendance = $this->group->createAttendance([
            'date' => now()->format('Y-m-d'),
            'total_count' => 10,
        ]);

        $response = $this->actingAs($this->admin)->put("/groups/{$this->group->id}/attendance/{$attendance->id}", [
            'date' => now()->format('Y-m-d'),
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect();
        $attendance->refresh();
        $this->assertEquals('Updated notes', $attendance->notes);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_group_attendance(): void
    {
        $attendance = $this->group->createAttendance([
            'date' => now()->format('Y-m-d'),
            'total_count' => 10,
        ]);

        $response = $this->actingAs($this->admin)->delete("/groups/{$this->group->id}/attendance/{$attendance->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('attendances', ['id' => $attendance->id, 'deleted_at' => null]);
    }

    public function test_destroy_returns_404_for_attendance_from_other_group(): void
    {
        $otherGroup = Group::factory()->forChurch($this->church)->create();
        $attendance = $otherGroup->createAttendance([
            'date' => now()->format('Y-m-d'),
            'total_count' => 5,
        ]);

        $response = $this->actingAs($this->admin)->delete("/groups/{$this->group->id}/attendance/{$attendance->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_access_group_from_other_church(): void
    {
        $otherChurch = Church::factory()->create(['attendance_enabled' => true]);
        $otherGroup = Group::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->get("/groups/{$otherGroup->id}/attendance");

        $response->assertStatus(404);
    }

    public function test_cannot_store_attendance_for_group_from_other_church(): void
    {
        $otherChurch = Church::factory()->create(['attendance_enabled' => true]);
        $otherGroup = Group::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->post("/groups/{$otherGroup->id}/attendance", [
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(404);
    }
}
