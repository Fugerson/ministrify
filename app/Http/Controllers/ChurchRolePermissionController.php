<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use Illuminate\Http\Request;

class ChurchRolePermissionController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $roles = ChurchRole::where('church_id', $church->id)
            ->with('permissions')
            ->orderBy('sort_order')
            ->get();

        return view('settings.role-permissions', [
            'roles' => $roles,
            'modules' => ChurchRolePermission::MODULES,
            'actions' => ChurchRolePermission::ACTIONS,
        ]);
    }

    public function update(Request $request, ChurchRole $churchRole)
    {
        $this->authorizeChurch($churchRole);

        // Cannot modify admin role permissions
        if ($churchRole->is_admin_role) {
            return response()->json([
                'message' => 'Неможливо змінити права адміністратора'
            ], 400);
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'string',
        ]);

        $churchRole->setPermissions($validated['permissions']);

        return response()->json(['success' => true]);
    }

    public function getPermissions(ChurchRole $churchRole)
    {
        $this->authorizeChurch($churchRole);

        return response()->json([
            'permissions' => $churchRole->getAllPermissions(),
            'is_admin_role' => $churchRole->is_admin_role,
        ]);
    }
}
