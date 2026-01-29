<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryControllerTest extends TestCase
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

    public function test_admin_can_view_ministries_index(): void
    {
        Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->get('/ministries');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_ministries(): void
    {
        $response = $this->get('/ministries');

        $response->assertRedirect('/login');
    }

    public function test_volunteer_with_permission_can_view_ministries(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/ministries');

        $response->assertStatus(200);
    }

    // ==================
    // Create / Store
    // ==================

    public function test_admin_can_create_ministry(): void
    {
        $response = $this->actingAs($this->admin)->post('/ministries', [
            'name' => 'Worship Team',
            'description' => 'A worship ministry',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministries', [
            'church_id' => $this->church->id,
            'name' => 'Worship Team',
        ]);
    }

    public function test_ministry_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/ministries', [
            'description' => 'No name provided',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ==================
    // Show
    // ==================

    public function test_admin_can_view_ministry(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_PUBLIC,
        ]);

        $response = $this->actingAs($this->admin)->get("/ministries/{$ministry->id}");

        $response->assertStatus(200);
    }

    public function test_cannot_view_ministry_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->get("/ministries/{$otherMinistry->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_ministry(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->put("/ministries/{$ministry->id}", [
            'name' => 'Updated Ministry',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministries', [
            'id' => $ministry->id,
            'name' => 'Updated Ministry',
        ]);
    }

    public function test_leader_can_update_own_ministry(): void
    {
        $leader = $this->createUserWithRole($this->church, 'leader');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $leader->id]);
        $ministry = Ministry::factory()->forChurch($this->church)->withLeader($person)->create();

        $response = $this->actingAs($leader)->put("/ministries/{$ministry->id}", [
            'name' => 'Leader Updated',
        ]);

        $response->assertRedirect();
    }

    public function test_cannot_update_ministry_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->put("/ministries/{$otherMinistry->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(404);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_ministry(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->delete("/ministries/{$ministry->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('ministries', ['id' => $ministry->id]);
    }

    // ==================
    // Members
    // ==================

    public function test_admin_can_add_member(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->post("/ministries/{$ministry->id}/members", [
            'person_id' => $person->id,
        ]);

        $response->assertRedirect();
        $this->assertTrue($ministry->members()->where('person_id', $person->id)->exists());
    }

    public function test_cannot_add_member_from_other_church(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->post("/ministries/{$ministry->id}/members", [
            'person_id' => $otherPerson->id,
        ]);

        $response->assertStatus(302);
        $this->assertFalse($ministry->members()->where('person_id', $otherPerson->id)->exists());
    }

    public function test_admin_can_remove_member(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();
        $ministry->members()->attach($person);

        $response = $this->actingAs($this->admin)->delete("/ministries/{$ministry->id}/members/{$person->id}");

        $response->assertRedirect();
        $this->assertFalse($ministry->members()->where('person_id', $person->id)->exists());
    }

    public function test_cannot_add_duplicate_member(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();
        $ministry->members()->attach($person);

        $response = $this->actingAs($this->admin)->post("/ministries/{$ministry->id}/members", [
            'person_id' => $person->id,
        ]);

        $response->assertSessionHas('error');
    }
}
