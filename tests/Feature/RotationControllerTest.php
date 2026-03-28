<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RotationControllerTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/rotation');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_rotation_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/rotation');

        $response->assertStatus(200);
    }

    public function test_volunteer_without_ministries_permission_gets_403(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        // Ensure volunteer has NO ministries view permission
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->setPermissions(['events' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/rotation');

        $response->assertStatus(403);
    }

    public function test_volunteer_with_ministries_view_permission_can_access_index(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/rotation');

        $response->assertStatus(200);
    }

    // ==================
    // Ministry Detail
    // ==================

    public function test_admin_can_view_ministry_rotation(): void
    {
        $response = $this->actingAs($this->admin)->get("/rotation/ministry/{$this->ministry->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_view_ministry_rotation_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->get("/rotation/ministry/{$otherMinistry->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Assign Position
    // ==================

    public function test_admin_can_assign_person_to_position_for_event(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/rotation/event/{$event->id}/assign-position", [
                'position_id' => $position->id,
                'person_id' => $person->id,
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('assignments', [
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => Assignment::STATUS_PENDING,
        ]);
    }

    public function test_cannot_assign_duplicate_person_to_same_position(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        // Create existing assignment
        Assignment::create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => Assignment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/rotation/event/{$event->id}/assign-position", [
                'position_id' => $position->id,
                'person_id' => $person->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Ця людина вже призначена на цю позицію']);
    }

    public function test_cannot_assign_to_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/rotation/event/{$otherEvent->id}/assign-position", [
                'position_id' => $position->id,
                'person_id' => $person->id,
            ]);

        // Should fail - event belongs to other church
        $response->assertStatus(404);
    }

    public function test_assign_validates_position_belongs_to_church(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();

        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherPosition = Position::create([
            'ministry_id' => $otherMinistry->id,
            'name' => 'Other Position',
        ]);

        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/rotation/event/{$event->id}/assign-position", [
                'position_id' => $otherPosition->id,
                'person_id' => $person->id,
            ]);

        // Position from other church is rejected (404 from findOrFail or 422 from validation)
        $this->assertTrue(
            in_array($response->status(), [404, 422]),
            'Expected 404 or 422 for position from other church, got '.$response->status()
        );
    }

    public function test_assign_validates_person_belongs_to_church(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);

        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/rotation/event/{$event->id}/assign-position", [
                'position_id' => $position->id,
                'person_id' => $otherPerson->id,
            ]);

        // BelongsToChurch rule should reject
        $response->assertStatus(422);
    }

    public function test_volunteer_without_edit_permission_cannot_assign(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($volunteer)
            ->postJson("/rotation/event/{$event->id}/assign-position", [
                'position_id' => $position->id,
                'person_id' => $person->id,
            ]);

        $response->assertStatus(403);
    }

    // ==================
    // Remove Assignment
    // ==================

    public function test_admin_can_remove_assignment(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => Assignment::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/rotation/assignment/{$assignment->id}");

        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('assignments', ['id' => $assignment->id]);
    }

    public function test_cannot_remove_assignment_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();
        $position = Position::create([
            'ministry_id' => $otherMinistry->id,
            'name' => 'Other Position',
        ]);

        $assignment = Assignment::create([
            'event_id' => $otherEvent->id,
            'position_id' => $position->id,
            'person_id' => $otherPerson->id,
            'status' => Assignment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/rotation/assignment/{$assignment->id}");

        $response->assertStatus(404);
    }

    public function test_volunteer_without_edit_permission_cannot_remove_assignment(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => Assignment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($volunteer)
            ->deleteJson("/rotation/assignment/{$assignment->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Report
    // ==================

    public function test_admin_can_view_rotation_report(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/rotation/report/{$this->ministry->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_view_report_for_other_church_ministry(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->get("/rotation/report/{$otherMinistry->id}");

        $response->assertStatus(403);
    }

    public function test_report_accepts_date_range_params(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/rotation/report/{$this->ministry->id}?start=2025-01-01&end=2025-03-31");

        $response->assertStatus(200);
    }

    // ==================
    // Volunteer Stats
    // ==================

    public function test_admin_can_view_volunteer_stats(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/rotation/volunteer/{$person->id}/stats");

        $response->assertStatus(200);
    }

    public function test_cannot_view_stats_for_person_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->get("/rotation/volunteer/{$otherPerson->id}/stats");

        $response->assertStatus(404);
    }

    public function test_volunteer_stats_returns_json_for_ajax(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/rotation/volunteer/{$person->id}/stats");

        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    // ==================
    // Update Assignment Notes
    // ==================

    public function test_admin_can_update_assignment_notes(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $position = Position::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Sound Tech',
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => Assignment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/rotation/assignment/{$assignment->id}/notes", [
                'notes' => 'Bring extra cables',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'notes' => 'Bring extra cables',
        ]);
    }
}
