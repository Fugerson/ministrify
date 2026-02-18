<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Person;
use App\Models\ChurchRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Load available People (without linked user) for manual linking
        $availablePeople = Person::where('church_id', $church->id)
            ->whereNull('user_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        // Find potential matches for each pending user
        $potentialMatches = [];
        $allPending = $servantPending->merge($churchRolePending)->unique('id');
        foreach ($allPending as $user) {
            $matches = $this->findPotentialMatches($user, $availablePeople);
            if ($matches->isNotEmpty()) {
                $potentialMatches[$user->id] = $matches;
            }
        }

        return view('settings.servant-approvals.index', compact(
            'servantPending',
            'churchRolePending',
            'churchRoles',
            'church',
            'availablePeople',
            'potentialMatches'
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
            'link_person_id' => 'nullable|integer|exists:people,id',
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

        // Handle Person linking if requested
        $linkPersonId = $validated['link_person_id'] ?? null;
        if ($linkPersonId) {
            $linkResult = $this->linkUserToPerson($user, $church, $linkPersonId);
            if ($linkResult !== true) {
                return response()->json(['message' => $linkResult], 400);
            }
        }

        // Update user
        $oldRoleId = $user->church_role_id;
        $user->update([
            'church_role_id' => $roleId,
            'servant_approval_status' => 'approved',
            'servant_approved_at' => now(),
        ]);

        // Update pivot records
        DB::table('church_user')
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
            'linked_person_id' => $linkPersonId,
        ]);

        $this->logAuditAction('servant_approved', 'User', $user->id, $user->name, [
            'role' => $role->name,
            'approved_by' => auth()->user()->name,
            'linked_person_id' => $linkPersonId,
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

    /**
     * Link user to an existing Person, removing the auto-created one.
     *
     * @return true|string  True on success, error message string on failure
     */
    private function linkUserToPerson(User $user, $church, int $linkPersonId)
    {
        // Validate the target Person
        $existingPerson = Person::where('id', $linkPersonId)
            ->where('church_id', $church->id)
            ->whereNull('user_id')
            ->first();

        if (!$existingPerson) {
            return 'Обрана людина не знайдена або вже привʼязана до іншого користувача';
        }

        // Find the auto-created Person via pivot
        $pivot = DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();

        $autoCreatedPersonId = $pivot?->person_id;

        DB::transaction(function () use ($user, $church, $existingPerson, $autoCreatedPersonId) {
            // Link existing Person to User
            $existingPerson->update(['user_id' => $user->id]);

            // Update pivot to point to the existing Person
            DB::table('church_user')
                ->where('user_id', $user->id)
                ->where('church_id', $church->id)
                ->update([
                    'person_id' => $existingPerson->id,
                    'updated_at' => now(),
                ]);

            // Delete auto-created Person if it's different and has no important data
            if ($autoCreatedPersonId && $autoCreatedPersonId !== $existingPerson->id) {
                $autoCreatedPerson = Person::find($autoCreatedPersonId);
                if ($autoCreatedPerson) {
                    // Clear user_id so it doesn't conflict
                    $autoCreatedPerson->update(['user_id' => null]);
                    $autoCreatedPerson->forceDelete();
                }
            }
        });

        Log::channel('security')->info('User linked to existing Person', [
            'user_id' => $user->id,
            'linked_person_id' => $existingPerson->id,
            'deleted_auto_person_id' => $autoCreatedPersonId,
            'church_id' => $church->id,
            'linked_by' => auth()->id(),
        ]);

        return true;
    }

    /**
     * Find potential Person matches for a pending user.
     */
    private function findPotentialMatches(User $user, $availablePeople): \Illuminate\Support\Collection
    {
        $nameParts = explode(' ', mb_strtolower(trim($user->name)), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        $email = mb_strtolower($user->email ?? '');
        $phone = preg_replace('/\D/', '', $user->person?->phone ?? '');

        return $availablePeople->filter(function ($person) use ($firstName, $lastName, $email, $phone) {
            $pFirst = mb_strtolower($person->first_name ?? '');
            $pLast = mb_strtolower($person->last_name ?? '');
            $pEmail = mb_strtolower($person->email ?? '');
            $pPhone = preg_replace('/\D/', '', $person->phone ?? '');

            // Exact email match
            if ($email && $pEmail && $email === $pEmail) {
                return true;
            }

            // Phone match — compare last 9 digits (works for any country code)
            if ($phone && $pPhone && strlen($phone) >= 9 && strlen($pPhone) >= 9) {
                if (substr($phone, -9) === substr($pPhone, -9)) {
                    return true;
                }
            }

            // Name match (first + last)
            if ($firstName && $pFirst && $firstName === $pFirst) {
                if ($lastName && $pLast && $lastName === $pLast) {
                    return true;
                }
                // Only first name match — still suggest
                if (!$lastName || !$pLast) {
                    return true;
                }
            }

            return false;
        })->values();
    }
}
