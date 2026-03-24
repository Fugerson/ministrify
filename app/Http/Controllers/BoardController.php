<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardCardActivity;
use App\Models\BoardCardAttachment;
use App\Models\BoardCardChecklistItem;
use App\Models\BoardCardComment;
use App\Models\BoardColumn;
use App\Models\BoardEpic;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Rules\BelongsToChurch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BoardController extends Controller
{
    // Task Tracker - Single board for all church tasks
    public function index(Request $request)
    {
        $this->checkPlanFeature('boards');

        if (! auth()->user()->canView('boards')) {
            return redirect()->route('dashboard')->with('error', __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();

        // Get or create the main church task tracker
        $board = Board::firstOrCreate(
            ['church_id' => $church->id, 'name' => 'Трекер завдань'],
            [
                'description' => 'Єдиний трекер для всіх завдань церкви',
                'color' => $church->primary_color ?? '#3b82f6',
                'is_archived' => false,
            ]
        );

        $this->ensureDefaultColumns($board);

        // Load board with cards and epics
        $board->load([
            'columns.cards.assignee',
            'columns.cards.ministry',
            'columns.cards.epic',
            'columns.cards.checklistItems',
            'columns.cards.comments',
            'epics',
        ]);

        // Inject ministry board cards with show_in_general into main board columns
        // Include cards with show_in_general=true OR cards belonging to epics with show_in_general=true
        $mainColumnsByPosition = $board->columns->keyBy('position');
        $generalCards = BoardCard::where(function ($q) {
            $q->where('show_in_general', true)
                ->orWhereHas('epic', function ($eq) {
                    $eq->where('show_in_general', true);
                });
        })
            ->whereHas('column.board', function ($q) use ($church) {
                $q->where('church_id', $church->id)->whereNotNull('ministry_id');
            })
            ->with(['assignee', 'ministry', 'epic', 'checklistItems', 'comments', 'column'])
            ->get();

        foreach ($generalCards as $card) {
            $cardPosition = $card->column->position;
            $targetColumn = $mainColumnsByPosition[$cardPosition] ?? $mainColumnsByPosition->first();
            if ($targetColumn) {
                $targetColumn->cards->push($card);
            }
        }

        // Get filter data
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();

        // Collect all visible epics: board's own + ministry epics visible in general
        $allEpics = $board->epics->collect();

        // Add ministry epics with show_in_general or having cards with show_in_general
        $generalEpicIds = $generalCards->pluck('epic_id')->filter()->unique();
        $ministryEpics = BoardEpic::where('show_in_general', true)
            ->whereHas('board', function ($q) use ($church) {
                $q->where('church_id', $church->id)->whereNotNull('ministry_id');
            })
            ->get();
        // Also add epics referenced by general cards (even without epic-level flag)
        if ($generalEpicIds->isNotEmpty()) {
            $cardEpics = BoardEpic::whereIn('id', $generalEpicIds)
                ->whereNotIn('id', $ministryEpics->pluck('id'))
                ->whereNotIn('id', $allEpics->pluck('id'))
                ->get();
            $ministryEpics = $ministryEpics->merge($cardEpics);
        }
        $allEpics = $allEpics->merge($ministryEpics)->unique('id');

        // Get epic stats — count from all visible cards (board + injected general)
        $allVisibleCards = $board->columns->flatMap->cards;
        $epicStatsRaw = $allVisibleCards
            ->filter(fn ($c) => $c->epic_id)
            ->groupBy('epic_id')
            ->map(fn ($cards) => (object) [
                'total' => $cards->count(),
                'completed' => $cards->where('is_completed', true)->count(),
            ]);

        $epics = $allEpics->map(function ($epic) use ($epicStatsRaw) {
            $stat = $epicStatsRaw[$epic->id] ?? null;
            $total = $stat ? (int) $stat->total : 0;
            $completed = $stat ? (int) $stat->completed : 0;

            return [
                'id' => $epic->id,
                'name' => $epic->name,
                'color' => $epic->color,
                'description' => $epic->description,
                'show_in_general' => $epic->show_in_general,
                'total' => $total,
                'completed' => $completed,
                'progress' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        })->values();

        return view('boards.index', compact('board', 'people', 'ministries', 'epics'));
    }

    // Kanban board for a specific ministry
    public function show(Board $board)
    {
        if (! auth()->user()->canView('boards')) {
            return redirect()->route('dashboard')->with('error', __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        // Church-wide board — redirect to index
        if (! $board->ministry_id) {
            return redirect()->route('boards.index');
        }

        $this->authorizeBoard($board);

        $church = $this->getCurrentChurch();

        $this->ensureDefaultColumns($board);

        $board->load([
            'columns.cards.assignee',
            'columns.cards.ministry',
            'columns.cards.epic',
            'columns.cards.checklistItems',
            'columns.cards.comments',
            'epics',
            'ministry',
        ]);

        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();
        $ministries = collect([$board->ministry]);

        $columnIds = $board->columns->pluck('id')->toArray();
        $epicStatsRaw = BoardCard::whereIn('column_id', $columnIds)
            ->whereNotNull('epic_id')
            ->selectRaw('epic_id, COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            ->groupBy('epic_id')
            ->get()
            ->keyBy('epic_id');

        $epics = $board->epics->map(function ($epic) use ($epicStatsRaw) {
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

        return view('boards.index', compact('board', 'people', 'ministries', 'epics'));
    }

    public function create()
    {
        abort_unless(auth()->user()->canCreate('boards'), 403);

        return view('boards.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canCreate('boards'), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'required|string|max:7',
        ]);

        $church = $this->getCurrentChurch();
        $validated['church_id'] = $church->id;

        $board = Board::create($validated);

        // Create default columns
        $defaultColumns = [
            ['name' => 'До виконання', 'color' => 'gray', 'position' => 0],
            ['name' => 'В процесі', 'color' => 'blue', 'position' => 1],
            ['name' => 'На перевірці', 'color' => 'yellow', 'position' => 2],
            ['name' => 'Завершено', 'color' => 'green', 'position' => 3],
        ];

        foreach ($defaultColumns as $column) {
            $board->columns()->create($column);
        }

        broadcast(new \App\Events\ChurchDataUpdated($church->id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.board_created'), 'boards.index');
    }

    public function edit(Board $board)
    {
        $this->authorizeBoard($board);

        return view('boards.edit', compact('board'));
    }

    public function update(Request $request, Board $board)
    {
        $this->authorizeBoard($board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'color' => 'required|string|max:7',
        ]);

        $board->update($validated);

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.board_updated'), 'boards.show', [$board]);
    }

    public function destroy(Request $request, Board $board)
    {
        $this->authorizeBoard($board);
        abort_unless(auth()->user()->canDelete('boards'), 403);

        $churchId = $board->church_id;
        $board->delete();

        broadcast(new \App\Events\ChurchDataUpdated($churchId, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.board_deleted'), 'boards.index');
    }

    public function archive(Request $request, Board $board)
    {
        $this->authorizeBoard($board);
        $board->update(['is_archived' => true]);

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.board_archived'), 'boards.index');
    }

    public function archived()
    {
        $church = $this->getCurrentChurch();
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', true)
            ->get();

        return view('boards.archived', compact('boards'));
    }

    public function restore(Request $request, Board $board)
    {
        $this->authorizeBoard($board);
        $board->update(['is_archived' => false]);

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.board_restored'), 'boards.index');
    }

    // Column Management
    public function storeColumn(Request $request, Board $board)
    {
        abort_unless(auth()->user()->canEdit('boards'), 403);
        $this->authorizeBoard($board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $maxPosition = $board->columns()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $board->columns()->create($validated);

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.column_added'), 'boards.show', [$board]);
    }

    public function updateColumn(Request $request, BoardColumn $column)
    {
        abort_unless(auth()->user()->canEdit('boards'), 403);
        $this->authorizeBoard($column->board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'card_limit' => 'nullable|integer|min:1',
        ]);

        $column->update($validated);

        broadcast(new \App\Events\ChurchDataUpdated($column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.column_updated'), 'boards.show', [$column->board]);
    }

    public function destroyColumn(Request $request, BoardColumn $column)
    {
        abort_unless(auth()->user()->canDelete('boards'), 403);
        $this->authorizeBoard($column->board);
        $board = $column->board;

        if ($column->cards()->count() > 0) {
            return $this->errorResponse($request, __('messages.cannot_delete_column_with_cards'));
        }

        $column->delete();

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.column_deleted'), 'boards.show', [$board]);
    }

    public function reorderColumns(Request $request, Board $board)
    {
        $this->authorizeBoard($board);

        $positions = $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'integer|exists:board_columns,id',
        ]);

        // Verify all columns belong to this board
        $boardColumnIds = $board->columns()->pluck('id')->toArray();
        foreach ($positions['positions'] as $index => $columnId) {
            if (! in_array($columnId, $boardColumnIds)) {
                abort(403, __('messages.column_not_belongs_to_board'));
            }
            BoardColumn::where('id', $columnId)->update(['position' => $index]);
        }

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return response()->json(['success' => true]);
    }

    // Card Management
    public function storeCard(Request $request, BoardColumn $column)
    {
        $this->authorizeBoard($column->board);

        if ($column->isAtLimit()) {
            return response()->json(['error' => __('messages.column_limit_reached')], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => ['nullable', new BelongsToChurch(Person::class)],
            'epic_id' => ['nullable', Rule::exists('board_epics', 'id')->whereIn('board_id', Board::where('church_id', $this->getCurrentChurch()->id)->pluck('id'))],
            'event_id' => ['nullable', new BelongsToChurch(Event::class)],
            'ministry_id' => ['nullable', new BelongsToChurch(Ministry::class)],
            'group_id' => ['nullable', new BelongsToChurch(Group::class)],
            'person_id' => ['nullable', new BelongsToChurch(Person::class)],
            'entity_type' => 'nullable|in:event,ministry,group,person',
        ]);

        $maxPosition = $column->cards()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;
        $validated['created_by'] = auth()->id();

        // Auto-set ministry for ministry boards
        if ($column->board->ministry_id && empty($validated['ministry_id'])) {
            $validated['ministry_id'] = $column->board->ministry_id;
        }

        $card = $column->cards()->create($validated);
        $card->load('epic');

        // Log activity
        BoardCardActivity::log($card, 'created');

        broadcast(new \App\Events\ChurchDataUpdated($column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.task_added'), 'boards.index', [], ['card' => $card]);
    }

    // Quick card creation from entities
    public function createFromEntity(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'entity_type' => 'required|in:event,ministry,group,person',
            'entity_id' => 'required|integer',
            'board_id' => 'required|exists:boards,id',
            'title' => 'nullable|string|max:255',
        ]);

        $board = Board::find($validated['board_id']);
        if (! $board) {
            return $this->errorResponse($request, __('messages.board_not_found'));
        }
        $this->authorizeBoard($board);

        // Get the first column (typically "To Do")
        $column = $board->columns()->orderBy('position')->first();
        if (! $column) {
            return $this->errorResponse($request, __('messages.board_has_no_columns'));
        }

        // Get entity details
        $entityData = $this->getEntityData($validated['entity_type'], $validated['entity_id'], $church->id);
        if (! $entityData) {
            return $this->errorResponse($request, __('messages.entity_not_found'));
        }

        $maxPosition = $column->cards()->max('position') ?? -1;

        $card = $column->cards()->create([
            'title' => ($validated['title'] ?? null) ?: $entityData['title'],
            'description' => $entityData['description'],
            'priority' => $entityData['priority'] ?? 'medium',
            'due_date' => $entityData['due_date'] ?? null,
            'created_by' => auth()->id(),
            'position' => $maxPosition + 1,
            'event_id' => $validated['entity_type'] === 'event' ? $validated['entity_id'] : null,
            'ministry_id' => $validated['entity_type'] === 'ministry' ? $validated['entity_id'] : null,
            'group_id' => $validated['entity_type'] === 'group' ? $validated['entity_id'] : null,
            'person_id' => $validated['entity_type'] === 'person' ? $validated['entity_id'] : null,
            'entity_type' => $validated['entity_type'],
        ]);

        broadcast(new \App\Events\ChurchDataUpdated($church->id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.card_created_from_entity', ['entity' => $this->getEntityTypeLabel($validated['entity_type'])]), 'boards.show', [$board], ['card' => $card]);
    }

    private function getEntityData(string $type, int $id, int $churchId): ?array
    {
        return match ($type) {
            'event' => $this->getEventData($id, $churchId),
            'ministry' => $this->getMinistryData($id, $churchId),
            'group' => $this->getGroupData($id, $churchId),
            'person' => $this->getPersonData($id, $churchId),
            default => null,
        };
    }

    private function getEventData(int $id, int $churchId): ?array
    {
        $event = Event::where('id', $id)->where('church_id', $churchId)->first();
        if (! $event) {
            return null;
        }

        $dateStr = $event->date ? $event->date->format('d.m.Y') : '';
        $timeStr = $event->time ? $event->time->format('H:i') : '';
        $fullDateTime = trim("{$dateStr} {$timeStr}");

        return [
            'title' => __('messages.preparation_prefix', ['title' => $event->title]),
            'description' => __('messages.event_description', ['title' => $event->title])."\n".__('messages.event_date_label', ['date' => $fullDateTime])."\n\n{$event->description}",
            'priority' => $event->date && $event->date->isBefore(now()->addDays(3)) ? 'high' : 'medium',
            'due_date' => $event->date ? $event->date->subDay() : now(),
        ];
    }

    private function getMinistryData(int $id, int $churchId): ?array
    {
        $ministry = Ministry::where('id', $id)->where('church_id', $churchId)->first();
        if (! $ministry) {
            return null;
        }

        return [
            'title' => __('messages.task_prefix', ['title' => $ministry->name]),
            'description' => __('messages.ministry_description', ['title' => $ministry->name])."\n\n{$ministry->description}",
            'priority' => 'medium',
            'due_date' => null,
        ];
    }

    private function getGroupData(int $id, int $churchId): ?array
    {
        $group = Group::where('id', $id)->where('church_id', $churchId)->first();
        if (! $group) {
            return null;
        }

        return [
            'title' => __('messages.task_prefix', ['title' => $group->name]),
            'description' => __('messages.group_description', ['title' => $group->name])."\n\n{$group->description}",
            'priority' => 'medium',
            'due_date' => null,
        ];
    }

    private function getPersonData(int $id, int $churchId): ?array
    {
        $person = Person::where('id', $id)->where('church_id', $churchId)->first();
        if (! $person) {
            return null;
        }

        return [
            'title' => __('messages.task_prefix', ['title' => $person->full_name]),
            'description' => __('messages.person_description', ['name' => $person->full_name])."\n".__('messages.phone_label', ['phone' => $person->phone])."\n".__('messages.email_label', ['email' => $person->email]),
            'priority' => 'medium',
            'due_date' => null,
        ];
    }

    private function getEntityTypeLabel(string $type): string
    {
        return match ($type) {
            'event' => __('messages.entity_type_event'),
            'ministry' => __('messages.entity_type_ministry'),
            'group' => __('messages.entity_type_group'),
            'person' => __('messages.entity_type_person'),
            default => __('messages.entity_type_default'),
        };
    }

    // Get cards linked to an entity
    public function getLinkedCards(Request $request)
    {
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'entity_type' => 'required|in:event,ministry,group,person',
            'entity_id' => 'required|integer',
        ]);

        $query = BoardCard::whereHas('column.board', function ($q) use ($church) {
            $q->where('church_id', $church->id);
        });

        // Use explicit column mapping for safety
        $columnMap = [
            'event' => 'event_id',
            'ministry' => 'ministry_id',
            'group' => 'group_id',
            'person' => 'person_id',
        ];
        $query->where($columnMap[$validated['entity_type']], $validated['entity_id']);

        $cards = $query->with(['column.board', 'assignee'])->get();

        return response()->json($cards);
    }

    public function showCard(BoardCard $card, Request $request)
    {
        $this->authorizeCardAccess($card);

        $card->load([
            'column.board.columns',
            'assignee',
            'creator',
            'comments.user',
            'attachments.uploader',
            'checklistItems',
            'ministry',
            'relatedCards.column',
            'relatedFrom.column',
            'activities.user',
        ]);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            // Get all related cards (both directions)
            $allRelated = $card->relatedCards->merge($card->relatedFrom)->unique('id');

            // Get other cards for linking (excluding current and already related)
            $relatedIds = $allRelated->pluck('id')->push($card->id)->toArray();
            $availableCards = BoardCard::whereHas('column.board', fn ($q) => $q->where('church_id', $church->id))
                ->whereNotIn('id', $relatedIds)
                ->with('column')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'card' => $card,
                'column_name' => $card->column?->name ?? '',
                'columns' => $card->column?->board?->columns ?? [],
                'comments' => $card->comments->map(fn ($c) => [
                    'id' => $c->id,
                    'content' => $c->content,
                    'user_name' => $c->user?->name ?? __('common.deleted'),
                    'user_initial' => mb_substr($c->user?->name ?? '?', 0, 1),
                    'created_at' => $c->created_at->diffForHumans(),
                    'created_at_full' => $c->created_at->format('d.m.Y H:i'),
                    'updated_at' => $c->updated_at->diffForHumans(),
                    'is_edited' => $c->created_at->ne($c->updated_at),
                    'is_mine' => $c->user_id === auth()->id(),
                    'attachments' => $c->attachments ? collect($c->attachments)->map(fn ($a) => [
                        'name' => $a['name'],
                        'url' => \Storage::url($a['path']),
                        'mime_type' => $a['mime_type'],
                        'is_image' => str_starts_with($a['mime_type'], 'image/'),
                    ])->toArray() : [],
                ]),
                'checklist' => $card->checklistItems->map(fn ($i) => [
                    'id' => $i->id,
                    'title' => $i->title,
                    'is_completed' => $i->is_completed,
                ]),
                'checklist_progress' => $card->checklist_progress,
                'attachments' => $card->attachments->map(fn ($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'size' => $a->size_for_humans,
                    'mime_type' => $a->mime_type,
                    'url' => \Storage::url($a->path),
                    'uploader' => $a->uploader?->name,
                    'created_at' => $a->created_at->diffForHumans(),
                    'is_image' => str_starts_with($a->mime_type, 'image/'),
                ]),
                'related_cards' => $allRelated->map(fn ($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'column_name' => $r->column->name,
                    'is_completed' => $r->is_completed,
                ]),
                'available_cards' => $availableCards->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'column_name' => $c->column->name,
                ]),
                'people' => $people->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->full_name,
                    'initial' => mb_substr($p->first_name, 0, 1),
                ]),
                'activities' => $card->activities->take(50)->map(fn ($a) => [
                    'id' => $a->id,
                    'action' => $a->action,
                    'field' => $a->field,
                    'old_value' => $a->old_value,
                    'new_value' => $a->new_value,
                    'description' => $a->description,
                    'user_name' => $a->user?->name ?? __('messages.system_user'),
                    'user_initial' => mb_substr($a->user?->name ?? __('messages.system_user'), 0, 1),
                    'created_at' => $a->created_at->diffForHumans(),
                    'created_at_full' => $a->created_at->format('d.m.Y H:i'),
                    'metadata' => $a->metadata,
                ]),
            ]);
        }

        $columns = $card->column->board->columns;

        return view('boards.card', compact('card', 'people', 'columns'));
    }

    public function updateCard(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => ['nullable', new BelongsToChurch(Person::class)],
            'epic_id' => ['nullable', Rule::exists('board_epics', 'id')->whereIn('board_id', Board::where('church_id', $this->getCurrentChurch()->id)->pluck('id'))],
            'column_id' => 'nullable|exists:board_columns,id',
            'show_in_general' => 'nullable|boolean',
        ]);

        // Verify column belongs to the same board if provided
        if (! empty($validated['column_id'])) {
            $targetColumn = BoardColumn::find($validated['column_id']);
            if (! $targetColumn) {
                abort(404);
            }
            // Cross-board: card from ministry board shown on main board via show_in_general
            if ($targetColumn->board_id !== $card->column->board_id) {
                $isGeneral = $card->show_in_general || ($card->epic && $card->epic->show_in_general);
                if (! $isGeneral) {
                    abort(403, __('messages.column_not_belongs_to_board'));
                }
                $matchingColumn = BoardColumn::where('board_id', $card->column->board_id)
                    ->where('position', $targetColumn->position)
                    ->first();
                if (! $matchingColumn) {
                    $matchingColumn = BoardColumn::where('board_id', $card->column->board_id)
                        ->orderBy('position')
                        ->skip($targetColumn->position)
                        ->first() ?? $card->column;
                }
                $validated['column_id'] = $matchingColumn->id;
            }
        }

        // Track changes for activity log
        $changes = [];
        foreach (['title', 'description', 'priority', 'due_date', 'assigned_to', 'epic_id', 'column_id'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] != $card->$field) {
                $oldValue = $card->$field;
                $newValue = $validated[$field];

                // Get human-readable values for relationships
                if ($field === 'column_id' && $newValue) {
                    $newColumn = BoardColumn::find($newValue);
                    $oldColumn = $card->column;
                    BoardCardActivity::log($card, 'moved', $field, $oldColumn?->name, $newColumn?->name);
                } elseif ($field === 'assigned_to') {
                    $oldPerson = $oldValue ? Person::find($oldValue) : null;
                    $newPerson = $newValue ? Person::find($newValue) : null;
                    BoardCardActivity::log($card, 'assigned', $field, $oldPerson?->full_name, $newPerson?->full_name);
                } else {
                    BoardCardActivity::log($card, 'updated', $field, $oldValue, $newValue);
                }
            }
        }

        $card->update($validated);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.card_updated'), 'boards.show', [$card->column->board]);
    }

    public function destroyCard(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);
        abort_unless(auth()->user()->canDelete('boards'), 403);
        $board = $card->column->board;
        $card->delete();

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.card_deleted'));
    }

    public function moveCard(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        $validated = $request->validate([
            'column_id' => 'required|exists:board_columns,id',
            'position' => 'required|integer|min:0',
        ]);

        // Verify target column belongs to the same board
        $targetColumn = BoardColumn::find($validated['column_id']);
        if (! $targetColumn) {
            abort(404);
        }

        // Check card limit on target column (skip if moving within same column)
        if ($targetColumn->id !== $card->column_id && $targetColumn->isAtLimit()) {
            return response()->json(['error' => __('messages.target_column_limit_reached')], 422);
        }

        // Cross-board move: card from ministry board shown on main board via show_in_general
        if ($targetColumn->board_id !== $card->column->board_id) {
            $isGeneral = $card->show_in_general || ($card->epic && $card->epic->show_in_general);
            if (! $isGeneral) {
                abort(403, __('messages.column_not_belongs_to_board'));
            }
            // Find matching column on the card's own board by position
            $matchingColumn = BoardColumn::where('board_id', $card->column->board_id)
                ->where('position', $targetColumn->position)
                ->first();
            if (! $matchingColumn) {
                $matchingColumn = BoardColumn::where('board_id', $card->column->board_id)
                    ->orderBy('position')
                    ->skip($targetColumn->position)
                    ->first() ?? $card->column;
            }
            $targetColumn = $matchingColumn;
            $validated['column_id'] = $matchingColumn->id;
        }

        $oldColumnId = $card->column_id;
        $oldColumnName = $card->column->name;

        DB::transaction(function () use ($card, $validated) {
            // Update positions in the old column
            BoardCard::where('column_id', $card->column_id)
                ->where('position', '>', $card->position)
                ->decrement('position');

            // Update positions in the new column
            BoardCard::where('column_id', $validated['column_id'])
                ->where('position', '>=', $validated['position'])
                ->increment('position');

            $card->update([
                'column_id' => $validated['column_id'],
                'position' => $validated['position'],
            ]);
        });

        // Log activity if column changed
        if ($oldColumnId != $validated['column_id']) {
            BoardCardActivity::log($card, 'moved', 'column_id', $oldColumnName, $targetColumn->name);
        }

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return response()->json(['success' => true]);
    }

    public function toggleCardComplete(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        if ($card->is_completed) {
            $card->markAsIncomplete();
            BoardCardActivity::log($card, 'reopened');
        } else {
            $card->markAsCompleted();
            BoardCardActivity::log($card, 'completed');
        }

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.card_status_updated'), null, [], ['is_completed' => $card->is_completed]);
    }

    // Duplicate card
    public function duplicateCard(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        // Create duplicate
        $newCard = $card->replicate(['is_completed', 'completed_at']);
        $newCard->title = $card->title.' '.__('messages.copy_suffix');
        $newCard->is_completed = false;
        $newCard->completed_at = null;
        $newCard->created_by = auth()->id();
        $newCard->position = $card->column->cards()->max('position') + 1;
        $newCard->save();

        // Duplicate checklist items
        foreach ($card->checklistItems as $item) {
            $newCard->checklistItems()->create([
                'title' => $item->title,
                'position' => $item->position,
                'is_completed' => false,
            ]);
        }

        // Log activity
        BoardCardActivity::log($newCard, 'created', null, null, null, [
            'duplicated_from' => $card->id,
        ]);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.card_duplicated'), 'boards.index', [], ['card' => $newCard->load(['column', 'epic'])]);
    }

    // Card Comments
    public function storeComment(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        $validated = $request->validate([
            'content' => 'nullable|string|max:10000',
            'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip,csv',
        ]);

        // Must have either content or files
        $content = $validated['content'] ?? null;
        if (empty($content) && ! $request->hasFile('files')) {
            return response()->json(['error' => __('messages.text_or_file_required')], 422);
        }

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('comment-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $comment = $card->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'] ?? '',
            'attachments' => ! empty($attachments) ? $attachments : null,
        ]);

        // Log activity
        BoardCardActivity::log($card, 'comment_added', null, null, null, [
            'comment_id' => $comment->id,
            'preview' => \Str::limit($validated['content'] ?? '', 50),
        ]);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.comment_added'), null, [], [
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_name' => auth()->user()->name,
                'user_initial' => mb_substr(auth()->user()->name, 0, 1),
                'created_at' => $comment->created_at->diffForHumans(),
                'is_mine' => true,
                'attachments' => $comment->attachments ? collect($comment->attachments)->map(fn ($a) => [
                    'name' => $a['name'],
                    'url' => \Storage::url($a['path']),
                    'mime_type' => $a['mime_type'],
                    'is_image' => str_starts_with($a['mime_type'], 'image/'),
                ])->toArray() : [],
            ],
        ]);
    }

    public function destroyComment(Request $request, BoardCardComment $comment)
    {
        $this->authorizeCardAccess($comment->card);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $card = $comment->card;
        $commentContent = $comment->content;
        $comment->delete();

        // Log activity
        BoardCardActivity::log($card, 'comment_deleted', null, \Str::limit($commentContent, 50));

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.comment_deleted'));
    }

    // Card Checklist
    public function storeChecklistItem(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $maxPosition = $card->checklistItems()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $item = $card->checklistItems()->create($validated);

        // Log activity
        BoardCardActivity::log($card, 'checklist_added', null, null, $validated['title']);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.checklist_item_added'), null, [], [
            'item' => [
                'id' => $item->id,
                'title' => $item->title,
                'is_completed' => false,
            ],
        ]);
    }

    public function toggleChecklistItem(Request $request, BoardCardChecklistItem $item)
    {
        $this->authorizeCardAccess($item->card);
        $wasCompleted = $item->is_completed;
        $item->toggle();

        // Log activity
        $action = $item->is_completed ? 'checklist_completed' : 'checklist_uncompleted';
        BoardCardActivity::log($item->card, $action, null, null, null, ['title' => $item->title]);

        broadcast(new \App\Events\ChurchDataUpdated($item->card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.checklist_item_updated'), null, [], ['is_completed' => $item->is_completed]);
    }

    public function destroyChecklistItem(Request $request, BoardCardChecklistItem $item)
    {
        $this->authorizeCardAccess($item->card);
        $card = $item->card;
        $title = $item->title;
        $item->delete();

        // Log activity
        BoardCardActivity::log($card, 'checklist_deleted', null, $title);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.checklist_item_deleted'));
    }

    // Update comment
    public function updateComment(Request $request, BoardCardComment $comment)
    {
        $this->authorizeCardAccess($comment->card);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string|max:10000',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip,csv|max:10240',
        ]);

        $oldContent = $comment->content;
        $newContent = $validated['content'] ?? $comment->content;

        // Handle file uploads
        $attachments = $comment->attachments ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('board-comments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $comment->update([
            'content' => $newContent,
            'attachments' => ! empty($attachments) ? $attachments : null,
        ]);

        // Log activity
        BoardCardActivity::log($comment->card, 'comment_edited', null, $oldContent, $newContent, [
            'comment_id' => $comment->id,
        ]);

        broadcast(new \App\Events\ChurchDataUpdated($comment->card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.comment_updated'), null, [], [
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'updated_at' => $comment->updated_at->diffForHumans(),
                'is_edited' => true,
            ],
            'attachments' => collect($comment->attachments ?? [])->map(function ($att) {
                return [
                    'name' => $att['name'],
                    'url' => \Storage::url($att['path']),
                    'size' => isset($att['size']) ? number_format($att['size'] / 1024, 1).' KB' : '',
                    'is_image' => str_starts_with($att['mime_type'] ?? '', 'image/'),
                ];
            })->toArray(),
        ]);
    }

    // Delete a single attachment from a comment
    public function deleteCommentAttachment(Request $request, BoardCardComment $comment, int $index)
    {
        $this->authorizeCardAccess($comment->card);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $attachments = $comment->attachments ?? [];

        if (! isset($attachments[$index])) {
            return response()->json(['error' => __('messages.attachment_not_found')], 404);
        }

        // Delete file from storage
        $attachment = $attachments[$index];
        if (! empty($attachment['path'])) {
            \Storage::disk('public')->delete($attachment['path']);
        }

        // Remove from array
        array_splice($attachments, $index, 1);
        $comment->update(['attachments' => ! empty($attachments) ? $attachments : null]);

        broadcast(new \App\Events\ChurchDataUpdated($comment->card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return response()->json([
            'success' => true,
            'attachments' => collect($attachments)->map(fn ($a) => [
                'name' => $a['name'],
                'url' => \Storage::url($a['path']),
                'mime_type' => $a['mime_type'],
                'is_image' => str_starts_with($a['mime_type'], 'image/'),
            ])->toArray(),
        ]);
    }

    // Attachments
    public function storeAttachment(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip,csv', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('board-attachments', 'public');

        $attachment = $card->attachments()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        // Log activity
        BoardCardActivity::log($card, 'attachment_added', null, null, $file->getClientOriginalName());

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.file_uploaded'), null, [], [
            'attachment' => [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'size' => $attachment->size_for_humans,
                'mime_type' => $attachment->mime_type,
                'url' => \Storage::url($attachment->path),
                'uploader' => auth()->user()->name,
                'created_at' => $attachment->created_at->diffForHumans(),
                'is_image' => str_starts_with($attachment->mime_type, 'image/'),
            ],
        ]);
    }

    public function destroyAttachment(Request $request, BoardCardAttachment $attachment)
    {
        $this->authorizeCardAccess($attachment->card);

        $card = $attachment->card;
        $fileName = $attachment->name;

        \Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        // Log activity
        BoardCardActivity::log($card, 'attachment_deleted', null, $fileName);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.file_deleted'));
    }

    // Related cards
    public function addRelatedCard(Request $request, BoardCard $card)
    {
        $this->authorizeCardAccess($card);

        $validated = $request->validate([
            'related_card_id' => 'required|exists:board_cards,id',
        ]);

        // Don't allow relating to self
        if ($validated['related_card_id'] == $card->id) {
            return response()->json(['error' => 'Cannot relate card to itself'], 422);
        }

        // Verify related card belongs to same board (church isolation)
        $relatedCard = BoardCard::with('column')->findOrFail($validated['related_card_id']);
        if ($relatedCard->column->board_id !== $card->column->board_id) {
            abort(403, __('messages.card_belongs_to_other_board'));
        }

        // Check if already related
        $exists = $card->relatedCards()->where('related_card_id', $validated['related_card_id'])->exists()
            || $card->relatedFrom()->where('card_id', $validated['related_card_id'])->exists();

        if (! $exists) {
            $card->relatedCards()->attach($validated['related_card_id'], ['relation_type' => 'related']);

            // Log activity
            BoardCardActivity::log($card, 'related_added', null, null, $relatedCard->title);
        }

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.card_linked'), null, [], [
            'related_card' => [
                'id' => $relatedCard->id,
                'title' => $relatedCard->title,
                'column_name' => $relatedCard->column->name,
                'is_completed' => $relatedCard->is_completed,
            ],
        ]);
    }

    public function removeRelatedCard(Request $request, BoardCard $card, BoardCard $relatedCard)
    {
        $this->authorizeCardAccess($card);

        $relatedTitle = $relatedCard->title;
        $card->relatedCards()->detach($relatedCard->id);
        $card->relatedFrom()->detach($relatedCard->id);

        // Log activity
        BoardCardActivity::log($card, 'related_removed', null, $relatedTitle);

        broadcast(new \App\Events\ChurchDataUpdated($card->column->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.link_removed'));
    }

    private function authorizeBoard(Board $board): void
    {
        if ($board->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if (! $board->canAccess(auth()->user())) {
            abort(403);
        }
    }

    /**
     * Authorize access to a card — allows access for show_in_general cards
     * even if user doesn't have access to the ministry board directly.
     */
    private function authorizeCardAccess(BoardCard $card): void
    {
        $board = $card->column->board;
        $churchId = $this->getCurrentChurch()->id;

        if ($board->church_id !== $churchId) {
            abort(404);
        }

        // Cards visible on the main board (show_in_general) are accessible to anyone in the church
        if ($card->show_in_general || ($card->epic_id && $card->epic?->show_in_general)) {
            return;
        }

        if (! $board->canAccess(auth()->user())) {
            abort(403);
        }
    }

    private function ensureDefaultColumns(Board $board): void
    {
        if ($board->columns()->count() === 0) {
            $defaultColumns = [
                ['name' => 'Нові', 'color' => 'gray', 'position' => 0],
                ['name' => 'До виконання', 'color' => 'blue', 'position' => 1],
                ['name' => 'В процесі', 'color' => 'yellow', 'position' => 2],
                ['name' => 'Завершено', 'color' => 'green', 'position' => 3],
            ];
            foreach ($defaultColumns as $column) {
                $board->columns()->create($column);
            }
        }
    }

    // Epic Management
    public function storeEpic(Request $request, Board $board)
    {
        $this->authorizeBoard($board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:2000',
            'show_in_general' => 'nullable|boolean',
        ]);

        $maxPosition = $board->epics()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $epic = $board->epics()->create($validated);

        broadcast(new \App\Events\ChurchDataUpdated($board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.epic_created'), 'boards.index', [], ['epic' => $epic]);
    }

    public function updateEpic(Request $request, BoardEpic $epic)
    {
        $this->authorizeBoard($epic->board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:2000',
            'show_in_general' => 'nullable|boolean',
        ]);

        $epic->update($validated);

        broadcast(new \App\Events\ChurchDataUpdated($epic->board->church_id, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.epic_updated'), 'boards.index', [], ['epic' => $epic]);
    }

    public function destroyEpic(Request $request, BoardEpic $epic)
    {
        $this->authorizeBoard($epic->board);

        // Remove epic from all cards (don't delete cards)
        BoardCard::where('epic_id', $epic->id)->update(['epic_id' => null]);

        $churchId = $epic->board->church_id;
        $epic->delete();

        broadcast(new \App\Events\ChurchDataUpdated($churchId, 'boards', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.epic_deleted'), 'boards.index');
    }
}
