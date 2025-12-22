<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::where('church_id', auth()->user()->church_id)
            ->with(['leader', 'members'])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        $people = Person::where('church_id', auth()->user()->church_id)
            ->orderBy('first_name')
            ->get();

        return view('groups.create', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:people,id',
            'meeting_day' => 'nullable|string',
            'meeting_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $validated['church_id'] = auth()->user()->church_id;

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

        $availablePeople = Person::where('church_id', auth()->user()->church_id)
            ->whereNotIn('id', $group->members->pluck('id'))
            ->orderBy('first_name')
            ->get();

        // Get boards for task creation
        $boards = Board::where('church_id', auth()->user()->church_id)
            ->where('is_archived', false)
            ->get();

        return view('groups.show', compact('group', 'availablePeople', 'boards'));
    }

    public function edit(Group $group)
    {
        $this->authorize('update', $group);

        $people = Person::where('church_id', auth()->user()->church_id)
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
            'leader_id' => 'nullable|exists:people,id',
            'meeting_day' => 'nullable|string',
            'meeting_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $group->update($validated);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Групу оновлено');
    }

    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Групу видалено');
    }

    public function addMember(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'role' => 'nullable|string',
        ]);

        $group->members()->attach($validated['person_id'], [
            'role' => $validated['role'] ?? 'member',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Учасника додано');
    }

    public function removeMember(Group $group, Person $person)
    {
        $this->authorize('update', $group);

        $group->members()->detach($person->id);

        return back()->with('success', 'Учасника видалено');
    }

    public function attendance(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'date' => 'required|date',
            'total_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $group->attendances()->updateOrCreate(
            ['date' => $validated['date']],
            [
                'total_count' => $validated['total_count'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Відвідуваність збережено');
    }
}
