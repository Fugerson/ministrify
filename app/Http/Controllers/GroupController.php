<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Person;
use App\Rules\BelongsToChurch;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        if (!auth()->user()->canView('groups')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $groups = Group::where('church_id', $this->getCurrentChurch()->id)
            ->with(['leader', 'members'])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        $people = Person::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('first_name')
            ->get();

        return view('groups.create', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'status' => 'nullable|in:active,paused,vacation',
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

        return redirect()->route('groups.show', $group)
            ->with('success', 'Групу створено');
    }

    public function show(Group $group)
    {
        $this->authorize('view', $group);

        $group->load(['leader', 'members', 'attendances' => fn($q) => $q->orderByDesc('date')->limit(10)]);

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

        return view('groups.show', compact('group', 'availablePeople', 'attendanceStats'));
    }

    public function edit(Group $group)
    {
        $this->authorize('update', $group);

        $people = Person::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('first_name')
            ->get();

        return view('groups.edit', compact('group', 'people'));
    }

    public function update(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'status' => 'nullable|in:active,paused,vacation',
        ]);

        $group->update($validated);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Групу оновлено');
    }

    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Групу видалено.');
    }

    public function addMember(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'person_id' => ['required', new BelongsToChurch(Person::class)],
            'role' => 'nullable|string',
        ]);

        $group->members()->attach($validated['person_id'], [
            'role' => $validated['role'] ?? 'member',
            'joined_at' => now(),
        ]);

        // Log member added
        $person = Person::find($validated['person_id']);
        $this->logAuditAction('member_added', 'Group', $group->id, $group->name, [
            'person_id' => $validated['person_id'],
            'person_name' => $person?->full_name,
            'role' => $validated['role'] ?? 'member',
        ]);

        return back()->with('success', 'Учасника додано');
    }

    public function removeMember(Group $group, Person $person)
    {
        $this->authorize('update', $group);

        $group->members()->detach($person->id);

        // Log member removed
        $this->logAuditAction('member_removed', 'Group', $group->id, $group->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
        ]);

        return back()->with('success', 'Учасника видалено');
    }

    public function updateMemberRole(Request $request, Group $group, Person $person)
    {
        $this->authorize('update', $group);

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
        return back()->with('success', "Роль змінено на: {$roleLabel}");
    }
}
