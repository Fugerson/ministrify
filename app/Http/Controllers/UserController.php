<?php

namespace App\Http\Controllers;

use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Person;
use App\Models\User;
use App\Notifications\AccessGranted;
use App\Rules\SecurePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();

        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->get();

        $churchRolesById = $churchRoles->keyBy('id');

        $users = $church->members()
            ->orderBy('name')
            ->get();

        // Preload all people for this church keyed by user_id to avoid N+1
        $userIds = $users->pluck('id')->toArray();
        $peopleByUserId = Person::where('church_id', $church->id)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $users->each(function ($user) use ($peopleByUserId, $churchRolesById) {
            $user->setRelation('person', $peopleByUserId[$user->id] ?? null);
            $pivotRoleId = $user->pivot->church_role_id;
            $user->setRelation('churchRole',
                $pivotRoleId ? ($churchRolesById[$pivotRoleId] ?? null) : null
            );
        });

        return view('settings.users.index', compact('users', 'churchRoles'));
    }

    public function create()
    {
        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->whereDoesntHave('user')
            ->orderBy('last_name')
            ->get();

        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->get();

        return view('settings.users.create', compact('people', 'churchRoles'));
    }

    public function store(Request $request)
    {
        $church = $this->getCurrentChurch();

        // If person_id provided, name/email are optional (will be taken from Person)
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email'],
            'church_role_id' => ['required', Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
            'person_id' => 'nullable|exists:people,id',
        ]);

        // Get name and email from Person if person_id provided
        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? null;

        if (! empty($validated['person_id'])) {
            $person = Person::where('id', $validated['person_id'])
                ->where('church_id', $church->id)
                ->firstOrFail();

            $name = $person->full_name;
            $email = $person->email;

            if (empty($email)) {
                return back()->withInput()->withErrors(['person_id' => __('messages.person_no_email')]);
            }
        } else {
            // No person selected - require name and email
            if (empty($name) || empty($email)) {
                return back()->withInput()->withErrors(['name' => __('messages.provide_name_and_email')]);
            }
        }

        // Check if user with this email already exists (active) → add to this church via pivot
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($existingUser->belongsToChurch($church->id)) {
                return back()->withInput()->withErrors(['email' => __('messages.user_already_member')]);
            }

            // Add to this church via pivot
            $personId = ! empty($validated['person_id']) ? $person->id : null;

            // Find or create Person
            if (! $personId) {
                // Try to find existing unlinked Person by email
                $foundPerson = Person::where('church_id', $church->id)
                    ->where('email', $email)
                    ->whereNull('user_id')
                    ->first();

                // Fallback: try by name
                if (! $foundPerson) {
                    $nameParts = explode(' ', $name, 2);
                    $fn = $nameParts[0];
                    $ln = $nameParts[1] ?? '';
                    if ($fn && $ln) {
                        $foundPerson = Person::where('church_id', $church->id)
                            ->where('first_name', $fn)
                            ->where('last_name', $ln)
                            ->whereNull('user_id')
                            ->first();
                    }
                }

                if ($foundPerson) {
                    $foundPerson->update(['user_id' => $existingUser->id]);
                    $personId = $foundPerson->id;
                } else {
                    $nameParts = $nameParts ?? explode(' ', $name, 2);
                    $newPerson = Person::create([
                        'church_id' => $church->id,
                        'user_id' => $existingUser->id,
                        'first_name' => $nameParts[0],
                        'last_name' => $nameParts[1] ?? '',
                        'email' => $email,
                        'membership_status' => 'member',
                    ]);
                    $personId = $newPerson->id;
                }
            } else {
                $person->update(['user_id' => $existingUser->id]);
            }

            DB::table('church_user')->insert([
                'user_id' => $existingUser->id,
                'church_id' => $church->id,
                'church_role_id' => $validated['church_role_id'],
                'person_id' => $personId,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            broadcast(new \App\Events\ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();

            return $this->successResponse($request, __('messages.user_added_to_church'), 'settings.users.index');
        }

        // Generate secure random password (user will reset via email)
        $password = Str::random(32);

        // Restore soft-deleted user or create new
        $trashedUser = User::onlyTrashed()->where('email', $email)->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'church_id' => $church->id,
                'name' => $name,
                'password' => Hash::make($password),
                'church_role_id' => $validated['church_role_id'],
            ]);
            $user = $trashedUser;

            Log::channel('security')->info('Soft-deleted user restored via admin user creation', [
                'user_id' => $user->id,
                'email' => $email,
                'church_id' => $church->id,
            ]);
        } else {
            $user = User::create([
                'church_id' => $church->id,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'church_role_id' => $validated['church_role_id'],
            ]);
        }

        // Link to person if provided, otherwise find by email or create new Person
        $personId = null;
        if (! empty($validated['person_id'])) {
            $person->update(['user_id' => $user->id]);
            $personId = $person->id;
        } else {
            // Try to find existing Person by email (may have been added manually before)
            $existingPerson = Person::where('church_id', $church->id)
                ->where('email', $email)
                ->whereNull('user_id')
                ->first();

            // Fallback: try to find by name
            if (! $existingPerson) {
                $nameParts = explode(' ', $name, 2);
                $fn = $nameParts[0];
                $ln = $nameParts[1] ?? '';
                if ($fn && $ln) {
                    $existingPerson = Person::where('church_id', $church->id)
                        ->where('first_name', $fn)
                        ->where('last_name', $ln)
                        ->whereNull('user_id')
                        ->first();
                }
            }

            if ($existingPerson) {
                $existingPerson->update(['user_id' => $user->id]);
                $personId = $existingPerson->id;
            } elseif (! Person::where('user_id', $user->id)->where('church_id', $church->id)->exists()) {
                $nameParts = explode(' ', $name, 2);
                $newPerson = Person::create([
                    'church_id' => $church->id,
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $email,
                    'membership_status' => 'member',
                ]);
                $personId = $newPerson->id;
            }
        }

        // Create pivot record (updateOrInsert to handle stale pivots from soft-deleted users)
        DB::table('church_user')->updateOrInsert(
            ['user_id' => $user->id, 'church_id' => $church->id],
            [
                'church_role_id' => $validated['church_role_id'],
                'person_id' => $personId,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Try to send password reset link
        $message = __('messages.user_created');
        try {
            $status = Password::sendResetLink(['email' => $user->email]);
            if ($status === Password::RESET_LINK_SENT) {
                $message .= __('messages.login_link_sent');
            }
        } catch (\Exception $e) {
            $message .= __('messages.setup_password_manually');
        }

        broadcast(new \App\Events\ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, $message, 'settings.users.index');
    }

    public function edit(User $user)
    {
        $this->authorizeChurch($user);

        $church = $this->getCurrentChurch();

        // Load church_role_id from pivot for this church (not from user's active church)
        $pivotRecord = DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();
        if ($pivotRecord) {
            $user->church_role_id = $pivotRecord->church_role_id;
        }

        $people = Person::where('church_id', $church->id)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('user')
                    ->orWhere('user_id', $user->id);
            })
            ->orderBy('last_name')
            ->get();

        $churchRoles = ChurchRole::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->get();

        return view('settings.users.edit', compact('user', 'people', 'churchRoles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeChurch($user);

        $church = $this->getCurrentChurch();

        // If person_id provided, name/email are optional (will be taken from Person)
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'church_role_id' => ['nullable', Rule::exists('church_roles', 'id')->where('church_id', $church->id)],
            'person_id' => 'nullable|exists:people,id',
            'link_person_id' => 'nullable|integer|exists:people,id',
            'password' => ['nullable', 'string', 'min:10', new SecurePassword],
        ]);

        // Get name and email from Person if person_id provided
        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? null;
        $churchRoleId = $validated['church_role_id'] ?: null; // Convert empty string to null

        // Prevent non-admins from assigning admin roles (privilege escalation)
        if ($churchRoleId) {
            $targetRole = ChurchRole::find($churchRoleId);
            if ($targetRole && $targetRole->is_admin_role && ! auth()->user()->isAdmin()) {
                return $this->errorResponse($request, __('messages.only_admins_can_assign_admin_roles'));
            }
        }

        // Check role from pivot for THIS church, not from user's active church
        $pivotRecord = DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();
        $hadNoRole = ! $pivotRecord || $pivotRecord->church_role_id === null;

        // link_person_id: lightweight linking (approval flow) — no name/email override
        $linkPersonId = $validated['link_person_id'] ?? null;

        if (! empty($validated['person_id'])) {
            $person = Person::where('id', $validated['person_id'])
                ->where('church_id', $church->id)
                ->firstOrFail();

            $name = $person->full_name;
            $email = $person->email;

            if (empty($email)) {
                $error = __('messages.person_no_email_edit');
                if ($request->expectsJson()) {
                    return response()->json(['message' => $error], 422);
                }

                return back()->withInput()->withErrors(['person_id' => $error]);
            }

            // Check if email is already taken by another user
            if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
                $error = __('messages.user_email_already_exists');
                if ($request->expectsJson()) {
                    return response()->json(['message' => $error], 422);
                }

                return back()->withInput()->withErrors(['person_id' => $error]);
            }
        } elseif ($linkPersonId) {
            // Lightweight link: just verify Person belongs to this church and is free
            $linkPerson = Person::where('id', $linkPersonId)
                ->where('church_id', $church->id)
                ->first();

            if (! $linkPerson) {
                return response()->json(['message' => __('messages.selected_person_not_found')], 422);
            }
            if ($linkPerson->user_id && $linkPerson->user_id !== $user->id) {
                return response()->json(['message' => __('messages.person_already_linked')], 422);
            }

            // Use existing User name/email — don't override
            if (empty($name)) {
                $name = $user->name;
            }
            if (empty($email)) {
                $email = $user->email;
            }
        } else {
            // No person selected - require name and email
            if (empty($name) || empty($email)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('messages.provide_name_and_email')], 422);
                }

                return back()->withInput()->withErrors(['name' => __('messages.provide_name_and_email')]);
            }
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
        ];
        // Only update users.church_role_id if this is the user's active church
        if ($user->church_id === $church->id) {
            $updateData['church_role_id'] = $churchRoleId;
        }
        $user->update($updateData);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update person link
        $oldPerson = Person::where('user_id', $user->id)->where('church_id', $church->id)->first();
        $newPersonId = $validated['person_id'] ?? $linkPersonId;

        if ($newPersonId && (! $oldPerson || $oldPerson->id !== (int) $newPersonId)) {
            // Detach old person first (unique constraint: user_id + church_id)
            if ($oldPerson) {
                $oldPerson->update(['user_id' => null]);
            }

            // Link new person
            $targetPerson = $linkPersonId
                ? $linkPerson
                : $person;
            $targetPerson->update(['user_id' => $user->id]);

            // Soft-delete old auto-created Person if it has no meaningful data
            if ($oldPerson) {
                $hasData = $oldPerson->assignments()->exists()
                    || $oldPerson->ministries()->exists()
                    || $oldPerson->groups()->exists()
                    || $oldPerson->transactions()->exists()
                    || $oldPerson->attendanceRecords()->exists();

                if (! $hasData) {
                    $oldPerson->delete();
                }
            }
        } elseif (! $newPersonId && $oldPerson && $request->has('person_id')) {
            // Unlinking person entirely (only when explicitly cleared in the form)
            $oldPerson->update(['user_id' => null]);
        }

        // Auto-create Person if user has none after all operations
        $currentPerson = Person::where('user_id', $user->id)->where('church_id', $church->id)->first();
        if (! $currentPerson) {
            $nameParts = explode(' ', $name, 2);
            $currentPerson = Person::create([
                'church_id' => $church->id,
                'user_id' => $user->id,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $email,
            ]);
            $newPersonId = $currentPerson->id;
        }

        if (! $newPersonId) {
            $newPersonId = $currentPerson->id;
        }

        // Sync pivot record
        $pivotUpdate = [
            'church_role_id' => $churchRoleId,
            'person_id' => $newPersonId,
            'updated_at' => now(),
        ];
        // Mark as approved if granting role to pending user
        if ($hadNoRole && $churchRoleId !== null) {
            $pivotUpdate['role_approval_status'] = 'approved';
        }
        DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->update($pivotUpdate);

        // Update user's approval status if granting role
        if ($hadNoRole && $churchRoleId !== null) {
            $user->update([
                'servant_approval_status' => 'approved',
                'servant_approved_at' => now(),
            ]);
        }

        // Send notification if user was granted access (had no role before, now has one)
        if ($hadNoRole && $churchRoleId !== null) {
            $role = ChurchRole::find($churchRoleId);
            if ($role) {
                $user->notify(new AccessGranted($role->name, $church->name));
            }
        }

        broadcast(new \App\Events\ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.user_updated'), 'settings.users.index');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeChurch($user);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return $this->errorResponse($request, __('messages.cannot_delete_self'));
        }

        $church = $this->getCurrentChurch();

        // Prevent deleting the last admin
        $userPivot = DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();
        if ($userPivot && $userPivot->church_role_id) {
            $userRole = ChurchRole::find($userPivot->church_role_id);
            if ($userRole && $userRole->is_admin_role) {
                $adminCount = DB::table('church_user')
                    ->join('church_roles', 'church_user.church_role_id', '=', 'church_roles.id')
                    ->where('church_user.church_id', $church->id)
                    ->where('church_roles.is_admin_role', true)
                    ->count();
                if ($adminCount <= 1) {
                    return $this->errorResponse($request, __('messages.cannot_delete_last_admin'));
                }
            }
        }

        // Remove from this church's pivot
        DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->delete();

        // Remove person link for this church
        Person::where('user_id', $user->id)->where('church_id', $church->id)->update(['user_id' => null]);

        // Check if user has other churches
        $otherChurch = DB::table('church_user')
            ->where('user_id', $user->id)
            ->first();

        if ($otherChurch) {
            // Switch user to another church
            $user->switchToChurch($otherChurch->church_id);
        } else {
            // No more churches — soft-delete the user
            $user->update(['church_id' => null, 'church_role_id' => null]);
            $user->delete();
        }

        broadcast(new \App\Events\ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.user_deleted'), 'settings.users.index');
    }

    public function sendInvite(Request $request, User $user)
    {
        $this->authorizeChurch($user);

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
            if ($status !== Password::RESET_LINK_SENT) {
                return $this->errorResponse($request, __('messages.invite_send_failed'));
            }
        } catch (\Exception $e) {
            return $this->errorResponse($request, __('messages.invite_error', ['error' => $e->getMessage()]));
        }

        // Log invite sent
        $this->logAuditAction('sent', 'User', $user->id, $user->name, [
            'type' => 'invite',
            'email' => $user->email,
        ]);

        return $this->successResponse($request, __('messages.invite_sent_to', ['email' => $user->email]));
    }

    public function getPermissions(User $user)
    {
        $this->authorizeChurch($user);

        $church = $this->getCurrentChurch();

        // Read role and overrides from pivot for THIS church
        $pivotRecord = DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();

        $churchRole = $pivotRecord?->church_role_id
            ? ChurchRole::find($pivotRecord->church_role_id)
            : null;

        $overrides = $pivotRecord?->permission_overrides
            ? json_decode($pivotRecord->permission_overrides, true)
            : [];

        return response()->json([
            'role_permissions' => $churchRole ? $churchRole->getAllPermissions() : [],
            'overrides' => $overrides,
            'role_name' => $churchRole?->name ?? __('messages.no_role'),
            'is_admin_role' => $churchRole?->is_admin_role ?? false,
        ]);
    }

    public function updatePermissions(Request $request, User $user)
    {
        $this->authorizeChurch($user);

        $church = $this->getCurrentChurch();

        // Read role from pivot for THIS church
        $pivotRecord = DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->first();

        $churchRole = $pivotRecord?->church_role_id
            ? ChurchRole::find($pivotRecord->church_role_id)
            : null;

        if ($churchRole?->is_admin_role) {
            return response()->json(['message' => __('messages.admin_role_full_access')], 400);
        }

        $overridesInput = $request->input('overrides', []);
        if (! is_array($overridesInput)) {
            $overridesInput = [];
        }

        // Strip actions already granted by role
        $rolePermissions = $churchRole ? $churchRole->getAllPermissions() : [];
        $cleanOverrides = [];

        foreach ($overridesInput as $module => $actions) {
            if (! is_array($actions) || ! array_key_exists($module, ChurchRolePermission::MODULES)) {
                continue;
            }

            $allowedActions = ChurchRolePermission::MODULES[$module]['actions'];
            $roleActions = $rolePermissions[$module] ?? [];

            // Keep only actions that are allowed for this module and not already in role
            $extra = array_values(array_intersect(
                array_diff($actions, $roleActions),
                $allowedActions
            ));

            if (! empty($extra)) {
                $cleanOverrides[$module] = $extra;
            }
        }

        // Update user's overrides only if this is the active church
        if ($user->church_id === $church->id) {
            $user->setPermissionOverrides($cleanOverrides);
        }

        // Always sync to pivot
        DB::table('church_user')
            ->where('user_id', $user->id)
            ->where('church_id', $church->id)
            ->update([
                'permission_overrides' => $cleanOverrides ? json_encode($cleanOverrides) : null,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => __('messages.extra_permissions_saved'),
            'overrides' => $cleanOverrides,
        ]);
    }

    protected function authorizeChurch($model): void
    {
        if ($model instanceof User) {
            if (! $model->belongsToChurch($this->getCurrentChurch()->id)) {
                abort(404);
            }

            return;
        }

        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
