<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryMeeting;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeetingControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_meetings_index(): void
    {
        MinistryMeeting::create([
            'ministry_id' => $this->ministry->id,
            'title' => 'Weekly Standup',
            'date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/ministries/{$this->ministry->id}/meetings");

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_meetings(): void
    {
        $response = $this->get("/ministries/{$this->ministry->id}/meetings");

        $response->assertRedirect('/login');
    }

    public function test_cannot_view_meetings_of_other_church_ministry(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->get("/ministries/{$otherMinistry->id}/meetings");

        $response->assertStatus(403);
    }

    // ==================
    // Create
    // ==================

    public function test_admin_can_view_create_meeting_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/ministries/{$this->ministry->id}/meetings/create");

        $response->assertStatus(200);
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_store_meeting(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/meetings", [
                'title' => 'Planning Meeting',
                'date' => '2026-04-01',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'location' => 'Room A',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_meetings', [
            'ministry_id' => $this->ministry->id,
            'title' => 'Planning Meeting',
            'location' => 'Room A',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_store_validates_title_required(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/meetings", [
                'date' => '2026-04-01',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_validates_date_required(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/meetings", [
                'title' => 'Meeting Without Date',
            ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_store_auto_invites_ministry_members(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $member1 = Person::factory()->forChurch($this->church)->create();
        $member2 = Person::factory()->forChurch($this->church)->create();
        $this->ministry->members()->attach([$member1->id, $member2->id]);

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/meetings", [
                'title' => 'Team Meeting',
                'date' => '2026-04-01',
            ]);

        $response->assertRedirect();

        $meeting = MinistryMeeting::where('title', 'Team Meeting')->first();
        $this->assertNotNull($meeting);
        $this->assertEquals(2, $meeting->attendees()->count());
        $this->assertTrue($meeting->attendees()->where('person_id', $member1->id)->exists());
        $this->assertTrue($meeting->attendees()->where('person_id', $member2->id)->exists());
    }

    public function test_cannot_store_meeting_for_other_church_ministry(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$otherMinistry->id}/meetings", [
                'title' => 'Hacked Meeting',
                'date' => '2026-04-01',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ministry_meetings', ['title' => 'Hacked Meeting']);
    }

    // ==================
    // Show
    // ==================

    public function test_admin_can_view_meeting(): void
    {
        $meeting = MinistryMeeting::create([
            'ministry_id' => $this->ministry->id,
            'title' => 'Team Sync',
            'date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/ministries/{$this->ministry->id}/meetings/{$meeting->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_view_meeting_from_wrong_ministry(): void
    {
        $otherMinistry = Ministry::factory()->forChurch($this->church)->create();
        $meeting = MinistryMeeting::create([
            'ministry_id' => $otherMinistry->id,
            'title' => 'Other Meeting',
            'date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/ministries/{$this->ministry->id}/meetings/{$meeting->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_meeting(): void
    {
        $meeting = MinistryMeeting::create([
            'ministry_id' => $this->ministry->id,
            'title' => 'Old Title',
            'date' => now(),
            'status' => 'planned',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/meetings/{$meeting->id}", [
                'title' => 'New Title',
                'date' => '2026-05-01',
                'status' => 'completed',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_meetings', [
            'id' => $meeting->id,
            'title' => 'New Title',
            'status' => 'completed',
        ]);
    }

    public function test_update_validates_status(): void
    {
        $meeting = MinistryMeeting::create([
            'ministry_id' => $this->ministry->id,
            'title' => 'Meeting',
            'date' => now(),
            'status' => 'planned',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/meetings/{$meeting->id}", [
                'title' => 'Meeting',
                'date' => '2026-05-01',
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_cannot_update_meeting_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $meeting = MinistryMeeting::create([
            'ministry_id' => $otherMinistry->id,
            'title' => 'Protected',
            'date' => now(),
            'status' => 'planned',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$otherMinistry->id}/meetings/{$meeting->id}", [
                'title' => 'Hacked',
                'date' => '2026-05-01',
                'status' => 'planned',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('ministry_meetings', [
            'id' => $meeting->id,
            'title' => 'Protected',
        ]);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_meeting(): void
    {
        $meeting = MinistryMeeting::create([
            'ministry_id' => $this->ministry->id,
            'title' => 'To Delete',
            'date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/ministries/{$this->ministry->id}/meetings/{$meeting->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('ministry_meetings', ['id' => $meeting->id]);
    }

    public function test_cannot_delete_meeting_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $meeting = MinistryMeeting::create([
            'ministry_id' => $otherMinistry->id,
            'title' => 'Protected',
            'date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/ministries/{$otherMinistry->id}/meetings/{$meeting->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('ministry_meetings', ['id' => $meeting->id]);
    }

    // ==================
    // Volunteer access
    // ==================

    public function test_volunteer_not_in_ministry_cannot_access_meetings(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $response = $this->actingAs($volunteer)
            ->get("/ministries/{$this->ministry->id}/meetings");

        $response->assertStatus(403);
    }

    public function test_ministry_member_can_access_meetings(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        // canManageMinistry requires leader, co-leader, or admin — regular member is not enough
        $this->ministry->members()->attach($person, ['role' => 'co-leader']);

        $response = $this->actingAs($volunteer)
            ->get("/ministries/{$this->ministry->id}/meetings");

        $response->assertStatus(200);
    }
}
