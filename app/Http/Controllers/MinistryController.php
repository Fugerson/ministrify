<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MinistryController extends Controller
{
    public function index()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $query = Ministry::where('church_id', $church->id)
            ->with(['leader', 'members', 'positions']);

        // Leaders can only see their ministries
        if ($user->isLeader() && $user->person) {
            $query->where('leader_id', $user->person->id);
        }

        $ministries = $query->get();

        return view('ministries.index', compact('ministries'));
    }

    public function create()
    {
        $this->authorize('create', Ministry::class);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->orderBy('last_name')
            ->get();

        return view('ministries.create', compact('people'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Ministry::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'leader_id' => 'nullable|exists:people,id',
            'monthly_budget' => 'nullable|numeric|min:0',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        $ministry = Ministry::create($validated);

        // Create default positions if provided
        if ($request->has('positions')) {
            foreach ($request->positions as $index => $positionName) {
                if (!empty($positionName)) {
                    $ministry->positions()->create([
                        'name' => $positionName,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('ministries.show', $ministry)
            ->with('success', 'Служіння створено.');
    }

    public function show(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('view-ministry', $ministry);
        $church = $this->getCurrentChurch();

        $ministry->load([
            'leader',
            'positions',
            'members',
            'events' => fn($q) => $q->upcoming()->with(['ministry.positions', 'assignments'])->limit(10),
            'expenses' => fn($q) => $q->forMonth(now()->year, now()->month)->with('category'),
        ]);

        $tab = request('tab', 'schedule');

        // Get boards for task creation
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', false)
            ->get();

        return view('ministries.show', compact('ministry', 'tab', 'boards'));
    }

    public function edit(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)
            ->orderBy('last_name')
            ->get();

        return view('ministries.edit', compact('ministry', 'people'));
    }

    public function update(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'leader_id' => 'nullable|exists:people,id',
            'monthly_budget' => 'nullable|numeric|min:0',
        ]);

        $ministry->update($validated);

        return redirect()->route('ministries.show', $ministry)
            ->with('success', 'Служіння оновлено.');
    }

    public function destroy(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        $this->authorize('delete', $ministry);

        $ministry->delete();

        return back()->with('success', 'Служіння видалено.');
    }

    public function addMember(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'position_ids' => 'nullable|array',
        ]);

        // Check if already a member
        if ($ministry->members()->where('person_id', $validated['person_id'])->exists()) {
            return back()->with('error', 'Ця людина вже є учасником служіння.');
        }

        $ministry->members()->attach($validated['person_id'], [
            'position_ids' => json_encode($validated['position_ids'] ?? []),
        ]);

        return back()->with('success', 'Учасника додано.');
    }

    public function removeMember(Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $ministry->members()->detach($person->id);

        return back()->with('success', 'Учасника видалено.');
    }

    public function updateMemberPositions(Request $request, Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'position_ids' => 'nullable|array',
        ]);

        $ministry->members()->updateExistingPivot($person->id, [
            'position_ids' => json_encode($validated['position_ids'] ?? []),
        ]);

        return back()->with('success', 'Позиції оновлено.');
    }

    private function authorizeChurch(Ministry $ministry): void
    {
        if ($ministry->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
