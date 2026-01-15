<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PermissionService
{
    /**
     * Check if current user can perform action on module
     */
    public function can(string $module, string $action): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Super admin can do everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Get church from user
        $churchId = $user->church_id;
        if (!$churchId) {
            return false;
        }

        return RolePermission::hasPermission($churchId, $user->role, $module, $action);
    }

    /**
     * Check if user can view module
     */
    public function canView(string $module): bool
    {
        return $this->can($module, 'view');
    }

    /**
     * Check if user can create in module
     */
    public function canCreate(string $module): bool
    {
        return $this->can($module, 'create');
    }

    /**
     * Check if user can edit in module
     */
    public function canEdit(string $module): bool
    {
        return $this->can($module, 'edit');
    }

    /**
     * Check if user can delete in module
     */
    public function canDelete(string $module): bool
    {
        return $this->can($module, 'delete');
    }

    /**
     * Get all accessible modules for current user
     */
    public function getAccessibleModules(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        if ($user->isSuperAdmin()) {
            return array_keys(RolePermission::MODULES);
        }

        $churchId = $user->church_id;
        if (!$churchId) {
            return [];
        }

        $permissions = RolePermission::getAllForRole($churchId, $user->role);

        return array_keys(array_filter($permissions, fn($perms) => in_array('view', $perms)));
    }

    /**
     * Check permission for specific user
     */
    public function userCan(User $user, string $module, string $action): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $churchId = $user->church_id;
        if (!$churchId) {
            return false;
        }

        return RolePermission::hasPermission($churchId, $user->role, $module, $action);
    }
}
