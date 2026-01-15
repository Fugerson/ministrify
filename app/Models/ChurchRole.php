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

    // Default roles to seed for new churches (only admin, others created by admin)
    public const DEFAULT_ROLES = [
        ['name' => 'Адміністратор', 'slug' => 'admin', 'color' => '#dc2626', 'sort_order' => 0, 'is_admin_role' => true, 'is_default' => true],
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
            // Return all permissions for admin role
            $result = [];
            foreach (array_keys(ChurchRolePermission::MODULES) as $module) {
                $result[$module] = array_keys(ChurchRolePermission::ACTIONS);
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
            ChurchRolePermission::updateOrCreate(
                ['church_role_id' => $this->id, 'module' => $module],
                ['actions' => $actions]
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

    public static function createDefaultsForChurch(int $churchId): void
    {
        foreach (self::DEFAULT_ROLES as $role) {
            self::create(array_merge($role, ['church_id' => $churchId]));
        }
    }
}
