<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\Ministry;
use App\Models\MinistryRole;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ServiceTeamControllerTest extends TestCase
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
    // Link Ministry
    // ==================

    public function test_admin_can_link_ministry_to_event(): void
    {
        $otherMinistry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/link-ministry", [
                'ministry_id' => $otherMinistry->id,
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('event_ministry', [
            'event_id' => $this->event->id,
            'ministry_id' => $otherMinistry->id,
        ]);
    }

    public function test_cannot_link_ministry_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $foreignMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/link-ministry", [
                'ministry_id' => $foreignMinistry->id,
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('event_ministry', [
            'event_id' => $this->event->id,
            'ministry_id' => $foreignMinistry->id,
        ]);
    }

    public function test_volunteer_without_permission_cannot_link_ministry(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/link-ministry", [
                'ministry_id' => $this->ministry->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_link_ministry(): void
    {
        $response = $this->postJson("/events/{$this->event->id}/link-ministry", [
            'ministry_id' => $this->ministry->id,
        ]);

        $response->assertStatus(401);
    }

    // ==================
    // Unlink Ministry
    // ==================

    public function test_admin_can_unlink_ministry_from_event(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/events/{$this->event->id}/unlink-ministry/{$this->ministry->id}");

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('event_ministry', [
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
        ]);
    }

    public function test_unlink_ministry_also_removes_team_assignments(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $person = Person::factory()->forChurch($this->church)->create();
        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        EventMinistryTeam::create([
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson("/events/{$this->event->id}/unlink-ministry/{$this->ministry->id}");

        $this->assertDatabaseMissing('event_ministry_team', [
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
        ]);
    }

    // ==================
    // Add Team Member
    // ==================

    public function test_admin_can_add_team_member(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $person = Person::factory()->forChurch($this->church)->create();
        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/ministry-team", [
                'ministry_id' => $this->ministry->id,
                'person_id' => $person->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('event_ministry_team', [
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
        ]);
    }

    public function test_cannot_add_duplicate_team_member(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $person = Person::factory()->forChurch($this->church)->create();
        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        EventMinistryTeam::create([
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/ministry-team", [
                'ministry_id' => $this->ministry->id,
                'person_id' => $person->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_add_team_member_with_person_from_other_church(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $otherChurch = Church::factory()->create();
        $foreignPerson = Person::factory()->forChurch($otherChurch)->create();
        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/ministry-team", [
                'ministry_id' => $this->ministry->id,
                'person_id' => $foreignPerson->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_add_team_member_with_role_from_other_ministry(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $person = Person::factory()->forChurch($this->church)->create();
        $otherMinistry = Ministry::factory()->forChurch($this->church)->create();
        $wrongRole = MinistryRole::create([
            'ministry_id' => $otherMinistry->id,
            'name' => 'Drummer',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/ministry-team", [
                'ministry_id' => $this->ministry->id,
                'person_id' => $person->id,
                'ministry_role_id' => $wrongRole->id,
            ]);

        $response->assertStatus(404);
    }

    // ==================
    // Remove Team Member
    // ==================

    public function test_admin_can_remove_team_member(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $person = Person::factory()->forChurch($this->church)->create();
        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $member = EventMinistryTeam::create([
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/events/{$this->event->id}/ministry-team/{$member->id}");

        $response->assertOk();
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('event_ministry_team', [
            'id' => $member->id,
        ]);
    }

    // ==================
    // Self-Signup
    // ==================

    public function test_ministry_member_can_self_signup(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        $this->ministry->members()->attach($person->id);

        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/self-signup", [
                'ministry_id' => $this->ministry->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('event_ministry_team', [
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_non_ministry_member_cannot_self_signup(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        // NOT attaching to ministry members

        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/self-signup", [
                'ministry_id' => $this->ministry->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_self_signup_to_unlinked_ministry(): void
    {
        // Ministry NOT linked to event
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        $this->ministry->members()->attach($person->id);

        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/self-signup", [
                'ministry_id' => $this->ministry->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_self_signup_duplicate(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        $this->ministry->members()->attach($person->id);

        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        EventMinistryTeam::create([
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/self-signup", [
                'ministry_id' => $this->ministry->id,
                'ministry_role_id' => $role->id,
            ]);

        $response->assertStatus(422);
    }

    // ==================
    // Self-Unsubscribe
    // ==================

    public function test_member_can_self_unsubscribe(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);

        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $member = EventMinistryTeam::create([
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $person->id,
            'ministry_role_id' => $role->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($volunteer)
            ->deleteJson("/events/{$this->event->id}/self-unsubscribe/{$member->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('event_ministry_team', [
            'id' => $member->id,
        ]);
    }

    public function test_cannot_unsubscribe_another_person(): void
    {
        $this->event->linkedMinistries()->attach($this->ministry->id);
        $otherPerson = Person::factory()->forChurch($this->church)->create();
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $myPerson = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);

        $role = MinistryRole::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'Vocalist',
        ]);

        $otherMember = EventMinistryTeam::create([
            'event_id' => $this->event->id,
            'ministry_id' => $this->ministry->id,
            'person_id' => $otherPerson->id,
            'ministry_role_id' => $role->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($volunteer)
            ->deleteJson("/events/{$this->event->id}/self-unsubscribe/{$otherMember->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('event_ministry_team', [
            'id' => $otherMember->id,
        ]);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_modify_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$otherEvent->id}/link-ministry", [
                'ministry_id' => $this->ministry->id,
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_unlink_ministry_from_other_church_event(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/events/{$otherEvent->id}/unlink-ministry/{$otherMinistry->id}");

        $response->assertStatus(404);
    }
}
