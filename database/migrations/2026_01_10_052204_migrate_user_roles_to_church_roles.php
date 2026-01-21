<?php

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Default permissions (previously from RolePermission model)
    private const DEFAULT_PERMISSIONS = [
        'leader' => [
            'dashboard' => ['view'],
            'people' => ['view', 'create', 'edit'],
            'groups' => ['view', 'create', 'edit'],
            'ministries' => ['view', 'edit'],
            'events' => ['view', 'create', 'edit'],
            'finances' => [],
            'reports' => ['view'],
            'resources' => ['view', 'create'],
            'boards' => ['view', 'create', 'edit'],
            'announcements' => ['view', 'create'],
            'website' => [],
            'settings' => [],
        ],
        'volunteer' => [
            'dashboard' => ['view'],
            'people' => ['view'],
            'groups' => ['view'],
            'ministries' => ['view'],
            'events' => ['view'],
            'finances' => [],
            'reports' => [],
            'resources' => ['view'],
            'boards' => ['view'],
            'announcements' => ['view'],
            'website' => [],
            'settings' => [],
        ],
    ];

    public function up(): void
    {
        // For each church, create system roles and migrate users
        $churches = Church::all();

        foreach ($churches as $church) {
            // Create or update Administrator role
            $adminRole = ChurchRole::updateOrCreate(
                ['church_id' => $church->id, 'slug' => 'administrator'],
                [
                    'name' => 'Адміністратор',
                    'color' => '#dc2626',
                    'sort_order' => 0,
                    'is_default' => false,
                    'is_admin_role' => true,
                ]
            );

            // Create or update Leader role
            $leaderRole = ChurchRole::updateOrCreate(
                ['church_id' => $church->id, 'slug' => 'leader'],
                [
                    'name' => 'Лідер',
                    'color' => '#8b5cf6',
                    'sort_order' => 1,
                    'is_default' => false,
                    'is_admin_role' => false,
                ]
            );
            $this->setPermissionsForRole($leaderRole, self::DEFAULT_PERMISSIONS['leader']);

            // Create or update Volunteer role
            $volunteerRole = ChurchRole::updateOrCreate(
                ['church_id' => $church->id, 'slug' => 'volunteer'],
                [
                    'name' => 'Служитель',
                    'color' => '#3b82f6',
                    'sort_order' => 2,
                    'is_default' => false,
                    'is_admin_role' => false,
                ]
            );
            $this->setPermissionsForRole($volunteerRole, self::DEFAULT_PERMISSIONS['volunteer']);

            // Migrate existing users
            User::where('church_id', $church->id)
                ->where('role', 'admin')
                ->whereNull('church_role_id')
                ->update(['church_role_id' => $adminRole->id]);

            User::where('church_id', $church->id)
                ->where('role', 'leader')
                ->whereNull('church_role_id')
                ->update(['church_role_id' => $leaderRole->id]);

            User::where('church_id', $church->id)
                ->where('role', 'volunteer')
                ->whereNull('church_role_id')
                ->update(['church_role_id' => $volunteerRole->id]);
        }
    }

    private function setPermissionsForRole(ChurchRole $role, array $permissions): void
    {
        foreach ($permissions as $module => $actions) {
            ChurchRolePermission::updateOrCreate(
                ['church_role_id' => $role->id, 'module' => $module],
                ['actions' => $actions]
            );
        }
    }

    public function down(): void
    {
        // Clear church_role_id from users
        User::whereNotNull('church_role_id')->update(['church_role_id' => null]);

        // Delete system roles created by this migration
        ChurchRole::whereIn('slug', ['administrator', 'leader', 'volunteer'])->delete();
    }
};
