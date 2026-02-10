<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Resource;
use App\Models\Song;
use App\Models\WorshipRole;
use App\Rules\BelongsToChurch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MinistryController extends Controller
{
    public function index()
    {
        if (!auth()->user()->canView('ministries')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $ministries = Ministry::where('church_id', $church->id)
            ->with(['leader', 'members', 'positions'])
            ->get();

        // Filter ministries based on visibility settings
        if (!$user->isAdmin()) {
            $ministries = $ministries->filter(function ($ministry) {
                return $ministry->canAccess();
            });
        }

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
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'monthly_budget' => 'nullable|numeric|min:0',
        ]);

        $church = $this->getCurrentChurch();
        $validated['church_id'] = $church->id;
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

        \App\Models\Church::clearMinistriesCache($church->id);

        return redirect()->route('ministries.show', $ministry)
            ->with('success', 'Служіння створено.');
    }

    public function show(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('view-ministry', $ministry);

        // Check private access
        if (!$ministry->canAccess()) {
            abort(403, 'Ця команда приватна. Доступ тільки для учасників.');
        }

        $church = $this->getCurrentChurch();

        $ministry->load([
            'leader',
            'positions',
            'members',
            'events' => fn($q) => $q->orderBy('date')->orderBy('time')->with(['ministry.positions', 'assignments']),
            'transactions' => fn($q) => $q->where('direction', 'out')->whereMonth('date', now()->month)->whereYear('date', now()->year)->with(['category', 'attachments'])->orderByDesc('date'),
            'goals' => fn($q) => $q->with(['tasks.assignee', 'creator'])->orderByDesc('created_at'),
        ]);

        // Default tab: 'goals' for managers, 'schedule' for others
        $defaultTab = Gate::allows('manage-ministry', $ministry) ? 'goals' : 'schedule';
        $tab = request('tab', $defaultTab);

        // Get boards for task creation
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', false)
            ->get();

        // Get available people for adding members (always load for client-side tabs)
        $memberIds = $ministry->members->pluck('id')->toArray();
        $availablePeople = Person::where('church_id', $church->id)
            ->whereNotIn('id', $memberIds)
            ->orderBy('last_name')
            ->get();

        // Get registered users (people with user accounts) for access settings
        $registeredUsers = Person::where('church_id', $church->id)
            ->whereHas('user')
            ->orderBy('last_name')
            ->get();

        // Resources: folder navigation
        $folderId = request('folder');
        $currentFolder = null;
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = Resource::where('id', $folderId)
                ->where('church_id', $church->id)
                ->where('ministry_id', $ministry->id)
                ->where('type', 'folder')
                ->first();

            if ($currentFolder) {
                $breadcrumbs = $currentFolder->getBreadcrumbs();
            } else {
                $folderId = null;
            }
        }

        $resources = Resource::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('parent_id', $currentFolder?->id)
            ->with('creator')
            ->orderByRaw("type = 'folder' DESC")
            ->orderBy('name')
            ->get();

        // Goals stats
        $goalsStats = [
            'total_goals' => $ministry->goals()->count(),
            'active_goals' => $ministry->goals()->active()->count(),
            'completed_goals' => $ministry->goals()->completed()->count(),
            'total_tasks' => $ministry->tasks()->count(),
            'completed_tasks' => $ministry->tasks()->done()->count(),
            'overdue_tasks' => $ministry->tasks()->overdue()->count(),
        ];

        // Load songs, worship events, and worship roles for worship ministries
        $songs = [];
        $worshipEvents = collect();
        $worshipRoles = collect();
        if ($ministry->is_worship_ministry) {
            $songs = Song::where('church_id', $church->id)
                ->orderBy('title')
                ->get();

            $worshipEvents = Event::where('church_id', $church->id)
                ->where('has_music', true)
                ->where('date', '>=', now()->subDays(7))
                ->withCount(['songs as songs_count', 'worshipTeam as team_count'])
                ->orderBy('date')
                ->orderBy('time')
                ->get();

            $worshipRoles = WorshipRole::where('church_id', $church->id)
                ->orderBy('sort_order')
                ->get();
        }

        return view('ministries.show', compact('ministry', 'tab', 'boards', 'availablePeople', 'resources', 'currentFolder', 'breadcrumbs', 'registeredUsers', 'goalsStats', 'songs', 'worshipEvents', 'worshipRoles'));
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
            'leader_id' => ['nullable', new BelongsToChurch(Person::class)],
            'monthly_budget' => 'nullable|numeric|min:0',
            'is_worship_ministry' => 'boolean',
        ]);

        $validated['is_worship_ministry'] = $request->boolean('is_worship_ministry');

        $ministry->update($validated);

        \App\Models\Church::clearMinistriesCache($ministry->church_id);

        return redirect()->route('ministries.show', $ministry)
            ->with('success', 'Служіння оновлено.');
    }

    public function destroy(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        $this->authorize('delete', $ministry);

        $churchId = $ministry->church_id;
        $ministry->delete();

        \App\Models\Church::clearMinistriesCache($churchId);

        return redirect()->route('ministries.index')->with('success', 'Служіння видалено.');
    }

    public function addMember(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'person_id' => ['required', new \App\Rules\BelongsToChurch(\App\Models\Person::class)],
            'position_ids' => 'nullable|array',
        ]);

        // Check if already a member
        if ($ministry->members()->where('person_id', $validated['person_id'])->exists()) {
            return back()->with('error', 'Ця людина вже є учасником служіння.');
        }

        $ministry->members()->attach($validated['person_id'], [
            'position_ids' => json_encode($validated['position_ids'] ?? []),
        ]);

        // Log member added
        $person = Person::find($validated['person_id']);
        $this->logAuditAction('member_added', 'Ministry', $ministry->id, $ministry->name, [
            'person_id' => $validated['person_id'],
            'person_name' => $person?->full_name,
            'position_ids' => $validated['position_ids'] ?? [],
        ]);

        return back()->with('success', 'Учасника додано.');
    }

    public function removeMember(Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $ministry->members()->detach($person->id);

        // Log member removed
        $this->logAuditAction('member_removed', 'Ministry', $ministry->id, $ministry->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
        ]);

        return back()->with('success', 'Учасника видалено.');
    }

    public function updateMemberPositions(Request $request, Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'position_ids' => 'nullable|array',
        ]);

        // Get old positions
        $oldPivot = $ministry->members()->where('person_id', $person->id)->first()?->pivot;
        $oldPositionIds = $oldPivot ? json_decode($oldPivot->position_ids ?? '[]', true) : [];

        $ministry->members()->updateExistingPivot($person->id, [
            'position_ids' => json_encode($validated['position_ids'] ?? []),
        ]);

        // Log positions update
        $this->logAuditAction('positions_updated', 'Ministry', $ministry->id, $ministry->name, [
            'person_id' => $person->id,
            'person_name' => $person->full_name,
            'new_position_ids' => $validated['position_ids'] ?? [],
        ], [
            'old_position_ids' => $oldPositionIds,
        ]);

        return back()->with('success', 'Позиції оновлено.');
    }

    public function togglePrivacy(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $oldValue = $ministry->is_private;
        $ministry->update([
            'is_private' => !$ministry->is_private,
        ]);

        // Log privacy toggle
        $this->logAuditAction('privacy_toggled', 'Ministry', $ministry->id, $ministry->name, [
            'is_private' => $ministry->is_private,
        ], [
            'is_private' => $oldValue,
        ]);

        return response()->json([
            'success' => true,
            'is_private' => $ministry->is_private,
        ]);
    }

    public function updateVisibility(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        $validated = $request->validate([
            'visibility' => 'required|in:public,members,leaders,specific',
            'allowed_person_ids' => 'nullable|array',
            'allowed_person_ids.*' => 'integer|exists:people,id',
        ]);

        $oldVisibility = $ministry->visibility;
        $oldAllowedIds = $ministry->allowed_person_ids;

        $ministry->update([
            'visibility' => $validated['visibility'],
            'allowed_person_ids' => $validated['allowed_person_ids'] ?? [],
            // Also update is_private for backwards compatibility
            'is_private' => $validated['visibility'] !== 'public',
        ]);

        // Log visibility update
        $this->logAuditAction('visibility_updated', 'Ministry', $ministry->id, $ministry->name, [
            'visibility' => $validated['visibility'],
            'allowed_person_ids' => $validated['allowed_person_ids'] ?? [],
        ], [
            'visibility' => $oldVisibility,
            'allowed_person_ids' => $oldAllowedIds,
        ]);

        return response()->json([
            'success' => true,
            'visibility' => $ministry->visibility,
            'allowed_person_ids' => $ministry->allowed_person_ids,
        ]);
    }

    public function storeWorshipRole(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = WorshipRole::where('church_id', $this->getCurrentChurch()->id)->max('sort_order') ?? 0;

        $role = WorshipRole::create([
            'church_id' => $this->getCurrentChurch()->id,
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $role->id]);
        }

        return back()->with('success', 'Роль додано');
    }

    public function updateWorshipRole(Request $request, Ministry $ministry, WorshipRole $role)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($role);
        Gate::authorize('manage-ministry', $ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $role->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Роль оновлено');
    }

    public function destroyWorshipRole(Request $request, Ministry $ministry, WorshipRole $role)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($role);
        Gate::authorize('manage-ministry', $ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $role->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Роль видалено');
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
