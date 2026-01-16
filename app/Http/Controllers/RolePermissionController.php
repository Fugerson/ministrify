<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\User;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Show permissions management page with church roles
     */
    public function index()
    {
        $church = $this->getCurrentChurch();

        // Get all church roles for this church
        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->with('permissions')
            ->orderBy('sort_order')
            ->get();

        // Get permissions for all roles
        $permissions = [];
        foreach ($churchRoles as $role) {
            $permissions[$role->id] = $role->getAllPermissions();
        }

        // Get users with permission overrides for display
        $usersWithOverrides = User::where('church_id', $church->id)
            ->whereNotNull('permission_overrides')
            ->with('person', 'churchRole')
            ->get();

        // Get all users for the dropdown
        $allUsers = User::where('church_id', $church->id)
            ->with('person', 'churchRole')
            ->orderBy('name')
            ->get();

        return view('settings.permissions', [
            'churchRoles' => $churchRoles,
            'modules' => ChurchRolePermission::MODULES,
            'actions' => ChurchRolePermission::ACTIONS,
            'permissions' => $permissions,
            'usersWithOverrides' => $usersWithOverrides,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Update permissions for a church role
     */
    public function update(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'role_id' => 'required|integer|exists:church_roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'array',
        ]);

        $role = ChurchRole::where('id', $validated['role_id'])
            ->where('church_id', $church->id)
            ->first();

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        // Don't allow editing admin role permissions
        if ($role->is_admin_role) {
            return response()->json(['error' => 'Admin role permissions cannot be modified'], 400);
        }

        $role->setPermissions($validated['permissions']);

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
            'role_id' => 'required|integer|exists:church_roles,id',
        ]);

        $role = ChurchRole::where('id', $validated['role_id'])
            ->where('church_id', $church->id)
            ->first();

        if (!$role) {
            return back()->with('error', 'Роль не знайдена');
        }

        // Clear all permissions for this role
        $role->permissions()->delete();
        $role->clearPermissionCache();

        return back()->with('success', 'Права доступу скинуто');
    }

    /**
     * Get user permission overrides for API
     */
    public function getUserOverrides(User $user)
    {
        $church = $this->getCurrentChurch();

        if ($user->church_id !== $church->id) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role_name' => $user->churchRole?->name,
            'overrides' => $user->getPermissionOverrides(),
        ]);
    }

    /**
     * Update user permission overrides
     */
    public function updateUserOverrides(Request $request, User $user)
    {
        $church = $this->getCurrentChurch();

        if ($user->church_id !== $church->id) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'overrides' => 'required|array',
        ]);

        // Filter out empty overrides
        $overrides = array_filter($validated['overrides'], function ($moduleOverrides) {
            return !empty($moduleOverrides['allow']) || !empty($moduleOverrides['deny']);
        });

        $user->permission_overrides = empty($overrides) ? null : $overrides;
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * Clear user permission overrides
     */
    public function clearUserOverrides(User $user)
    {
        $church = $this->getCurrentChurch();

        if ($user->church_id !== $church->id) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->clearPermissionOverrides();

        return response()->json(['success' => true]);
    }
}
