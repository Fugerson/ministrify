<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Redirect to church roles page (permissions are now managed there)
     */
    public function index()
    {
        return redirect()->route('settings.church-roles.index');
    }

    /**
     * Update permissions for a role
     */
    public function update(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys(RolePermission::ROLES)),
            'permissions' => 'required|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'string|in:' . implode(',', array_keys(RolePermission::ACTIONS)),
        ]);

        $role = $validated['role'];
        $permissions = $validated['permissions'];

        // Update permissions for each module
        foreach (array_keys(RolePermission::MODULES) as $module) {
            $modulePermissions = $permissions[$module] ?? [];
            RolePermission::setPermissions($church->id, $role, $module, $modulePermissions);
        }

        // Clear cache
        RolePermission::clearCache($church->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Права доступу оновлено');
    }

    /**
     * Reset permissions to defaults for a role
     */
    public function reset(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys(RolePermission::ROLES)),
        ]);

        $role = $validated['role'];
        $defaults = RolePermission::DEFAULT_PERMISSIONS[$role] ?? [];

        foreach ($defaults as $module => $permissions) {
            RolePermission::setPermissions($church->id, $role, $module, $permissions);
        }

        // Clear cache
        RolePermission::clearCache($church->id);

        return back()->with('success', 'Права доступу скинуто до стандартних');
    }

    /**
     * Get permissions for API
     */
    public function get(string $role)
    {
        $church = $this->getCurrentChurch();

        if (!array_key_exists($role, RolePermission::ROLES)) {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        $permissions = RolePermission::getAllForRole($church->id, $role);

        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }
}
