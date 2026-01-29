<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Group;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_groups_index(): void
    {
        Group::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->get('/groups');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_groups(): void
    {
        $response = $this->get('/groups');

        $response->assertRedirect('/login');
    }

    public function test_volunteer_with_permission_can_view_groups(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['groups' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/groups');

        $response->assertStatus(200);
    }

    // ==================
    // Create / Store
    // ==================

    public function test_admin_can_create_group(): void
    {
        $response = $this->actingAs($this->admin)->post('/groups', [
            'name' => 'Small Group Alpha',
            'description' => 'A new group',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'church_id' => $this->church->id,
            'name' => 'Small Group Alpha',
        ]);
    }

    public function test_group_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/groups', [
            'description' => 'No name',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_leader_can_create_group(): void
    {
        $leader = $this->createUserWithRole($this->church, 'leader');

        $response = $this->actingAs($leader)->post('/groups', [
            'name' => 'Leader Group',
        ]);

        $response->assertRedirect();
    }

    public function test_group_with_leader_adds_leader_as_member(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->post('/groups', [
            'name' => 'Group with Leader',
            'leader_id' => $person->id,
        ]);

        $response->assertRedirect();
        $group = Group::where('name', 'Group with Leader')->first();
        $this->assertTrue($group->members()->where('person_id', $person->id)->exists());
    }

    // ==================
    // Show
    // ==================

    public function test_admin_can_view_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->get("/groups/{$group->id}");

        $response->assertStatus(200);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->put("/groups/{$group->id}", [
            'name' => 'Updated Group',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Group',
        ]);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->delete("/groups/{$group->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('groups', ['id' => $group->id]);
    }

    public function test_volunteer_cannot_delete_group(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['groups' => ['view']]);
        $group = Group::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($volunteer)->delete("/groups/{$group->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Members
    // ==================

    public function test_admin_can_add_member_to_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->post("/groups/{$group->id}/members", [
            'person_id' => $person->id,
        ]);

        $response->assertRedirect();
        $this->assertTrue($group->members()->where('person_id', $person->id)->exists());
    }

    public function test_cannot_add_member_from_other_church(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->post("/groups/{$group->id}/members", [
            'person_id' => $otherPerson->id,
        ]);

        $response->assertStatus(302);
        $this->assertFalse($group->members()->where('person_id', $otherPerson->id)->exists());
    }

    public function test_admin_can_remove_member_from_group(): void
    {
        $group = Group::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();
        $group->members()->attach($person, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($this->admin)->delete("/groups/{$group->id}/members/{$person->id}");

        $response->assertRedirect();
        $this->assertFalse($group->members()->where('person_id', $person->id)->exists());
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_volunteer_cannot_view_group_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherGroup = Group::factory()->forChurch($otherChurch)->create();
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get("/groups/{$otherGroup->id}");

        // GroupPolicy::view checks church_id match; volunteer has no Gate::before bypass
        $response->assertStatus(403);
    }
}
