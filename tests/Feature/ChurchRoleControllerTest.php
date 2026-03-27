<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChurchRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_church_roles_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/settings/church-roles');

        $response->assertStatus(200);
    }

    public function test_index_shows_roles_with_people_count(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'volunteer')
            ->first();

        // Create a person with this role
        Person::factory()->forChurch($this->church)->create([
            'church_role_id' => $role->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/settings/church-roles');

        $response->assertStatus(200);
        $response->assertViewHas('roles');
    }

    public function test_volunteer_can_view_church_roles_index(): void
    {
        // GET /settings/church-roles only requires permission:settings (view)
        // Volunteers don't have settings permission at all
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/settings/church-roles');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_church_roles(): void
    {
        $response = $this->get('/settings/church-roles');

        $response->assertRedirect('/login');
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_create_church_role(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'name' => 'Deacon',
            'color' => '#10b981',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('church_roles', [
            'church_id' => $this->church->id,
            'name' => 'Deacon',
            'slug' => 'deacon',
            'color' => '#10b981',
        ]);
    }

    public function test_store_creates_with_correct_slug(): void
    {
        $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'name' => 'Youth Leader',
            'color' => '#f59e0b',
        ]);

        $this->assertDatabaseHas('church_roles', [
            'church_id' => $this->church->id,
            'slug' => 'youth-leader',
        ]);
    }

    public function test_store_creates_with_incremented_sort_order(): void
    {
        $maxOrder = ChurchRole::where('church_id', $this->church->id)->max('sort_order');

        $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'name' => 'New Role',
            'color' => '#000000',
        ]);

        $newRole = ChurchRole::where('church_id', $this->church->id)
            ->where('name', 'New Role')
            ->first();

        $this->assertEquals($maxOrder + 1, $newRole->sort_order);
    }

    public function test_store_validates_name_required(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'color' => '#ff0000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_store_validates_color_required(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'name' => 'Test Role',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('color');
    }

    public function test_store_validates_name_max_length(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'name' => str_repeat('A', 256),
            'color' => '#ff0000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_store_validates_color_max_length(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/church-roles', [
            'name' => 'Test',
            'color' => '#ff00ff00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('color');
    }

    public function test_volunteer_cannot_create_church_role(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->postJson('/settings/church-roles', [
            'name' => 'Test',
            'color' => '#ff0000',
        ]);

        $response->assertStatus(403);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_church_role(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'volunteer')
            ->first();

        $response = $this->actingAs($this->admin)->putJson("/settings/church-roles/{$role->id}", [
            'name' => 'Updated Name',
            'color' => '#abcdef',
        ]);

        $response->assertStatus(200);
        $role->refresh();
        $this->assertEquals('Updated Name', $role->name);
        $this->assertEquals('#abcdef', $role->color);
        $this->assertEquals('updated-name', $role->slug);
    }

    public function test_cannot_update_role_from_another_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherRole = ChurchRole::where('church_id', $otherChurch->id)
            ->where('slug', 'volunteer')
            ->first();

        $response = $this->actingAs($this->admin)->putJson("/settings/church-roles/{$otherRole->id}", [
            'name' => 'Hacked',
            'color' => '#000000',
        ]);

        $response->assertStatus(404);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_church_role_without_people(): void
    {
        // Create an extra role so we have more than one
        $role = ChurchRole::create([
            'church_id' => $this->church->id,
            'name' => 'Temporary',
            'slug' => 'temporary',
            'color' => '#999999',
            'sort_order' => 99,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/settings/church-roles/{$role->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('church_roles', ['id' => $role->id]);
    }

    public function test_cannot_delete_role_with_people_assigned(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'volunteer')
            ->first();

        Person::factory()->forChurch($this->church)->create([
            'church_role_id' => $role->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/settings/church-roles/{$role->id}");

        $response->assertStatus(400);
        $this->assertDatabaseHas('church_roles', ['id' => $role->id]);
    }

    public function test_cannot_delete_last_role(): void
    {
        // Delete all roles except one
        $roles = ChurchRole::where('church_id', $this->church->id)->get();
        $lastRole = $roles->last();

        // Ensure last role is admin so user retains access
        $lastRole->update(['is_admin_role' => true]);

        // Move admin user to the last role before deleting others
        $this->admin->update(['church_role_id' => $lastRole->id]);
        \Illuminate\Support\Facades\DB::table('church_user')
            ->where('user_id', $this->admin->id)
            ->where('church_id', $this->church->id)
            ->update(['church_role_id' => $lastRole->id]);
        $this->admin->refresh();
        Cache::flush();

        // Remove people/users from roles to allow deletion
        foreach ($roles as $role) {
            if ($role->id !== $lastRole->id) {
                Person::where('church_role_id', $role->id)->update(['church_role_id' => null]);
                User::where('church_role_id', $role->id)->update(['church_role_id' => null]);
                $role->delete();
            }
        }

        $response = $this->actingAs($this->admin)->deleteJson("/settings/church-roles/{$lastRole->id}");

        $response->assertStatus(400);
    }

    // ==================
    // Set Default
    // ==================

    public function test_admin_can_set_default_role(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'volunteer')
            ->first();

        $response = $this->actingAs($this->admin)->postJson("/settings/church-roles/{$role->id}/set-default");

        $response->assertStatus(200);
        $role->refresh();
        $this->assertTrue($role->is_default);

        // All other roles should not be default
        $otherDefaults = ChurchRole::where('church_id', $this->church->id)
            ->where('id', '!=', $role->id)
            ->where('is_default', true)
            ->count();
        $this->assertEquals(0, $otherDefaults);
    }

    // ==================
    // Toggle Admin
    // ==================

    public function test_admin_can_toggle_admin_on_role(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'volunteer')
            ->first();

        $this->assertFalse($role->is_admin_role);

        $response = $this->actingAs($this->admin)->postJson("/settings/church-roles/{$role->id}/toggle-admin");

        $response->assertStatus(200);
        $role->refresh();
        $this->assertTrue($role->is_admin_role);
    }

    public function test_cannot_remove_admin_from_last_admin_role(): void
    {
        // The admin() factory creates a separate 'admin' role, so there may be two admin roles.
        // Consolidate to ensure only one admin role exists for this test.
        $adminRoles = ChurchRole::where('church_id', $this->church->id)
            ->where('is_admin_role', true)
            ->get();

        // Keep the one the admin user is assigned to, demote others
        $adminRole = $adminRoles->firstWhere('id', $this->admin->church_role_id)
            ?? $adminRoles->first();
        foreach ($adminRoles as $role) {
            if ($role->id !== $adminRole->id) {
                $role->update(['is_admin_role' => false]);
            }
        }
        Cache::flush();

        // Ensure it's the only admin role
        $adminCount = ChurchRole::where('church_id', $this->church->id)
            ->where('is_admin_role', true)
            ->count();
        $this->assertEquals(1, $adminCount);

        $response = $this->actingAs($this->admin)->postJson("/settings/church-roles/{$adminRole->id}/toggle-admin");

        $response->assertStatus(400);
        $adminRole->refresh();
        $this->assertTrue($adminRole->is_admin_role);
    }

    public function test_can_remove_admin_when_multiple_admin_roles_exist(): void
    {
        // Make volunteer role also admin
        $volunteerRole = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'volunteer')
            ->first();
        $volunteerRole->update(['is_admin_role' => true]);

        $adminRole = ChurchRole::where('church_id', $this->church->id)
            ->where('is_admin_role', true)
            ->where('id', '!=', $volunteerRole->id)
            ->first();

        $response = $this->actingAs($this->admin)->postJson("/settings/church-roles/{$adminRole->id}/toggle-admin");

        $response->assertStatus(200);
        $adminRole->refresh();
        $this->assertFalse($adminRole->is_admin_role);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_multi_tenancy_roles_are_scoped_to_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherRole = ChurchRole::where('church_id', $otherChurch->id)->first();

        // Cannot update other church's role
        $response = $this->actingAs($this->admin)->putJson("/settings/church-roles/{$otherRole->id}", [
            'name' => 'Hacked',
            'color' => '#000',
        ]);
        $response->assertStatus(404);

        // Cannot delete other church's role
        $response = $this->actingAs($this->admin)->deleteJson("/settings/church-roles/{$otherRole->id}");
        $response->assertStatus(404);

        // Cannot set-default on other church's role
        $response = $this->actingAs($this->admin)->postJson("/settings/church-roles/{$otherRole->id}/set-default");
        $response->assertStatus(404);

        // Cannot toggle-admin on other church's role
        $response = $this->actingAs($this->admin)->postJson("/settings/church-roles/{$otherRole->id}/toggle-admin");
        $response->assertStatus(404);
    }
}
