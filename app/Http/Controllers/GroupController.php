<?php

namespace App\Http\Controllers;

use App\Events\ChurchDataUpdated;
use App\Models\Group;
use App\Models\GroupGuest;
use App\Models\Person;
use App\Rules\BelongsToChurch;
use App\Services\ImageService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->canView('groups')) {
            return $this->errorResponse($request, __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $groups = Group::where('church_id', $this->getCurrentChurch()->id)
            ->with(['leader', 'members'])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        $people = Person::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('first_name')
            ->get();

        return view('groups.index', compact('groups', 'people'));
    }

    public function create()
    {
        $this->authorize('create', Group::class);

        $people = Person::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('first_name')
            ->get();

        return view('groups.create', compact('people'));
    }

    public function store(Request $request)
    {
        $this->checkPlanLimit('groups');
        $this->authorize('create', Group::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'status' => 'nullable|in:active,paused,vacation',
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meeting_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        $validated['status'] = $validated['status'] ?? 'active';

        $group = Group::create($validated);

        // Add leader as member with leader role
        if ($group->leader_id) {
            $group->members()->attach($group->leader_id, [
                'role' => 'leader',
                'joined_at' => now(),
            ]);
        }

        broadcast(new ChurchDataUpdated($validated['church_id'], 'groups', 'created', $group->name))->toOthers();

        return $this->successResponse($request, 'Групу створено!', 'groups.show', [$group]);
    }

    public function show(Group $group)
    {
        $this->authorizeChurch($group);
        $this->authorize('view', $group);

        $group->load(['leader', 'members', 'guests', 'attendances' => fn ($q) => $q->orderByDesc('date')->limit(10)]);

        $availablePeople = Person::where('church_id', $this->getCurrentChurch()->id)
            ->whereNotIn('id', $group->members->pluck('id'))
            ->orderBy('first_name')
            ->get();

        // Attendance stats
        $attendanceStats = [
            'total_meetings' => $group->attendances()->count(),
            'average_attendance' => $group->average_attendance,
            'trend' => $group->attendance_trend,
            'last_meeting' => $group->last_attendance,
        ];

        $existingToday = $group->attendances()->whereDate('date', today())->first();

        return view('groups.show', compact('group', 'availablePeople', 'attendanceStats', 'existingToday'));
    }

    public function edit(Group $group)
    {
        $this->authorizeChurch($group);
        $this->authorize('update', $group);

        $people = Person::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('first_name')
            ->get();

        return view('groups.edit', compact('group', 'people'));
    }

    public function update(Request $request, Group $group)
    {
        $this->authorizeChurch($group);
        $this->authorize('update', $group);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'status' => 'nullable|in:active,paused,vacation',
            'meeting_day' => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meeting_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
        ]);

        $oldLeaderId = $group->getOriginal('leader_id');
        $group->update($validated);

        // Demote old leader's pivot role if leader changed
        if ($oldLeaderId && $oldLeaderId !== $group->leader_id) {
            $group->members()->updateExistingPivot($oldLeaderId, ['role' => 'member']);
        }

        // Ensure new leader is a member with leader role
        if ($group->leader_id) {
            if (! $group->members()->where('people.id', $group->leader_id)->exists()) {
                $group->members()->attach($group->leader_id, [
                    'role' => 'leader',
                    'joined_at' => now(),
                ]);
            } else {
                $group->members()->updateExistingPivot($group->leader_id, ['role' => 'leader']);
            }
        }

        broadcast(new ChurchDataUpdated($group->church_id, 'groups', 'updated', $group->name))->toOthers();

        return $this->successResponse($request, 'Групу оновлено!', 'groups.show', [$group]);
    }

    public function destroy(Request $request, Group $group)
    {
        $this->authorizeChurch($group);
        $this->authorize('delete', $group);

        $group->delete();

        broadcast(new ChurchDataUpdated($group->church_id, 'groups', 'deleted'))->toOthers();

        return $this->successResponse($request, 'Групу видалено.', 'groups.index');
    }

    public function addMember(Request $request, Group $group)
    {
        $this->authorizeChurch($group);
        $this->authorize('update', $group);

        $role = $request->input('role', 'member');

        // Guest flow: create GroupGuest record (separate from people table)
        if ($role === 'guest') {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date|before_or_equal:today',
                'photo' => 'nullable|image|max:5120',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($request->hasFile('photo')) {
                $validated['photo'] = app(ImageService::class)->storeProfilePhoto(
                    $request->file('photo'),
                    'people'
                );
            }

            $validated['church_id'] = $this->getCurrentChurch()->id;
            $validated['group_id'] = $group->id;

            $guest = GroupGuest::create($validated);

            $this->logAuditAction('member_added', 'Group', $group->id, $group->name, [
                'guest_id' => $guest->id,
                'guest_name' => $guest->full_name,
                'role' => 'guest',
            ]);

            broadcast(new ChurchDataUpdated($this->getCurrentChurch()->id, 'groups', 'guest_added', $group->name))->toOthers();

            return $this->successResponse($request, __('messages.guest_added'));
        }

        // Regular member/assistant flow
        $validated = $request->validate([
            'person_id' => ['required', new BelongsToChurch(Person::class)],
            'role' => 'nullable|in:leader,assistant,member',
        ]);

        if ($group->members()->where('people.id', $validated['person_id'])->exists()) {
            return $this->errorResponse($request, 'Ця людина вже є учасником групи.');
        }

        $group->members()->attach($validated['person_id'], [
            'role' => $validated['role'] ?? 'member',
            'joined_at' => now(),
        ]);

        $person = Person::find($validated['person_id']);
        $this->logAuditAction('member_added', 'Group', $group->id, $group->name, [
            'person_id' => $validated['person_id'],
            'person_name' => $person?->full_name,
            'role' => $validated['role'] ?? 'member',
        ]);

        broadcast(new ChurchDataUpdated($this->getCurrentChurch()->id, 'groups', 'member_added', $group->name))->toOthers();

        return $this->successResponse($request, 'Учасника додано');
    }

    public function removeMember(Request $request, Group $group, Person $person)
    {
        $this->authorizeChurch($group);
        $this->authorize('update', $group);
        abort_unless($person->church_id === $this->getCurrentChurch()->id, 404);

        // Prevent removing the leader from group
        if ($group->leader_id === $person->id) {
            return $this->errorResponse($request, __('messages.cannot_remove_leader'));
        }

        $group->members()->detach($person->id);

        // Log member removed
        $this->logAuditAction('member_removed', 'Group', $group->id, $group->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
        ]);

        broadcast(new ChurchDataUpdated($this->getCurrentChurch()->id, 'groups', 'member_removed', $group->name))->toOthers();

        return $this->successResponse($request, 'Учасника видалено');
    }

    public function updateMemberRole(Request $request, Group $group, Person $person)
    {
        $this->authorizeChurch($group);
        $this->authorize('update', $group);
        abort_unless($person->church_id === $this->getCurrentChurch()->id, 404);

        $validated = $request->validate([
            'role' => 'required|in:leader,assistant,member',
        ]);

        // If promoting to leader, demote current leader
        if ($validated['role'] === 'leader' && $group->leader_id !== $person->id) {
            // Update old leader to assistant
            if ($group->leader_id) {
                $group->members()->updateExistingPivot($group->leader_id, ['role' => 'assistant']);
            }
            // Update group leader
            $group->update(['leader_id' => $person->id]);
        }

        // If demoting the current leader, clear group leader_id
        if ($group->leader_id === $person->id && $validated['role'] !== 'leader') {
            $group->update(['leader_id' => null]);
        }

        // Get old role before updating
        $oldRole = $group->members()->where('person_id', $person->id)->first()?->pivot?->role ?? 'member';

        $group->members()->updateExistingPivot($person->id, ['role' => $validated['role']]);

        // Log role change
        $this->logAuditAction('member_role_changed', 'Group', $group->id, $group->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
            'new_role' => $validated['role'],
        ], [
            'old_role' => $oldRole,
        ]);

        $roleLabel = Group::ROLES[$validated['role']] ?? $validated['role'];

        return $this->successResponse($request, "Роль змінено на: {$roleLabel}");
    }
}
