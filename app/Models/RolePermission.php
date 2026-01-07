<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class RolePermission extends Model
{
    protected $fillable = [
        'church_id',
        'role',
        'module',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Available roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_LEADER = 'leader';
    public const ROLE_VOLUNTEER = 'volunteer';

    public const ROLES = [
        self::ROLE_ADMIN => 'Адміністратор',
        self::ROLE_LEADER => 'Лідер',
        self::ROLE_VOLUNTEER => 'Волонтер',
    ];

    // Available modules
    public const MODULES = [
        'dashboard' => ['label' => 'Головна', 'icon' => 'home'],
        'people' => ['label' => 'Люди', 'icon' => 'users'],
        'groups' => ['label' => 'Домашні групи', 'icon' => 'user-group'],
        'ministries' => ['label' => 'Служіння', 'icon' => 'heart'],
        'events' => ['label' => 'Розклад', 'icon' => 'calendar'],
        'finances' => ['label' => 'Фінанси', 'icon' => 'currency-dollar'],
        'reports' => ['label' => 'Звіти', 'icon' => 'chart-bar'],
        'resources' => ['label' => 'Ресурси', 'icon' => 'folder'],
        'boards' => ['label' => 'Дошки завдань', 'icon' => 'view-boards'],
        'announcements' => ['label' => 'Комунікації', 'icon' => 'speakerphone'],
        'website' => ['label' => 'Веб-сайт', 'icon' => 'globe'],
        'settings' => ['label' => 'Налаштування', 'icon' => 'cog'],
    ];

    // Available actions
    public const ACTIONS = [
        'view' => 'Переглядати',
        'create' => 'Створювати',
        'edit' => 'Редагувати',
        'delete' => 'Видаляти',
    ];

    // Default permissions for each role
    public const DEFAULT_PERMISSIONS = [
        self::ROLE_ADMIN => [
            'dashboard' => ['view'],
            'people' => ['view', 'create', 'edit', 'delete'],
            'groups' => ['view', 'create', 'edit', 'delete'],
            'ministries' => ['view', 'create', 'edit', 'delete'],
            'events' => ['view', 'create', 'edit', 'delete'],
            'finances' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view'],
            'resources' => ['view', 'create', 'edit', 'delete'],
            'boards' => ['view', 'create', 'edit', 'delete'],
            'announcements' => ['view', 'create', 'edit', 'delete'],
            'website' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
        ],
        self::ROLE_LEADER => [
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
        self::ROLE_VOLUNTEER => [
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

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Check if role has permission for module and action
     */
    public static function hasPermission(int $churchId, string $role, string $module, string $action): bool
    {
        // Super admin has all permissions
        if ($role === 'super_admin') {
            return true;
        }

        // Admin ALWAYS has full access - cannot be restricted
        if ($role === self::ROLE_ADMIN) {
            return true;
        }

        $permissions = self::getPermissionsForRole($churchId, $role, $module);
        return in_array($action, $permissions);
    }

    /**
     * Get permissions for a role and module
     */
    public static function getPermissionsForRole(int $churchId, string $role, string $module): array
    {
        $cacheKey = "permissions:{$churchId}:{$role}:{$module}";

        return Cache::remember($cacheKey, 3600, function () use ($churchId, $role, $module) {
            $record = self::where('church_id', $churchId)
                ->where('role', $role)
                ->where('module', $module)
                ->first();

            if ($record) {
                return $record->permissions ?? [];
            }

            // Return default permissions if not customized
            return self::DEFAULT_PERMISSIONS[$role][$module] ?? [];
        });
    }

    /**
     * Get all permissions for a role in a church
     */
    public static function getAllForRole(int $churchId, string $role): array
    {
        $permissions = [];

        foreach (array_keys(self::MODULES) as $module) {
            $permissions[$module] = self::getPermissionsForRole($churchId, $role, $module);
        }

        return $permissions;
    }

    /**
     * Set permissions for a role and module
     */
    public static function setPermissions(int $churchId, string $role, string $module, array $permissions): void
    {
        self::updateOrCreate(
            [
                'church_id' => $churchId,
                'role' => $role,
                'module' => $module,
            ],
            [
                'permissions' => $permissions,
            ]
        );

        // Clear cache
        Cache::forget("permissions:{$churchId}:{$role}:{$module}");
    }

    /**
     * Initialize default permissions for a church
     */
    public static function initializeDefaults(int $churchId): void
    {
        foreach (self::DEFAULT_PERMISSIONS as $role => $modules) {
            foreach ($modules as $module => $permissions) {
                self::firstOrCreate(
                    [
                        'church_id' => $churchId,
                        'role' => $role,
                        'module' => $module,
                    ],
                    [
                        'permissions' => $permissions,
                    ]
                );
            }
        }
    }

    /**
     * Clear all permission cache for a church
     */
    public static function clearCache(int $churchId): void
    {
        foreach (array_keys(self::ROLES) as $role) {
            foreach (array_keys(self::MODULES) as $module) {
                Cache::forget("permissions:{$churchId}:{$role}:{$module}");
            }
        }
    }
}
