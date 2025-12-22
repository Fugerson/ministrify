<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardCardChecklistItem;
use App\Models\BoardCardComment;
use App\Models\BoardColumn;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    // Board Management
    public function index()
    {
        $church = $this->getCurrentChurch();
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', false)
            ->withCount(['columns', 'cards'])
            ->with(['columns.cards', 'columns'])
            ->get();

        $archivedCount = Board::where('church_id', $church->id)
            ->where('is_archived', true)
            ->count();

        return view('boards.index', compact('boards', 'archivedCount'));
    }

    public function create()
    {
        return view('boards.create');
    }

    public function store(Request $request)
    {
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

        return redirect()->route('boards.show', $board)
            ->with('success', 'Дошку створено.');
    }

    public function show(Board $board)
    {
        $this->authorizeBoard($board);

        $board->load([
            'columns.cards.assignee',
            'columns.cards.checklistItems',
            'columns.cards.comments',
        ]);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();

        return view('boards.show', compact('board', 'people'));
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

        return redirect()->route('boards.index')
            ->with('success', 'Дошку видалено.');
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

        foreach ($positions['positions'] as $index => $columnId) {
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
            'assigned_to' => 'nullable|exists:people,id',
            'event_id' => 'nullable|exists:events,id',
            'ministry_id' => 'nullable|exists:ministries,id',
            'group_id' => 'nullable|exists:groups,id',
            'person_id' => 'nullable|exists:people,id',
            'entity_type' => 'nullable|in:event,ministry,group,person',
        ]);

        $maxPosition = $column->cards()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;
        $validated['created_by'] = auth()->id();

        $column->cards()->create($validated);

        return redirect()->route('boards.show', $column->board)
            ->with('success', 'Картку додано.');
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
            'title' => $validated['title'] ?: $entityData['title'],
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

        return [
            'title' => "Підготовка: {$event->title}",
            'description' => "Подія: {$event->title}\nДата: {$event->start_date->format('d.m.Y H:i')}\n\n{$event->description}",
            'priority' => $event->start_date->isBefore(now()->addDays(3)) ? 'high' : 'medium',
            'due_date' => $event->start_date->subDay(),
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

        $query->where($validated['entity_type'] . '_id', $validated['entity_id']);

        $cards = $query->with(['column.board', 'assignee'])->get();

        return response()->json($cards);
    }

    public function showCard(BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        $card->load([
            'column.board',
            'assignee',
            'creator',
            'comments.user',
            'attachments',
            'checklistItems',
        ]);

        $church = $this->getCurrentChurch();
        $people = Person::where('church_id', $church->id)->get();
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
            'assigned_to' => 'nullable|exists:people,id',
            'column_id' => 'nullable|exists:board_columns,id',
        ]);

        $card->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('boards.show', $card->column->board)
            ->with('success', 'Картку оновлено.');
    }

    public function destroyCard(BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);
        $board = $card->column->board;
        $card->delete();

        return redirect()->route('boards.show', $board)
            ->with('success', 'Картку видалено.');
    }

    public function moveCard(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        $validated = $request->validate([
            'column_id' => 'required|exists:board_columns,id',
            'position' => 'required|integer|min:0',
        ]);

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

        return response()->json(['success' => true]);
    }

    public function toggleCardComplete(BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        if ($card->is_completed) {
            $card->markAsIncomplete();
        } else {
            $card->markAsCompleted();
        }

        return back()->with('success', 'Статус картки оновлено.');
    }

    // Card Comments
    public function storeComment(Request $request, BoardCard $card)
    {
        $this->authorizeBoard($card->column->board);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $card->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Коментар додано.');
    }

    public function destroyComment(BoardCardComment $comment)
    {
        $this->authorizeBoard($comment->card->column->board);

        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $comment->delete();

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

        $card->checklistItems()->create($validated);

        return back()->with('success', 'Пункт додано.');
    }

    public function toggleChecklistItem(BoardCardChecklistItem $item)
    {
        $this->authorizeBoard($item->card->column->board);
        $item->toggle();

        return back();
    }

    public function destroyChecklistItem(BoardCardChecklistItem $item)
    {
        $this->authorizeBoard($item->card->column->board);
        $item->delete();

        return back()->with('success', 'Пункт видалено.');
    }

    private function authorizeBoard(Board $board): void
    {
        if ($board->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
