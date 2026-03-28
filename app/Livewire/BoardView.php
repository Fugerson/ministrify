<?php

namespace App\Livewire;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardColumn;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BoardView extends Component
{
    public Board $board;

    public string $search = '';

    public string $filterEpic = '';

    public string $filterPriority = '';

    public string $filterAssignee = '';

    // New card form
    public string $newCardTitle = '';

    public ?int $newCardColumnId = null;

    public bool $showNewCardForm = false;

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    public function moveCard(int $cardId, int $targetColumnId, int $position): void
    {
        $card = BoardCard::where('board_id', $this->board->id)->findOrFail($cardId);
        $targetColumn = BoardColumn::where('board_id', $this->board->id)->findOrFail($targetColumnId);

        DB::transaction(function () use ($card, $targetColumnId, $position) {
            // Shift cards in target column
            BoardCard::where('column_id', $targetColumnId)
                ->where('sort_order', '>=', $position)
                ->increment('sort_order');

            $card->update([
                'column_id' => $targetColumnId,
                'sort_order' => $position,
            ]);
        });
    }

    public function createCard(): void
    {
        $this->validate([
            'newCardTitle' => 'required|string|max:255',
            'newCardColumnId' => 'required|exists:board_columns,id',
        ]);

        $maxOrder = BoardCard::where('column_id', $this->newCardColumnId)->max('sort_order') ?? 0;

        BoardCard::create([
            'board_id' => $this->board->id,
            'column_id' => $this->newCardColumnId,
            'title' => $this->newCardTitle,
            'sort_order' => $maxOrder + 1,
            'created_by' => auth()->id(),
        ]);

        $this->newCardTitle = '';
        $this->showNewCardForm = false;
    }

    public function toggleCardComplete(int $cardId): void
    {
        $card = BoardCard::where('board_id', $this->board->id)->findOrFail($cardId);
        $card->update(['is_completed' => ! $card->is_completed]);
    }

    public function deleteCard(int $cardId): void
    {
        BoardCard::where('board_id', $this->board->id)->findOrFail($cardId)->delete();
    }

    public function createColumn(string $name, string $color = '#6b7280'): void
    {
        $maxOrder = BoardColumn::where('board_id', $this->board->id)->max('sort_order') ?? 0;

        BoardColumn::create([
            'board_id' => $this->board->id,
            'name' => $name,
            'color' => $color,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    public function render(): View
    {
        $board = $this->board->load([
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'columns.cards' => fn ($q) => $q->orderBy('sort_order'),
            'columns.cards.assignee',
            'columns.cards.epic',
            'columns.cards.checklistItems',
            'columns.cards.comments',
        ]);

        $people = Person::where('church_id', $board->church_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'photo']);

        $epics = $board->epics()->get();

        return view('livewire.board-view', compact('board', 'people', 'epics'));
    }
}
