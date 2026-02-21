<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\EventSong;
use App\Models\Ministry;
use App\Models\MinistryRole;
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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Команду створено!',
                'redirect_url' => route('ministries.show', $ministry),
            ]);
        }

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

        // Load songs for worship ministries
        $songs = [];
        if ($ministry->is_worship_ministry) {
            $songs = Song::where('church_id', $church->id)
                ->orderBy('title')
                ->get();
        }

        // Load schedule events and ministry roles for worship or sunday service part ministries
        $scheduleEvents = collect();
        $ministryRoles = collect();
        if ($ministry->is_worship_ministry || $ministry->is_sunday_service_part) {
            $scheduleEventsQuery = Event::where('church_id', $church->id)
                ->where(function ($q) use ($ministry) {
                    $q->where('service_type', 'sunday_service')
                      ->orWhere('ministry_id', $ministry->id);
                })
                ->orderBy('date')
                ->orderBy('time');

            if ($ministry->is_worship_ministry) {
                $scheduleEventsQuery->withCount(['songs as songs_count', 'ministryTeams as team_count' => function ($q) use ($ministry) {
                    $q->where('ministry_id', $ministry->id);
                }]);
            } else {
                $scheduleEventsQuery->withCount(['ministryTeams as team_count' => function ($q) use ($ministry) {
                    $q->where('ministry_id', $ministry->id);
                }]);
            }

            $scheduleEvents = $scheduleEventsQuery->get();
            $ministryRoles = $ministry->ministryRoles()->orderBy('sort_order')->get();
        }

        // Get or create ministry board
        $ministryBoard = Board::firstOrCreate(
            ['church_id' => $church->id, 'ministry_id' => $ministry->id],
            [
                'name' => $ministry->name,
                'color' => $ministry->color ?? '#3b82f6',
                'is_archived' => false,
            ]
        );

        // Ensure default columns exist
        if ($ministryBoard->columns()->count() === 0) {
            $defaultColumns = [
                ['name' => 'До виконання', 'color' => 'gray', 'position' => 0],
                ['name' => 'В процесі', 'color' => 'blue', 'position' => 1],
                ['name' => 'На перевірці', 'color' => 'yellow', 'position' => 2],
                ['name' => 'Завершено', 'color' => 'green', 'position' => 3],
            ];
            foreach ($defaultColumns as $column) {
                $ministryBoard->columns()->create($column);
            }
        }

        // Migrate cards from main board to ministry board (one-time)
        if ($ministryBoard->cards()->count() === 0) {
            $mainBoard = Board::where('church_id', $church->id)
                ->where('name', 'Трекер завдань')
                ->first();

            if ($mainBoard) {
                $mainColumns = $mainBoard->columns()->orderBy('position')->pluck('id')->toArray();
                $ministryColumns = $ministryBoard->columns()->orderBy('position')->pluck('id')->toArray();

                foreach ($mainColumns as $idx => $mainColId) {
                    $targetColId = $ministryColumns[$idx] ?? end($ministryColumns);

                    BoardCard::where('column_id', $mainColId)
                        ->where('ministry_id', $ministry->id)
                        ->update(['column_id' => $targetColId]);
                }
            }
        }

        $ministryBoard->load([
            'columns.cards.assignee',
            'columns.cards.ministry',
            'columns.cards.epic',
            'columns.cards.checklistItems',
            'columns.cards.comments',
            'epics',
            'ministry',
        ]);

        $boardPeople = Person::where('church_id', $church->id)->orderBy('first_name')->get();
        $boardMinistries = collect([$ministry]);

        $columnIds = $ministryBoard->columns->pluck('id')->toArray();
        $epicStatsRaw = BoardCard::whereIn('column_id', $columnIds)
            ->whereNotNull('epic_id')
            ->selectRaw('epic_id, COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            ->groupBy('epic_id')
            ->get()
            ->keyBy('epic_id');

        $boardEpics = $ministryBoard->epics->map(function ($epic) use ($epicStatsRaw) {
            $stat = $epicStatsRaw[$epic->id] ?? null;
            $total = $stat ? (int) $stat->total : 0;
            $completed = $stat ? (int) $stat->completed : 0;
            return [
                'id' => $epic->id,
                'name' => $epic->name,
                'color' => $epic->color,
                'description' => $epic->description,
                'total' => $total,
                'completed' => $completed,
                'progress' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        });

        return view('ministries.show', compact('ministry', 'tab', 'boards', 'availablePeople', 'resources', 'currentFolder', 'breadcrumbs', 'registeredUsers', 'goalsStats', 'songs', 'scheduleEvents', 'ministryRoles', 'ministryBoard', 'boardPeople', 'boardMinistries', 'boardEpics'));
    }

    public function scheduleGridData(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('view-ministry', $ministry);

        $church = $this->getCurrentChurch();

        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $monthNames = ['', 'січ', 'лют', 'бер', 'кві', 'тра', 'чер', 'лип', 'сер', 'вер', 'жов', 'лис', 'гру'];

        $rawEvents = Event::where('church_id', $church->id)
            ->where('service_type', 'sunday_service')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $events = $rawEvents->map(fn($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'date' => $e->date->format('Y-m-d'),
            'dateLabel' => $e->date->format('j') . ' ' . $monthNames[$e->date->month],
            'dayOfWeek' => mb_substr($e->date->translatedFormat('D'), 0, 2),
            'dataUrl' => route('ministries.worship-events.data', [$ministry, $e]),
            'eventUrl' => route('ministries.worship-events.show', [$ministry, $e]),
            'time' => $e->time?->format('H:i') ?? '',
            'fullDate' => $e->date->translatedFormat('l, j M'),
            'isSundayService' => true,
        ]);

        $roles = $ministry->ministryRoles()->orderBy('sort_order')->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'icon' => $r->icon,
            ]);

        $eventIds = $events->pluck('id')->toArray();

        $teamEntries = EventMinistryTeam::whereIn('event_id', $eventIds)
            ->where('ministry_id', $ministry->id)
            ->with('person')
            ->get();

        $grid = [];
        $seen = []; // track person_id per role+event to skip duplicates
        foreach ($teamEntries as $entry) {
            $roleId = (string) $entry->ministry_role_id;
            $eventId = (string) $entry->event_id;
            $key = $roleId . '-' . $eventId . '-' . $entry->person_id;

            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            if (!isset($grid[$roleId])) {
                $grid[$roleId] = [];
            }
            if (!isset($grid[$roleId][$eventId])) {
                $grid[$roleId][$eventId] = [];
            }

            $person = $entry->person;
            $personName = $person
                ? $person->first_name . ' ' . mb_substr($person->last_name, 0, 1) . '.'
                : '?';

            $grid[$roleId][$eventId][] = [
                'id' => $entry->id,
                'person_id' => $entry->person_id,
                'person_name' => $personName,
                'status' => $entry->status,
                'has_telegram' => (bool) $person?->telegram_chat_id,
            ];
        }

        $members = $ministry->members()->orderBy('last_name')->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->full_name,
                'has_telegram' => (bool) $m->telegram_chat_id,
            ]);

        // Songs per event
        $songs = [];
        if (count($eventIds) > 0) {
            $eventSongs = EventSong::whereIn('event_id', $eventIds)
                ->with('song')
                ->orderBy('order')
                ->get();

            foreach ($eventSongs as $es) {
                $eId = (string) $es->event_id;
                if (!isset($songs[$eId])) {
                    $songs[$eId] = [];
                }
                $songs[$eId][] = [
                    'title' => $es->song?->title ?? '?',
                    'key' => $es->key,
                ];
            }
        }

        // Add counts to events
        $teamByEvent = [];
        foreach ($teamEntries as $entry) {
            $eId = (string) $entry->event_id;
            $teamByEvent[$eId] = ($teamByEvent[$eId] ?? 0) + 1;
        }

        $events = $events->map(function ($e) use ($songs, $teamByEvent) {
            $eId = (string) $e['id'];
            $e['songsCount'] = count($songs[$eId] ?? []);
            $e['teamCount'] = $teamByEvent[$eId] ?? 0;
            return $e;
        });

        $currentPersonId = auth()->user()->person?->id;

        return response()->json(compact('events', 'roles', 'grid', 'members', 'songs', 'currentPersonId'));
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
            'is_sunday_service_part' => 'boolean',
        ]);

        $validated['is_worship_ministry'] = $request->boolean('is_worship_ministry');
        $validated['is_sunday_service_part'] = $request->boolean('is_sunday_service_part');

        $ministry->update($validated);

        \App\Models\Church::clearMinistriesCache($ministry->church_id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Команду оновлено!',
            ]);
        }

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
        abort_unless($person->church_id === auth()->user()->church_id, 404);

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
        abort_unless($person->church_id === auth()->user()->church_id, 404);

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

    public function updateMemberRole(Request $request, Ministry $ministry, Person $person)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);
        abort_unless($person->church_id === auth()->user()->church_id, 404);

        $validated = $request->validate([
            'role' => 'required|in:member,co-leader,leader',
        ]);

        $newRole = $validated['role'];

        // If setting as leader, update ministry's leader_id
        if ($newRole === 'leader') {
            // Demote current leader in pivot if exists
            if ($ministry->leader_id && $ministry->leader_id !== $person->id) {
                $ministry->members()->updateExistingPivot($ministry->leader_id, ['role' => 'member']);
            }
            $ministry->update(['leader_id' => $person->id]);
        } elseif ($ministry->leader_id === $person->id) {
            // Removing leader role — clear leader_id
            $ministry->update(['leader_id' => null]);
        }

        $ministry->members()->updateExistingPivot($person->id, ['role' => $newRole]);

        return response()->json(['success' => true, 'role' => $newRole]);
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

    public function storeMinistryRole(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        if (!$ministry->is_sunday_service_part && !$ministry->is_worship_ministry) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = $ministry->ministryRoles()->max('sort_order') ?? 0;

        $role = MinistryRole::create([
            'ministry_id' => $ministry->id,
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

    public function updateMinistryRole(Request $request, Ministry $ministry, MinistryRole $role)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        if ((!$ministry->is_sunday_service_part && !$ministry->is_worship_ministry) || $role->ministry_id !== $ministry->id) {
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

    public function destroyMinistryRole(Request $request, Ministry $ministry, MinistryRole $role)
    {
        $this->authorizeChurch($ministry);
        Gate::authorize('manage-ministry', $ministry);

        if ((!$ministry->is_sunday_service_part && !$ministry->is_worship_ministry) || $role->ministry_id !== $ministry->id) {
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
