<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardCardChecklistItem;
use App\Models\BoardCardComment;
use App\Models\BoardColumn;
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
        ]);

        $maxPosition = $column->cards()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;
        $validated['created_by'] = auth()->id();

        $column->cards()->create($validated);

        return redirect()->route('boards.show', $column->board)
            ->with('success', 'Картку додано.');
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
