<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChurchRole extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'name',
        'slug',
        'color',
        'sort_order',
        'is_default',
        'is_admin_role',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_admin_role' => 'boolean',
    ];

    public const DEFAULT_ROLES = [
        'administrator' => ['name' => 'Адміністратор', 'slug' => 'administrator', 'color' => '#dc2626', 'sort_order' => 0, 'is_admin_role' => true, 'is_default' => false],
        'leader' => ['name' => 'Лідер', 'slug' => 'leader', 'color' => '#8b5cf6', 'sort_order' => 1, 'is_admin_role' => false, 'is_default' => false],
        'volunteer' => ['name' => 'Служитель', 'slug' => 'volunteer', 'color' => '#3b82f6', 'sort_order' => 2, 'is_admin_role' => false, 'is_default' => false],
    ];

    public const DEFAULT_PERMISSIONS = [
        'leader' => [
            'dashboard' => ['view'],
            'people' => ['view', 'create', 'edit'],
            'groups' => ['view', 'create', 'edit'],
            'ministries' => ['view', 'edit'],
            'events' => ['view', 'create', 'edit'],
            'reports' => ['view'],
            'resources' => ['view', 'create'],
            'boards' => ['view', 'create', 'edit'],
            'announcements' => ['view', 'create'],
        ],
        'volunteer' => [
            'dashboard' => ['view'],
            'people' => ['view'],
            'groups' => ['view'],
            'ministries' => ['view'],
            'events' => ['view'],
            'resources' => ['view'],
            'boards' => ['view'],
            'announcements' => ['view'],
        ],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ChurchRolePermission::class);
    }

    /**
     * Check if this role has permission for module and action
     */
    public function hasPermission(string $module, string $action): bool
    {
        // Admin role always has full access
        if ($this->is_admin_role) {
            return true;
        }

        $cacheKey = "church_role_permission:{$this->id}:{$module}";

        $actions = Cache::remember($cacheKey, 3600, function () use ($module) {
            $permission = $this->permissions()->where('module', $module)->first();
            return $permission?->actions ?? [];
        });

        return in_array($action, $actions);
    }

    /**
     * Get all permissions for this role
     */
    public function getAllPermissions(): array
    {
        if ($this->is_admin_role) {
            // Return only valid actions per module for admin role
            $result = [];
            foreach (ChurchRolePermission::MODULES as $module => $config) {
                $result[$module] = $config['actions'];
            }
            return $result;
        }

        $permissions = [];
        foreach ($this->permissions as $permission) {
            $permissions[$permission->module] = $permission->actions ?? [];
        }

        // Fill missing modules with empty arrays
        foreach (array_keys(ChurchRolePermission::MODULES) as $module) {
            if (!isset($permissions[$module])) {
                $permissions[$module] = [];
            }
        }

        return $permissions;
    }

    /**
     * Set permissions for this role
     */
    public function setPermissions(array $permissions): void
    {
        foreach ($permissions as $module => $actions) {
            // Skip invalid modules
            if (!array_key_exists($module, ChurchRolePermission::MODULES)) {
                continue;
            }

            // Filter to only valid actions for this module
            $allowedActions = ChurchRolePermission::MODULES[$module]['actions'];
            $validActions = array_values(array_intersect((array) $actions, $allowedActions));

            ChurchRolePermission::updateOrCreate(
                ['church_role_id' => $this->id, 'module' => $module],
                ['actions' => $validActions]
            );
        }

        $this->clearPermissionCache();
    }

    /**
     * Clear permission cache for this role
     */
    public function clearPermissionCache(): void
    {
        foreach (array_keys(ChurchRolePermission::MODULES) as $module) {
            Cache::forget("church_role_permission:{$this->id}:{$module}");
        }
    }

    public static function createDefaultsForChurch(int $churchId): self
    {
        $adminRole = null;

        foreach (self::DEFAULT_ROLES as $key => $roleData) {
            $role = self::firstOrCreate(
                ['church_id' => $churchId, 'slug' => $roleData['slug']],
                $roleData
            );

            if ($role->is_admin_role) {
                $adminRole = $role;
            }

            if (isset(self::DEFAULT_PERMISSIONS[$key])) {
                foreach (self::DEFAULT_PERMISSIONS[$key] as $module => $actions) {
                    ChurchRolePermission::firstOrCreate(
                        ['church_role_id' => $role->id, 'module' => $module],
                        ['actions' => $actions]
                    );
                }
            }
        }

        return $adminRole;
    }
}
