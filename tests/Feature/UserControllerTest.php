<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
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

    public function test_admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/settings/users');

        $response->assertStatus(200);
    }

    public function test_volunteer_cannot_view_users(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/settings/users');

        $response->assertStatus(403);
    }

    // ==================
    // Create / Store
    // ==================

    public function test_admin_can_create_user(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)->first();

        $response = $this->actingAs($this->admin)->post('/settings/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'church_role_id' => $role->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'church_id' => $this->church->id,
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_admin_can_create_user_linked_to_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'email' => 'person@example.com',
        ]);
        $role = ChurchRole::where('church_id', $this->church->id)->first();

        $response = $this->actingAs($this->admin)->post('/settings/users', [
            'person_id' => $person->id,
            'church_role_id' => $role->id,
        ]);

        $response->assertRedirect();
        $person->refresh();
        $this->assertNotNull($person->user_id);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_user_role(): void
    {
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $leaderRole = ChurchRole::firstOrCreate(
            ['church_id' => $this->church->id, 'slug' => 'leader'],
            ['name' => 'Лідер', 'is_admin_role' => false, 'sort_order' => 1]
        );

        $response = $this->actingAs($this->admin)->put("/settings/users/{$user->id}", [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'church_role_id' => $leaderRole->id,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals($leaderRole->id, $user->church_role_id);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create([
            'church_id' => $this->church->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/settings/users/{$user->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete("/settings/users/{$this->admin->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'deleted_at' => null]);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_manage_user_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherUser = User::factory()->create([
            'church_id' => $otherChurch->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/settings/users/{$otherUser->id}/edit");

        $response->assertStatus(404);
    }

    public function test_cannot_delete_user_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherUser = User::factory()->create([
            'church_id' => $otherChurch->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/settings/users/{$otherUser->id}");

        $response->assertStatus(404);
    }
}
