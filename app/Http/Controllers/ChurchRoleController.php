<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChurchRoleController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $roles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->withCount('people')
            ->get();

        // Prepare data for JSON (avoid arrow functions in blade)
        $rolesJson = $roles->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'color' => $r->color,
                'is_admin_role' => $r->is_admin_role ?? false,
                'is_default' => $r->is_default ?? false,
                'people_count' => $r->people_count ?? 0,
            ];
        });

        return view('settings.church-roles.index', compact('roles', 'rolesJson'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $church = $this->getCurrentChurch();

        // Get max sort order
        $maxOrder = ChurchRole::where('church_id', $church->id)->max('sort_order') ?? 0;

        $role = ChurchRole::create([
            'church_id' => $church->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => $validated['color'],
            'sort_order' => $maxOrder + 1,
        ]);

        return $this->successResponse($request, 'Роль додано', null, [], [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'color' => $role->color,
                'is_admin_role' => $role->is_admin_role ?? false,
                'is_default' => $role->is_default ?? false,
                'people_count' => 0,
            ],
        ]);
    }

    public function update(Request $request, ChurchRole $churchRole)
    {
        $this->authorizeChurch($churchRole);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $churchRole->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => $validated['color'],
        ]);

        return $this->successResponse($request, 'Роль оновлено');
    }

    public function destroy(Request $request, ChurchRole $churchRole)
    {
        $this->authorizeChurch($churchRole);

        // Don't allow deleting if it's the only role or has people
        $church = $this->getCurrentChurch();
        $rolesCount = ChurchRole::where('church_id', $church->id)->count();

        if ($rolesCount <= 1) {
            return $this->errorResponse($request, 'Не можна видалити останню роль', 400);
        }

        if ($churchRole->people()->count() > 0) {
            return $this->errorResponse($request, 'Роль використовується. Спочатку змініть роль у людей.', 400);
        }

        if ($churchRole->users()->count() > 0) {
            return $this->errorResponse($request, 'Роль має користувачів з доступом. Спочатку змініть їх роль.', 400);
        }

        $churchRole->delete();

        return $this->successResponse($request, 'Роль видалено');
    }

    public function toggleAdmin(Request $request, ChurchRole $churchRole)
    {
        $this->authorizeChurch($churchRole);

        $church = $this->getCurrentChurch();

        if ($churchRole->is_admin_role) {
            // Check if this is the only admin role
            $adminRolesCount = ChurchRole::where('church_id', $church->id)
                ->where('is_admin_role', true)
                ->count();

            if ($adminRolesCount <= 1) {
                return $this->errorResponse($request, 'Потрібна хоча б одна роль з повним доступом', 400);
            }

            $churchRole->update(['is_admin_role' => false]);
        } else {
            $churchRole->update(['is_admin_role' => true]);
        }

        return $this->successResponse($request, 'Оновлено', null, [], [
            'is_admin_role' => $churchRole->is_admin_role,
        ]);
    }

    public function setDefault(Request $request, ChurchRole $churchRole)
    {
        $this->authorizeChurch($churchRole);

        $church = $this->getCurrentChurch();

        // Remove default from all other roles
        ChurchRole::where('church_id', $church->id)->update(['is_default' => false]);

        // Set this one as default
        $churchRole->update(['is_default' => true]);

        $churchRole->logCustomAction('set_as_default', 'Set as default church role');

        return $this->successResponse($request, 'Роль за замовчуванням оновлено');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:church_roles,id',
        ]);

        $church = $this->getCurrentChurch();

        foreach ($validated['order'] as $index => $id) {
            ChurchRole::where('id', $id)
                ->where('church_id', $church->id)
                ->update(['sort_order' => $index + 1]);
        }

        return $this->successResponse($request, 'Порядок оновлено');
    }

    public function resetToDefaults(Request $request)
    {
        $church = $this->getCurrentChurch();

        // Check if any roles have people or users assigned
        $usedRoles = ChurchRole::where('church_id', $church->id)
            ->where(fn ($q) => $q->whereHas('people')->orWhereHas('users'))
            ->count();

        if ($usedRoles > 0) {
            return $this->errorResponse($request, 'Неможливо скинути - деякі ролі використовуються людьми', 400);
        }

        // Delete all current roles
        ChurchRole::where('church_id', $church->id)->delete();

        // Create defaults
        ChurchRole::createDefaultsForChurch($church->id);

        // Log reset action
        $this->logAuditAction('settings_updated', 'Church', $church->id, $church->name, [
            'action' => 'reset_church_roles_to_defaults',
        ]);

        return $this->successResponse($request, 'Ролі скинуто до стандартних');
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
