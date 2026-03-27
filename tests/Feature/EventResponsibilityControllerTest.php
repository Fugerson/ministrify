<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventResponsibility;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EventResponsibilityControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Ministry $ministry;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
        $this->event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_create_responsibility(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/responsibilities", [
                'name' => 'Open doors',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('event_responsibilities', [
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);
    }

    public function test_admin_can_create_responsibility_with_notes(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/responsibilities", [
                'name' => 'Snacks',
                'notes' => 'Bring cookies and juice',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('event_responsibilities', [
            'event_id' => $this->event->id,
            'name' => 'Snacks',
            'notes' => 'Bring cookies and juice',
        ]);
    }

    public function test_cannot_create_responsibility_without_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/responsibilities", [
                'name' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_volunteer_without_permission_cannot_create_responsibility(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/responsibilities", [
                'name' => 'Open doors',
            ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_responsibility(): void
    {
        $response = $this->postJson("/events/{$this->event->id}/responsibilities", [
            'name' => 'Open doors',
        ]);

        $response->assertStatus(401);
    }

    // ==================
    // Assign
    // ==================

    public function test_admin_can_assign_person_to_responsibility(): void
    {
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/responsibilities/{$responsibility->id}/assign", [
                'person_id' => $person->id,
            ]);

        $response->assertOk();

        $responsibility->refresh();
        $this->assertEquals($person->id, $responsibility->person_id);
        $this->assertEquals('pending', $responsibility->status);
    }

    public function test_cannot_assign_person_from_other_church(): void
    {
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $otherChurch = Church::factory()->create();
        $foreignPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/responsibilities/{$responsibility->id}/assign", [
                'person_id' => $foreignPerson->id,
            ]);

        $response->assertStatus(422);
    }

    // ==================
    // Unassign
    // ==================

    public function test_admin_can_unassign_person_from_responsibility(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'pending',
            'person_id' => $person->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/responsibilities/{$responsibility->id}/unassign");

        $response->assertOk();

        $responsibility->refresh();
        $this->assertNull($responsibility->person_id);
        $this->assertEquals('open', $responsibility->status);
    }

    // ==================
    // Confirm / Decline
    // ==================

    public function test_assigned_person_can_confirm_responsibility(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->update(['person_id' => $person->id]);

        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'pending',
            'person_id' => $person->id,
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/responsibilities/{$responsibility->id}/confirm");

        $response->assertOk();

        $responsibility->refresh();
        $this->assertEquals('confirmed', $responsibility->status);
        $this->assertNotNull($responsibility->responded_at);
    }

    public function test_assigned_person_can_decline_responsibility(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->update(['person_id' => $person->id]);

        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'pending',
            'person_id' => $person->id,
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/responsibilities/{$responsibility->id}/decline");

        $response->assertOk();

        $responsibility->refresh();
        $this->assertEquals('declined', $responsibility->status);
        $this->assertNotNull($responsibility->responded_at);
    }

    public function test_admin_can_confirm_responsibility(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'pending',
            'person_id' => $person->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/responsibilities/{$responsibility->id}/confirm");

        $response->assertOk();

        $responsibility->refresh();
        $this->assertEquals('confirmed', $responsibility->status);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_responsibility(): void
    {
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/responsibilities/{$responsibility->id}", [
                'name' => 'Close doors',
                'notes' => 'After service ends',
            ]);

        $response->assertOk();

        $responsibility->refresh();
        $this->assertEquals('Close doors', $responsibility->name);
        $this->assertEquals('After service ends', $responsibility->notes);
    }

    public function test_cannot_update_responsibility_without_name(): void
    {
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/responsibilities/{$responsibility->id}", [
                'name' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_responsibility(): void
    {
        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/responsibilities/{$responsibility->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('event_responsibilities', [
            'id' => $responsibility->id,
        ]);
    }

    public function test_volunteer_without_permission_cannot_delete_responsibility(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions([]);

        $responsibility = EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $response = $this->actingAs($volunteer)
            ->deleteJson("/responsibilities/{$responsibility->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('event_responsibilities', [
            'id' => $responsibility->id,
        ]);
    }

    // ==================
    // Poll
    // ==================

    public function test_admin_can_poll_responsibilities(): void
    {
        EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        EventResponsibility::create([
            'event_id' => $this->event->id,
            'name' => 'Snacks',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/events/{$this->event->id}/responsibilities/poll");

        $response->assertOk();
        $response->assertJsonStructure([
            'responsibilities',
            'server_time',
        ]);

        $this->assertCount(2, $response->json('responsibilities'));
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_create_responsibility_for_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$otherEvent->id}/responsibilities", [
                'name' => 'Open doors',
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_poll_responsibilities_of_other_church_event(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/events/{$otherEvent->id}/responsibilities/poll");

        $response->assertStatus(404);
    }

    public function test_cannot_assign_to_responsibility_of_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $responsibility = EventResponsibility::create([
            'event_id' => $otherEvent->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/responsibilities/{$responsibility->id}/assign", [
                'person_id' => $otherPerson->id,
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_responsibility_of_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $responsibility = EventResponsibility::create([
            'event_id' => $otherEvent->id,
            'name' => 'Open doors',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/responsibilities/{$responsibility->id}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('event_responsibilities', [
            'id' => $responsibility->id,
        ]);
    }
}
