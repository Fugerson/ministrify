<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
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

    public function test_admin_can_view_events_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/events');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_events(): void
    {
        $response = $this->get('/events');

        $response->assertRedirect('/login');
    }

    public function test_volunteer_with_permission_can_view_events(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['events' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/events');

        $response->assertStatus(200);
    }

    // ==================
    // Create / Store
    // ==================

    public function test_admin_can_create_event(): void
    {
        $response = $this->actingAs($this->admin)->post('/events', [
            'title' => 'Sunday Service',
            'date' => now()->addWeek()->format('Y-m-d'),
            'time' => '10:00',
            'ministry_id' => $this->ministry->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'church_id' => $this->church->id,
            'title' => 'Sunday Service',
        ]);
    }

    public function test_event_requires_title_and_date(): void
    {
        $response = $this->actingAs($this->admin)->post('/events', [
            'ministry_id' => $this->ministry->id,
        ]);

        $response->assertSessionHasErrors(['title', 'date']);
    }

    public function test_leader_can_create_event(): void
    {
        $leader = $this->createUserWithRole($this->church, 'leader');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $leader->id]);
        // Make them the actual leader of this ministry
        $this->ministry->update(['leader_id' => $person->id]);

        $response = $this->actingAs($leader)->post('/events', [
            'title' => 'Leader Event',
            'date' => now()->addWeek()->format('Y-m-d'),
            'time' => '18:00',
            'ministry_id' => $this->ministry->id,
        ]);

        $response->assertRedirect();
    }

    // ==================
    // Show
    // ==================

    public function test_admin_can_view_event(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();

        $response = $this->actingAs($this->admin)->get("/events/{$event->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_view_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->create();

        $response = $this->actingAs($this->admin)->get("/events/{$otherEvent->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_event(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();

        $response = $this->actingAs($this->admin)->put("/events/{$event->id}", [
            'title' => 'Updated Event',
            'date' => now()->addWeek()->format('Y-m-d'),
            'time' => '10:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event',
        ]);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_event(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();

        $response = $this->actingAs($this->admin)->delete("/events/{$event->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_volunteer_cannot_delete_event(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['events' => ['view']]);
        $event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();

        $response = $this->actingAs($volunteer)->delete("/events/{$event->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Schedule View
    // ==================

    public function test_admin_can_view_schedule(): void
    {
        $response = $this->actingAs($this->admin)->get('/schedule');

        $response->assertStatus(200);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_update_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->create();

        $response = $this->actingAs($this->admin)->put("/events/{$otherEvent->id}", [
            'title' => 'Hacked',
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(404);
    }
}
