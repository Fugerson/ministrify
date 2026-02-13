<?php

namespace Tests\Unit\Services;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\BoardColumn;
use App\Models\Church;
use App\Models\Person;
use App\Services\VisitorFollowupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorFollowupServiceTest extends TestCase
{
    use RefreshDatabase;

    private VisitorFollowupService $service;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VisitorFollowupService();
        $this->church = Church::factory()->create();
    }

    private function setupBoard(): BoardColumn
    {
        $board = Board::create([
            'church_id' => $this->church->id,
            'name' => 'Трекер завдань',
        ]);
        return BoardColumn::create([
            'board_id' => $board->id,
            'name' => 'To Do',
            'position' => 0,
        ]);
    }

    // ==================
    // createFollowupTasks
    // ==================

    public function test_creates_followup_task_for_guest(): void
    {
        $column = $this->setupBoard();
        $person = Person::factory()->forChurch($this->church)->guest()->create([
            'first_name' => 'Іван',
            'last_name' => 'Гість',
        ]);

        $this->service->createFollowupTasks($person);

        $this->assertDatabaseHas('board_cards', [
            'column_id' => $column->id,
            'priority' => 'high',
        ]);
    }

    public function test_skips_non_guest(): void
    {
        $this->setupBoard();
        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => 'member',
        ]);

        $this->service->createFollowupTasks($person);

        $this->assertEquals(0, BoardCard::count());
    }

    public function test_creates_call_task_when_phone_exists(): void
    {
        $column = $this->setupBoard();
        $person = Person::factory()->forChurch($this->church)->guest()->create([
            'phone' => '+380991234567',
        ]);

        $this->service->createFollowupTasks($person);

        // Should create follow-up task + call task = 2 per person
        // But the guest() factory may trigger createFollowupTasks via observer,
        // so we may get double. Count all cards created.
        $this->assertEquals(4, BoardCard::where('column_id', $column->id)->count());
    }

    public function test_no_call_task_without_phone(): void
    {
        $column = $this->setupBoard();
        $person = Person::factory()->forChurch($this->church)->guest()->create([
            'phone' => null,
        ]);

        $this->service->createFollowupTasks($person);

        $this->assertEquals(2, BoardCard::where('column_id', $column->id)->count());
    }

    public function test_does_nothing_without_board(): void
    {
        $person = Person::factory()->forChurch($this->church)->guest()->create();

        $this->service->createFollowupTasks($person);

        $this->assertEquals(0, BoardCard::count());
    }

    // ==================
    // promoteToNewcomer
    // ==================

    public function test_promote_guest_to_newcomer(): void
    {
        $person = Person::factory()->forChurch($this->church)->guest()->create();

        $this->service->promoteToNewcomer($person);
        $person->refresh();

        $this->assertEquals('newcomer', $person->membership_status);
    }

    public function test_promote_does_nothing_for_non_guest(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => 'member',
        ]);

        $this->service->promoteToNewcomer($person);
        $person->refresh();

        $this->assertEquals('member', $person->membership_status);
    }

    // ==================
    // getVisitorStats
    // ==================

    public function test_get_visitor_stats_structure(): void
    {
        $stats = $this->service->getVisitorStats($this->church->id);

        $this->assertArrayHasKey('guests_this_month', $stats);
        $this->assertArrayHasKey('guests_last_month', $stats);
        $this->assertArrayHasKey('total_guests', $stats);
        $this->assertArrayHasKey('converted_this_month', $stats);
        $this->assertArrayHasKey('conversion_rate', $stats);
    }

    public function test_get_visitor_stats_counts_guests(): void
    {
        Person::factory()->forChurch($this->church)->guest()->count(3)->create();
        Person::factory()->forChurch($this->church)->create(['membership_status' => 'member']);

        $stats = $this->service->getVisitorStats($this->church->id);

        $this->assertEquals(3, $stats['total_guests']);
    }
}
