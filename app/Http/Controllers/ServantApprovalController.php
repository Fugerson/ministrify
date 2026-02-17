<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChurchRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServantApprovalController extends Controller
{
    /**
     * List pending role approvals for current church
     */
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Тільки адміністратор може управляти одобреннями.');
        }

        $church = $this->getCurrentChurch();

        $query = User::where('church_id', $church->id)
            ->where(function ($q) {
                $q->where('servant_approval_status', 'pending')
                  ->orWhereHas('churches', function ($q2) {
                      $q2->where('role_approval_status', 'pending');
                  });
            })
            ->with(['person', 'requestedChurchRole', 'churchRole'])
            ->orderBy('created_at', 'desc');

        $pendingUsers = $query->get();

        // Group by approval type
        $servantPending = $pendingUsers->filter(fn($u) => $u->servant_approval_status === 'pending')->values();
        $churchRolePending = $pendingUsers->filter(fn($u) =>
            $u->churches()->wherePivot('role_approval_status', 'pending')->exists()
        )->values();

        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->get();

        return view('settings.servant-approvals.index', compact(
            'servantPending',
            'churchRolePending',
            'churchRoles',
            'church'
        ));
    }

    /**
     * Approve a user's servant/role request
     */
    public function approve(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $church = $this->getCurrentChurch();
        if ($user->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'church_role_id' => 'nullable|exists:church_roles,id',
        ]);

        $roleId = $validated['church_role_id'] ?? $user->requested_church_role_id;

        if (!$roleId) {
            return response()->json(['message' => 'Роль не вибрана'], 400);
        }

        // Verify role belongs to this church
        $role = ChurchRole::where('id', $roleId)->where('church_id', $church->id)->first();
        if (!$role) {
            return response()->json(['message' => 'Невірна роль'], 400);
        }

        // Update user
        $oldRoleId = $user->church_role_id;
        $user->update([
            'church_role_id' => $roleId,
            'servant_approval_status' => 'approved',
            'servant_approved_at' => now(),
        ]);

        // Update pivot records
        \Illuminate\Support\Facades\DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->update([
                'church_role_id' => $roleId,
                'role_approval_status' => 'approved',
                'updated_at' => now(),
            ]);

        // Log approval
        Log::channel('security')->info('Servant/role approved', [
            'user_id' => $user->id,
            'email' => $user->email,
            'church_id' => $church->id,
            'role_id' => $roleId,
            'role_name' => $role->name,
            'approved_by' => auth()->id(),
        ]);

        $this->logAuditAction('servant_approved', 'User', $user->id, $user->name, [
            'role' => $role->name,
            'approved_by' => auth()->user()->name,
        ]);

        // Send notification
        try {
            $user->notify(new \App\Notifications\ServantsApproved($role->name, $church->name));
        } catch (\Exception $e) {
            Log::error('Failed to send approval notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Користувач {$user->name} одобрений як {$role->name}",
        ]);
    }

    /**
     * Reject a user's servant/role request
     */
    public function reject(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Недостатньо прав'], 403);
        }

        $church = $this->getCurrentChurch();
        if ($user->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $requestedRole = $user->requestedChurchRole?->name ?? 'Unknown';

        // Update user
        $user->update([
            'servant_approval_status' => null,
            'requested_church_role_id' => null,
        ]);

        // Clear pivot role_approval_status
        \Illuminate\Support\Facades\DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->update([
                'church_role_id' => null,
                'role_approval_status' => null,
                'updated_at' => now(),
            ]);

        // Log rejection
        Log::channel('security')->info('Servant/role rejected', [
            'user_id' => $user->id,
            'email' => $user->email,
            'church_id' => $church->id,
            'requested_role' => $requestedRole,
            'reason' => $validated['reason'] ?? 'No reason provided',
            'rejected_by' => auth()->id(),
        ]);

        $this->logAuditAction('servant_rejected', 'User', $user->id, $user->name, [
            'requested_role' => $requestedRole,
            'reason' => $validated['reason'] ?? 'Без причини',
            'rejected_by' => auth()->user()->name,
        ]);

        // Send notification
        try {
            $user->notify(new \App\Notifications\ServantsRejected($requestedRole, $validated['reason'] ?? null));
        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Заявку {$user->name} відхилено",
        ]);
    }
}
