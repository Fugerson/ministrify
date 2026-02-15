<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardCardActivity;
use App\Models\BoardCardChecklistItem;
use App\Models\BoardCardComment;
use App\Models\BoardColumn;
use App\Models\BoardEpic;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    // Task Tracker - Single board for all church tasks
    public function index(Request $request)
    {
        if (!auth()->user()->canView('boards')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
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
        $mainColumnsByPosition = $board->columns->keyBy('position');
        $generalCards = BoardCard::where('show_in_general', true)
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

        // Get epic stats via single GROUP BY query
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

    // Kanban board for a specific ministry
    public function show(Board $board)
    {
        if (!auth()->user()->canView('boards')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        // Church-wide board — redirect to index
        if (!$board->ministry_id) {
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
            'description' => 'nullable|string',
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

        return redirect()->route('boards.index')
            ->with('success', 'Дошку створено.');
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
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ]);

        $board->update($validated);

        return redirect()->route('boards.show', $board)
            ->with('success', 'Дошку оновлено.');
    }

    public function destroy(Board $board)
    {
        $this->authorizeBoard($board);
        $board->delete();

        return redirect()->route('boards.index')->with('success', 'Дошку видалено.');
    }

    public function archive(Board $board)
    {
        $this->authorizeBoard($board);
        $board->update(['is_archived' => true]);

        return redirect()->route('boards.index')
            ->with('success', 'Дошку архівовано.');
    }

    public function archived()
    {
        $church = $this->getCurrentChurch();
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', true)
            ->get();

        return view('boards.archived', compact('boards'));
    }

    public function restore(Board $board)
    {
        $this->authorizeBoard($board);
        $board->update(['is_archived' => false]);

        return redirect()->route('boards.index')
            ->with('success', 'Дошку відновлено.');
    }

    // Column Management
    public function storeColumn(Request $request, Board $board)
    {
        $this->authorizeBoard($board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $maxPosition = $board->columns()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $board->columns()->create($validated);

        return redirect()->route('boards.show', $board)
            ->with('success', 'Колонку додано.');
    }

    public function updateColumn(Request $request, BoardColumn $column)
    {
        $this->authorizeBoard($column->board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'card_limit' => 'nullable|integer|min:1',
        ]);

        $column->update($validated);

        return redirect()->route('boards.show', $column->board)
            ->with('success', 'Колонку оновлено.');
    }

    public function destroyColumn(BoardColumn $column)
    {
        $this->authorizeBoard($column->board);
        $board = $column->board;

        if ($column->cards()->count() > 0) {
            return back()->with('error', 'Неможливо видалити колонку з картками.');
        }

        $column->delete();

        return redirect()->route('boards.show', $board)
            ->with('success', 'Колонку видалено.');
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
            if (!in_array($columnId, $boardColumnIds)) {
                abort(403, 'Колонка не належить цій дошці.');
            }
            BoardColumn::where('id', $columnId)->update(['position' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // Card Management
    public function storeCard(Request $request, BoardColumn $column)
    {
        $this->authorizeBoard($column->board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => ['nullable', new \App\Rules\BelongsToChurch(\App\Models\Person::class)],
            'epic_id' => ['nullable', \Illuminate\Validation\Rule::exists('board_epics', 'id')->where('board_id', $column->board_id)],
            'event_id' => ['nullable', new \App\Rules\BelongsToChurch(\App\Models\Event::class)],
            'ministry_id' => ['nullable', new \App\Rules\BelongsToChurch(\App\Models\Ministry::class)],
            'group_id' => ['nullable', new \App\Rules\BelongsToChurch(\App\Models\Group::class)],
            'person_id' => ['nullable', new \App\Rules\BelongsToChurch(\App\Models\Person::class)],
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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'card' => $card,
                'message' => 'Завдання додано.'
            ]);
        }

        return redirect()->route('boards.index')
            ->with('success', 'Завдання додано.');
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
        if (!$board) {
            return back()->with('error', 'Дошку не знайдено.');
        }
        $this->authorizeBoard($board);

        // Get the first column (typically "To Do")
        $column = $board->columns()->orderBy('position')->first();
        if (!$column) {
            return back()->with('error', 'Дошка не має колонок.');
        }

        // Get entity details
        $entityData = $this->getEntityData($validated['entity_type'], $validated['entity_id'], $church->id);
        if (!$entityData) {
            return back()->with('error', 'Об\'єкт не знайдено.');
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

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'card' => $card]);
        }

        return redirect()->route('boards.show', $board)
            ->with('success', 'Картку створено з ' . $this->getEntityTypeLabel($validated['entity_type']) . '.');
    }

    private function getEntityData(string $type, int $id, int $churchId): ?array
    {
        return match($type) {
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
        if (!$event) return null;

        $dateStr = $event->date ? $event->date->format('d.m.Y') : '';
        $timeStr = $event->time ? $event->time->format('H:i') : '';
        $fullDateTime = trim("{$dateStr} {$timeStr}");

        return [
            'title' => "Підготовка: {$event->title}",
            'description' => "Подія: {$event->title}\nДата: {$fullDateTime}\n\n{$event->description}",
            'priority' => $event->date && $event->date->isBefore(now()->addDays(3)) ? 'high' : 'medium',
            'due_date' => $event->date ? $event->date->subDay() : now(),
        ];
    }

    private function getMinistryData(int $id, int $churchId): ?array
    {
        $ministry = Ministry::where('id', $id)->where('church_id', $churchId)->first();
        if (!$ministry) return null;

        return [
            'title' => "Завдання: {$ministry->name}",
            'description' => "Служіння: {$ministry->name}\n\n{$ministry->description}",
            'priority' => 'medium',
            'due_date' => null,
        ];
    }

    private function getGroupData(int $id, int $churchId): ?array
    {
        $group = Group::where('id', $id)->where('church_id', $churchId)->first();
        if (!$group) return null;

        return [
            'title' => "Завдання: {$group->name}",
            'description' => "Група: {$group->name}\n\n{$group->description}",
            'priority' => 'medium',
            'due_date' => null,
        ];
    }

    private function getPersonData(int $id, int $churchId): ?array
    {
        $person = Person::where('id', $id)->where('church_id', $churchId)->first();
        if (!$person) return null;

        return [
            'title' => "Завдання: {$person->full_name}",
            'description' => "Особа: {$person->full_name}\nТелефон: {$person->phone}\nEmail: {$person->email}",
            'priority' => 'medium',
            'due_date' => null,
        ];
    }

    private function getEntityTypeLabel(string $type): string
    {
        return match($type) {
            'event' => 'події',
            'ministry' => 'служіння',
            'group' => 'групи',
            'person' => 'особи',
            default => 'об\'єкту',
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
        $this->authorizeBoard($card->column->board);

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
            $availableCards = BoardCard::whereHas('column.board', fn($q) => $q->where('church_id', $church->id))
                ->whereNotIn('id', $relatedIds)
                ->with('column')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'card' => $card,
                'column_name' => $card->column->name,
                'columns' => $card->column->board->columns,
                'comments' => $card->comments->map(fn($c) => [
                    'id' => $c->id,
                    'content' => $c->content,
                    'user_name' => $c->user->name,
                    'user_initial' => mb_substr($c->user->name, 0, 1),
                    'created_at' => $c->created_at->diffForHumans(),
                    'created_at_full' => $c->created_at->format('d.m.Y H:i'),
                    'updated_at' => $c->updated_at->diffForHumans(),
                    'is_edited' => $c->created_at->ne($c->updated_at),
                    'is_mine' => $c->user_id === auth()->id(),
                    'attachments' => $c->attachments ? collect($c->attachments)->map(fn($a) => [
                        'name' => $a['name'],
                        'url' => \Storage::url($a['path']),
                        'mime_type' => $a['mime_type'],
                        'is_image' => str_starts_with($a['mime_type'], 'image/'),
                    ])->toArray() : [],
                ]),
                'checklist' => $card->checklistItems->map(fn($i) => [
                    'id' => $i->id,
                    'title' => $i->title,
                    'is_completed' => $i->is_completed,
                ]),
                'checklist_progress' => $card->checklist_progress,
                'attachments' => $card->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'size' => $a->size_for_humans,
                    'mime_type' => $a->mime_type,
                    'url' => \Storage::url($a->path),
                    'uploader' => $a->uploader?->name,
                    'created_at' => $a->created_at->diffForHumans(),
                    'is_image' => str_starts_with($a->mime_type, 'image/'),
                ]),
                'related_cards' => $allRelated->map(fn($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'column_name' => $r->column->name,
                    'is_completed' => $r->is_completed,
                ]),
                'available_cards' => $availableCards->map(fn($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'column_name' => $c->column->name,
                ]),
                'people' => $people->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->full_name,
                    'initial' => mb_substr($p->first_name, 0, 1),
                ]),
                'activities' => $card->activities->take(50)->map(fn($a) => [
                    'id' => $a->id,
                    'action' => $a->action,
                    'field' => $a->field,
                    'old_value' => $a->old_value,
                    'new_value' => $a->new_value,
                    'description' => $a->description,
                    'user_name' => $a->user->name ?? 'Система',
                    'user_initial' => mb_substr($a->user->name ?? 'С', 0, 1),
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
        $this->authorizeBoard($card->column->board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => ['nullable', new \App\Rules\BelongsToChurch(\App\Models\Person::class)],
            'epic_id' => ['nullable', \Illuminate\Validation\Rule::exists('board_epics', 'id')->where('board_id', $card->column->board_id)],
            'column_id' => 'nullable|exists:board_columns,id',
            'show_in_general' => 'nullable|boolean',
        ]);

        // Verify column belongs to the same board if provided
        if (!empty($validated['column_id'])) {
            $targetColumn = BoardColumn::find($validated['column_id']);
            if (!$targetColumn || $targetColumn->board_id !== $card->column->board_id) {
                abort(403, 'Колонка не належить цій дошці.');
            }
        }

        // Track changes for activity log
        $changes = [];
        foreach (['title', 'description', 'priority', 'due_date', 'assigned_to', 'epic_id', 'column_id'] as $field) {
            if (array_key_exists($field, $validated) && $card->$field != $validated[$field]) {
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

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('boards.show', $card->column->board)
            ->with('success', 'Картку оновлено.');
    }

    public function destroyCard(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);
        $board = $card->column->board;
        $card->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Картку видалено.');
    }

    public function moveCard(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        $validated = $request->validate([
            'column_id' => 'required|exists:board_columns,id',
            'position' => 'required|integer|min:0',
        ]);

        // Verify target column belongs to the same board
        $targetColumn = BoardColumn::find($validated['column_id']);
        if (!$targetColumn || $targetColumn->board_id !== $card->column->board_id) {
            abort(403, 'Колонка не належить цій дошці.');
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

        return response()->json(['success' => true]);
    }

    public function toggleCardComplete(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        if ($card->is_completed) {
            $card->markAsIncomplete();
            BoardCardActivity::log($card, 'reopened');
        } else {
            $card->markAsCompleted();
            BoardCardActivity::log($card, 'completed');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $card->is_completed,
            ]);
        }

        return back()->with('success', 'Статус картки оновлено.');
    }

    // Duplicate card
    public function duplicateCard(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        // Create duplicate
        $newCard = $card->replicate(['is_completed', 'completed_at']);
        $newCard->title = $card->title . ' (копія)';
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'card' => $newCard->load(['column', 'epic']),
                'message' => 'Картку здубльовано',
            ]);
        }

        return redirect()->route('boards.index')->with('success', 'Картку здубльовано.');
    }

    // Card Comments
    public function storeComment(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        $validated = $request->validate([
            'content' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip,csv',
        ]);

        // Must have either content or files
        $content = $validated['content'] ?? null;
        if (empty($content) && !$request->hasFile('files')) {
            return response()->json(['error' => 'Потрібен текст або файл'], 422);
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
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        // Log activity
        BoardCardActivity::log($card, 'comment_added', null, null, null, [
            'comment_id' => $comment->id,
            'preview' => \Str::limit($validated['content'] ?? '', 50),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_name' => auth()->user()->name,
                    'user_initial' => mb_substr(auth()->user()->name, 0, 1),
                    'created_at' => $comment->created_at->diffForHumans(),
                    'is_mine' => true,
                    'attachments' => $comment->attachments ? collect($comment->attachments)->map(fn($a) => [
                        'name' => $a['name'],
                        'url' => \Storage::url($a['path']),
                        'mime_type' => $a['mime_type'],
                        'is_image' => str_starts_with($a['mime_type'], 'image/'),
                    ])->toArray() : [],
                ],
            ]);
        }

        return back()->with('success', 'Коментар додано.');
    }

    public function destroyComment(Request $request, BoardCardComment $comment)
    {
        $this->authorizeBoard($comment->card->column->board);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $card = $comment->card;
        $commentContent = $comment->content;
        $comment->delete();

        // Log activity
        BoardCardActivity::log($card, 'comment_deleted', null, \Str::limit($commentContent, 50));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Коментар видалено.');
    }

    // Card Checklist
    public function storeChecklistItem(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $maxPosition = $card->checklistItems()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $item = $card->checklistItems()->create($validated);

        // Log activity
        BoardCardActivity::log($card, 'checklist_added', null, null, $validated['title']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'is_completed' => false,
                ],
            ]);
        }

        return back()->with('success', 'Пункт додано.');
    }

    public function toggleChecklistItem(Request $request, BoardCardChecklistItem $item)
    {
        $this->authorizeBoard($item->card->column->board);
        $wasCompleted = $item->is_completed;
        $item->toggle();

        // Log activity
        $action = $item->is_completed ? 'checklist_completed' : 'checklist_uncompleted';
        BoardCardActivity::log($item->card, $action, null, null, null, ['title' => $item->title]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $item->is_completed,
            ]);
        }

        return back();
    }

    public function destroyChecklistItem(Request $request, BoardCardChecklistItem $item)
    {
        $this->authorizeBoard($item->card->column->board);
        $card = $item->card;
        $title = $item->title;
        $item->delete();

        // Log activity
        BoardCardActivity::log($card, 'checklist_deleted', null, $title);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Пункт видалено.');
    }

    // Update comment
    public function updateComment(Request $request, BoardCardComment $comment)
    {
        $this->authorizeBoard($comment->card->column->board);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'files.*' => 'file|max:10240',
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
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        $comment->update([
            'content' => $newContent,
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        // Log activity
        BoardCardActivity::log($comment->card, 'comment_edited', null, $oldContent, $newContent, [
            'comment_id' => $comment->id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
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
                        'size' => isset($att['size']) ? number_format($att['size'] / 1024, 1) . ' KB' : '',
                        'is_image' => str_starts_with($att['mime'] ?? '', 'image/'),
                    ];
                })->toArray(),
            ]);
        }

        return back()->with('success', 'Коментар оновлено.');
    }

    // Delete a single attachment from a comment
    public function deleteCommentAttachment(Request $request, BoardCardComment $comment, int $index)
    {
        $this->authorizeBoard($comment->card->column->board);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $attachments = $comment->attachments ?? [];

        if (!isset($attachments[$index])) {
            return response()->json(['error' => 'Вкладення не знайдено'], 404);
        }

        // Delete file from storage
        $attachment = $attachments[$index];
        if (!empty($attachment['path'])) {
            \Storage::disk('public')->delete($attachment['path']);
        }

        // Remove from array
        array_splice($attachments, $index, 1);
        $comment->update(['attachments' => !empty($attachments) ? $attachments : null]);

        return response()->json([
            'success' => true,
            'attachments' => collect($attachments)->map(fn($a) => [
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
        $this->authorizeBoard($card->column->board);

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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
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

        return back()->with('success', 'Файл завантажено.');
    }

    public function destroyAttachment(Request $request, \App\Models\BoardCardAttachment $attachment)
    {
        $this->authorizeBoard($attachment->card->column->board);

        $card = $attachment->card;
        $fileName = $attachment->name;

        \Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        // Log activity
        BoardCardActivity::log($card, 'attachment_deleted', null, $fileName);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Файл видалено.');
    }

    // Related cards
    public function addRelatedCard(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

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
            abort(403, 'Картка належить іншій дошці.');
        }

        // Check if already related
        $exists = $card->relatedCards()->where('related_card_id', $validated['related_card_id'])->exists()
            || $card->relatedFrom()->where('card_id', $validated['related_card_id'])->exists();

        if (!$exists) {
            $card->relatedCards()->attach($validated['related_card_id'], ['relation_type' => 'related']);

            // Log activity
            BoardCardActivity::log($card, 'related_added', null, null, $relatedCard->title);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'related_card' => [
                    'id' => $relatedCard->id,
                    'title' => $relatedCard->title,
                    'column_name' => $relatedCard->column->name,
                    'is_completed' => $relatedCard->is_completed,
                ],
            ]);
        }

        return back()->with('success', 'Картку пов\'язано.');
    }

    public function removeRelatedCard(Request $request, BoardCard $card, BoardCard $relatedCard)
    {
        $this->authorizeBoard($card->column->board);

        $relatedTitle = $relatedCard->title;
        $card->relatedCards()->detach($relatedCard->id);
        $card->relatedFrom()->detach($relatedCard->id);

        // Log activity
        BoardCardActivity::log($card, 'related_removed', null, $relatedTitle);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Зв\'язок видалено.');
    }

    private function authorizeBoard(Board $board): void
    {
        if ($board->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if (!$board->canAccess(auth()->user())) {
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
            'description' => 'nullable|string',
        ]);

        $maxPosition = $board->epics()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;

        $epic = $board->epics()->create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'epic' => $epic,
            ]);
        }

        return redirect()->route('boards.index')->with('success', 'Епік створено.');
    }

    public function updateEpic(Request $request, BoardEpic $epic)
    {
        $this->authorizeBoard($epic->board);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        $epic->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'epic' => $epic]);
        }

        return redirect()->route('boards.index')->with('success', 'Епік оновлено.');
    }

    public function destroyEpic(BoardEpic $epic)
    {
        $this->authorizeBoard($epic->board);

        // Remove epic from all cards (don't delete cards)
        BoardCard::where('epic_id', $epic->id)->update(['epic_id' => null]);

        $epic->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('boards.index')->with('success', 'Епік видалено.');
    }
}
