<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChurchRoleTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    public function test_admin_role_has_all_permissions(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();

        $this->assertTrue($role->hasPermission('finances', 'delete'));
        $this->assertTrue($role->hasPermission('people', 'create'));
        $this->assertTrue($role->hasPermission('settings', 'edit'));
    }

    public function test_custom_role_checks_configured_permissions(): void
    {
        $role = ChurchRole::where('church_id', $this->church->id)->where('slug', 'volunteer')->first();
        ChurchRolePermission::updateOrCreate(
            ['church_role_id' => $role->id, 'module' => 'people'],
            ['actions' => ['view', 'create']]
        );
        $role->clearPermissionCache();

        $this->assertTrue($role->hasPermission('people', 'view'));
        $this->assertTrue($role->hasPermission('people', 'create'));
        $this->assertFalse($role->hasPermission('people', 'delete'));
        $this->assertFalse($role->hasPermission('finances', 'view'));
    }

    public function test_get_all_permissions_for_admin(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();

        $permissions = $role->getAllPermissions();

        foreach (array_keys(ChurchRolePermission::MODULES) as $module) {
            $this->assertArrayHasKey($module, $permissions);
            $this->assertContains('view', $permissions[$module]);
        }
    }

    public function test_get_all_permissions_for_custom_role(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        ChurchRolePermission::create([
            'church_role_id' => $role->id,
            'module' => 'people',
            'actions' => ['view'],
        ]);

        $permissions = $role->getAllPermissions();

        $this->assertEquals(['view'], $permissions['people']);
        $this->assertEquals([], $permissions['finances']);
    }

    public function test_set_permissions_creates_records(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();

        $role->setPermissions([
            'people' => ['view', 'create'],
            'finances' => ['view'],
        ]);

        $this->assertDatabaseHas('church_role_permissions', [
            'church_role_id' => $role->id,
            'module' => 'people',
        ]);
        $this->assertDatabaseHas('church_role_permissions', [
            'church_role_id' => $role->id,
            'module' => 'finances',
        ]);
    }

    public function test_set_permissions_updates_existing(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();

        $role->setPermissions(['people' => ['view']]);
        $role->setPermissions(['people' => ['view', 'create', 'edit']]);

        $permission = ChurchRolePermission::where('church_role_id', $role->id)
            ->where('module', 'people')
            ->first();

        $this->assertEquals(['view', 'create', 'edit'], $permission->actions);
    }

    public function test_clear_permission_cache(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();

        // Prime the cache
        Cache::put("church_role_permission:{$role->id}:people", ['view'], 3600);

        $role->clearPermissionCache();

        $this->assertNull(Cache::get("church_role_permission:{$role->id}:people"));
    }

    public function test_create_defaults_for_church(): void
    {
        $newChurch = Church::factory()->create();

        // Church::created event auto-calls createDefaultsForChurch
        $adminRole = ChurchRole::where('church_id', $newChurch->id)
            ->where('is_admin_role', true)
            ->first();

        $this->assertNotNull($adminRole);
        $this->assertTrue($adminRole->is_admin_role);
        $this->assertEquals(3, ChurchRole::where('church_id', $newChurch->id)->count());
    }

    public function test_slug_auto_generation(): void
    {
        $role = ChurchRole::create([
            'church_id' => $this->church->id,
            'name' => 'Новий Координатор',
        ]);

        $this->assertNotEmpty($role->slug);
        // Transliteration varies by library — just verify it's a non-empty slug
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $role->slug);
    }

    public function test_slug_not_overwritten_if_provided(): void
    {
        $role = ChurchRole::create([
            'church_id' => $this->church->id,
            'name' => 'Custom Role',
            'slug' => 'my-custom-slug',
        ]);

        $this->assertEquals('my-custom-slug', $role->slug);
    }
}
