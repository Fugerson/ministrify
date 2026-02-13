<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryGoal;
use App\Models\MinistryTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryGoalTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Accessors
    // ==================

    public function test_is_overdue_when_past_due_and_active(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => now()->subDay(),
            'status' => 'active',
        ]);

        $this->assertTrue($goal->is_overdue);
    }

    public function test_is_not_overdue_when_completed(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => now()->subDay(),
            'status' => 'completed',
        ]);

        $this->assertFalse($goal->is_overdue);
    }

    public function test_is_not_overdue_when_future_date(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => now()->addWeek(),
            'status' => 'active',
        ]);

        $this->assertFalse($goal->is_overdue);
    }

    public function test_is_not_overdue_when_no_due_date(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => null,
            'status' => 'active',
        ]);

        $this->assertFalse($goal->is_overdue);
    }

    public function test_days_remaining_positive(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => now()->addDays(5),
            'status' => 'active',
        ]);

        $this->assertEquals(5, $goal->days_remaining);
    }

    public function test_days_remaining_negative_when_overdue(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => now()->subDays(3),
            'status' => 'active',
        ]);

        $this->assertEquals(-3, $goal->days_remaining);
    }

    public function test_days_remaining_null_when_no_due_date(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'due_date' => null,
            'status' => 'active',
        ]);

        $this->assertNull($goal->days_remaining);
    }

    public function test_days_remaining_null_when_completed(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->completed()->create();

        $this->assertNull($goal->days_remaining);
    }

    public function test_calculated_progress_from_tasks(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create();

        MinistryTask::factory()->forGoal($goal)->done()->create();
        MinistryTask::factory()->forGoal($goal)->done()->create();
        MinistryTask::factory()->forGoal($goal)->todo()->create();
        MinistryTask::factory()->forGoal($goal)->todo()->create();

        // 2 done out of 4 = 50%
        $this->assertEquals(50, $goal->calculated_progress);
    }

    public function test_calculated_progress_returns_manual_when_no_tasks(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create([
            'progress' => 75,
        ]);

        $this->assertEquals(75, $goal->calculated_progress);
    }

    public function test_status_label(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create(['status' => 'active']);
        $this->assertEquals('Активна', $goal->status_label);
    }

    public function test_priority_label(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create(['priority' => 'high']);
        $this->assertEquals('Високий', $goal->priority_label);
    }

    public function test_status_color(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create(['status' => 'completed']);
        $this->assertEquals('green', $goal->status_color);
    }

    // ==================
    // Methods
    // ==================

    public function test_mark_as_completed(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->active()->create();

        $goal->markAsCompleted();
        $goal->refresh();

        $this->assertEquals('completed', $goal->status);
        $this->assertEquals(100, $goal->progress);
        $this->assertNotNull($goal->completed_at);
    }

    public function test_update_progress_from_tasks(): void
    {
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create(['progress' => 0]);

        MinistryTask::factory()->forGoal($goal)->done()->create();
        MinistryTask::factory()->forGoal($goal)->todo()->create();

        $goal->updateProgressFromTasks();
        $goal->refresh();

        $this->assertEquals(50, $goal->progress);
    }

    // ==================
    // Scopes
    // ==================

    public function test_active_scope(): void
    {
        MinistryGoal::factory()->forMinistry($this->ministry)->active()->create();
        MinistryGoal::factory()->forMinistry($this->ministry)->completed()->create();

        $this->assertCount(1, MinistryGoal::active()->get());
    }

    public function test_completed_scope(): void
    {
        MinistryGoal::factory()->forMinistry($this->ministry)->active()->create();
        MinistryGoal::factory()->forMinistry($this->ministry)->completed()->create();

        $this->assertCount(1, MinistryGoal::completed()->get());
    }

    public function test_overdue_scope(): void
    {
        MinistryGoal::factory()->forMinistry($this->ministry)->overdue()->create();
        MinistryGoal::factory()->forMinistry($this->ministry)->active()->create([
            'due_date' => now()->addMonth(),
        ]);

        $this->assertCount(1, MinistryGoal::overdue()->get());
    }
}
