<?php

namespace Tests\Unit\Models;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardCardChecklistItem;
use App\Models\BoardColumn;
use App\Models\Church;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardCardTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private BoardColumn $column;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $board = Board::create([
            'church_id' => $this->church->id,
            'name' => 'Test Board',
        ]);
        $this->column = BoardColumn::create([
            'board_id' => $board->id,
            'name' => 'To Do',
            'position' => 0,
        ]);
    }

    private function createCard(array $attrs = []): BoardCard
    {
        return BoardCard::create(array_merge([
            'column_id' => $this->column->id,
            'title' => 'Test Card',
            'position' => 0,
            'priority' => 'medium',
            'is_completed' => false,
        ], $attrs));
    }

    // ==================
    // isOverdue / isDueSoon
    // ==================

    public function test_is_overdue_when_past_due_and_not_completed(): void
    {
        $card = $this->createCard(['due_date' => now()->subDay()]);
        $this->assertTrue($card->isOverdue());
    }

    public function test_is_not_overdue_when_completed(): void
    {
        $card = $this->createCard([
            'due_date' => now()->subDay(),
            'is_completed' => true,
        ]);

        $this->assertFalse($card->isOverdue());
    }

    public function test_is_not_overdue_without_due_date(): void
    {
        $card = $this->createCard(['due_date' => null]);
        $this->assertFalse($card->isOverdue());
    }

    public function test_is_not_overdue_when_future_date(): void
    {
        $card = $this->createCard(['due_date' => now()->addWeek()]);
        $this->assertFalse($card->isOverdue());
    }

    public function test_is_due_soon_within_two_days(): void
    {
        $card = $this->createCard(['due_date' => now()->addDay()]);
        $this->assertTrue($card->isDueSoon());
    }

    public function test_is_not_due_soon_when_far_away(): void
    {
        $card = $this->createCard(['due_date' => now()->addWeek()]);
        $this->assertFalse($card->isDueSoon());
    }

    public function test_is_not_due_soon_when_completed(): void
    {
        $card = $this->createCard([
            'due_date' => now()->addDay(),
            'is_completed' => true,
        ]);

        $this->assertFalse($card->isDueSoon());
    }

    // ==================
    // Checklist Progress
    // ==================

    public function test_checklist_progress_all_completed(): void
    {
        $card = $this->createCard();
        BoardCardChecklistItem::create(['card_id' => $card->id, 'title' => 'Item 1', 'is_completed' => true, 'position' => 0]);
        BoardCardChecklistItem::create(['card_id' => $card->id, 'title' => 'Item 2', 'is_completed' => true, 'position' => 1]);

        $this->assertEquals(100, $card->checklist_progress);
    }

    public function test_checklist_progress_half_completed(): void
    {
        $card = $this->createCard();
        BoardCardChecklistItem::create(['card_id' => $card->id, 'title' => 'Item 1', 'is_completed' => true, 'position' => 0]);
        BoardCardChecklistItem::create(['card_id' => $card->id, 'title' => 'Item 2', 'is_completed' => false, 'position' => 1]);

        $this->assertEquals(50, $card->checklist_progress);
    }

    public function test_checklist_progress_zero_when_no_items(): void
    {
        $card = $this->createCard();
        $this->assertEquals(0, $card->checklist_progress);
    }

    // ==================
    // Priority Color
    // ==================

    public function test_priority_color_urgent(): void
    {
        $card = $this->createCard(['priority' => 'urgent']);
        $this->assertEquals('red', $card->priority_color);
    }

    public function test_priority_color_high(): void
    {
        $card = $this->createCard(['priority' => 'high']);
        $this->assertEquals('orange', $card->priority_color);
    }

    public function test_priority_color_medium(): void
    {
        $card = $this->createCard(['priority' => 'medium']);
        $this->assertEquals('yellow', $card->priority_color);
    }

    public function test_priority_color_low(): void
    {
        $card = $this->createCard(['priority' => 'low']);
        $this->assertEquals('green', $card->priority_color);
    }

    // ==================
    // Mark Completed/Incomplete
    // ==================

    public function test_mark_as_completed(): void
    {
        $card = $this->createCard();

        $card->markAsCompleted();
        $card->refresh();

        $this->assertTrue($card->is_completed);
        $this->assertNotNull($card->completed_at);
    }

    public function test_mark_as_incomplete(): void
    {
        $card = $this->createCard([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $card->markAsIncomplete();
        $card->refresh();

        $this->assertFalse($card->is_completed);
        $this->assertNull($card->completed_at);
    }
}
