<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PermissionService $service;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PermissionService();
        $this->church = Church::factory()->create();
    }

    // ==================
    // can()
    // ==================

    public function test_admin_can_access_any_module(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');
        $this->actingAs($admin);

        $this->assertTrue($this->service->can('people', 'view'));
        $this->assertTrue($this->service->can('finances', 'delete'));
        $this->assertTrue($this->service->can('settings', 'edit'));
    }

    public function test_custom_role_with_permission_can_access(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'custom']);
        $role->setPermissions(['people' => ['view', 'create']]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $this->actingAs($user);

        $this->assertTrue($this->service->can('people', 'view'));
        $this->assertTrue($this->service->can('people', 'create'));
        $this->assertFalse($this->service->can('people', 'delete'));
    }

    public function test_custom_role_without_permission_cannot_access(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'limited']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $this->actingAs($user);

        $this->assertFalse($this->service->can('finances', 'view'));
    }

    public function test_guest_user_returns_false(): void
    {
        // No user acting as
        $this->assertFalse($this->service->can('people', 'view'));
    }

    // ==================
    // Shortcut Methods
    // ==================

    public function test_can_view(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');
        $this->actingAs($admin);

        $this->assertTrue($this->service->canView('people'));
    }

    public function test_can_create(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');
        $this->actingAs($admin);

        $this->assertTrue($this->service->canCreate('people'));
    }

    public function test_can_edit(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');
        $this->actingAs($admin);

        $this->assertTrue($this->service->canEdit('people'));
    }

    public function test_can_delete(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');
        $this->actingAs($admin);

        $this->assertTrue($this->service->canDelete('people'));
    }

    // ==================
    // Super Admin
    // ==================

    public function test_super_admin_can_access_everything(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'church_id' => $this->church->id,
        ]);
        $this->actingAs($superAdmin);

        $this->assertTrue($this->service->can('settings', 'edit'));
        $this->assertTrue($this->service->can('finances', 'delete'));
    }

    // ==================
    // getAccessibleModules
    // ==================

    public function test_get_accessible_modules_for_admin(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');
        $this->actingAs($admin);

        $modules = $this->service->getAccessibleModules();

        $this->assertContains('people', $modules);
        $this->assertContains('finances', $modules);
        $this->assertContains('settings', $modules);
    }

    public function test_get_accessible_modules_for_limited_role(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'viewer']);
        $role->setPermissions([
            'people' => ['view'],
            'events' => ['view'],
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $this->actingAs($user);

        $modules = $this->service->getAccessibleModules();

        $this->assertContains('people', $modules);
        $this->assertContains('events', $modules);
        $this->assertNotContains('finances', $modules);
    }

    public function test_get_accessible_modules_for_guest(): void
    {
        $modules = $this->service->getAccessibleModules();

        $this->assertEmpty($modules);
    }

    // ==================
    // userCan
    // ==================

    public function test_user_can_checks_specific_user(): void
    {
        $admin = $this->createUserWithRole($this->church, 'admin');

        $this->assertTrue($this->service->userCan($admin, 'people', 'view'));
    }
}
