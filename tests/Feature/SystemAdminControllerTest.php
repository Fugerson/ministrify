<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $this->superAdmin = User::factory()->superAdmin()->admin()->create([
            'church_id' => $this->church->id,
        ]);
        if (! DB::table('church_user')->where('user_id', $this->superAdmin->id)->where('church_id', $this->church->id)->exists()) {
            $this->superAdmin->churches()->attach($this->church->id, [
                'church_role_id' => $this->superAdmin->church_role_id,
            ]);
        }
        $this->superAdmin->refresh();
    }

    // ==================
    // Access control
    // ==================

    public function test_guest_cannot_access_system_admin(): void
    {
        $response = $this->get('/system-admin/churches');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_gets_403_on_system_admin(): void
    {
        [$church, $admin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($admin)->get('/system-admin/churches');

        $response->assertStatus(403);
    }

    public function test_volunteer_gets_403_on_system_admin(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/system-admin/churches');

        $response->assertStatus(403);
    }

    // ==================
    // Index (MySQL-specific — skip on SQLite)
    // ==================

    public function test_super_admin_can_view_dashboard(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('System admin dashboard uses MySQL-specific queries (COALESCE in SUM)');
        }

        $response = $this->actingAs($this->superAdmin)->get('/system-admin');

        $response->assertStatus(200);
    }

    // ==================
    // Churches
    // ==================

    public function test_super_admin_can_view_churches_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/system-admin/churches');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_church_details(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Church details page uses MySQL-specific queries');
        }

        $response = $this->actingAs($this->superAdmin)->get("/system-admin/churches/{$this->church->id}");

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_church(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/system-admin/churches', [
            'name' => 'New Test Church',
            'city' => 'Kyiv',
            'address' => 'Test Address 123',
            'admin_name' => 'New Admin',
            'admin_email' => 'newadmin-' . uniqid() . '@example.com',
            'admin_password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('churches', ['name' => 'New Test Church']);
    }

    public function test_create_church_requires_valid_data(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/system-admin/churches', [
            'name' => '',
            'city' => '',
            'admin_email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'city', 'admin_name', 'admin_email', 'admin_password']);
    }

    public function test_super_admin_can_delete_church(): void
    {
        $churchToDelete = Church::factory()->create();

        $response = $this->actingAs($this->superAdmin)->delete("/system-admin/churches/{$churchToDelete->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('churches', ['id' => $churchToDelete->id, 'deleted_at' => null]);
    }

    // ==================
    // Users
    // ==================

    public function test_super_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/system-admin/users');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_edit_user(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($this->superAdmin)->get("/system-admin/users/{$otherAdmin->id}/edit");

        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_user(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($this->superAdmin)->put("/system-admin/users/{$otherAdmin->id}", [
            'name' => 'Updated Name',
            'email' => $otherAdmin->email,
            'church_role_id' => $otherAdmin->church_role_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_regular_admin_cannot_access_users_list(): void
    {
        [$church, $admin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($admin)->get('/system-admin/users');

        $response->assertStatus(403);
    }

    // ==================
    // Search
    // ==================

    public function test_churches_list_supports_search(): void
    {
        Church::factory()->create(['name' => 'Unique Church Name XYZ']);

        $response = $this->actingAs($this->superAdmin)->get('/system-admin/churches?search=XYZ');

        $response->assertStatus(200);
        $response->assertSee('Unique Church Name XYZ');
    }

    public function test_users_list_supports_search(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/system-admin/users?search=' . urlencode($this->superAdmin->email));

        $response->assertStatus(200);
    }
}
