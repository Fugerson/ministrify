<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryGoal;
use App\Models\MinistryTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryTaskTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private Ministry $ministry;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Accessors
    // ==================

    public function test_is_done_returns_true_for_done_status(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->done()->create();
        $this->assertTrue($task->is_done);
    }

    public function test_is_done_returns_false_for_todo(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();
        $this->assertFalse($task->is_done);
    }

    public function test_is_overdue_when_past_due_and_not_done(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->create([
            'due_date' => now()->subDay(),
            'status' => 'todo',
        ]);

        $this->assertTrue($task->is_overdue);
    }

    public function test_is_not_overdue_when_done(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->done()->create([
            'due_date' => now()->subDay(),
        ]);

        $this->assertFalse($task->is_overdue);
    }

    public function test_is_not_overdue_when_no_due_date(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->create([
            'due_date' => null,
            'status' => 'todo',
        ]);

        $this->assertFalse($task->is_overdue);
    }

    public function test_status_label(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->create(['status' => 'in_progress']);
        $this->assertEquals('В процесі', $task->status_label);
    }

    public function test_priority_label(): void
    {
        $task = MinistryTask::factory()->forMinistry($this->ministry)->create(['priority' => 'medium']);
        $this->assertEquals('Середній', $task->priority_label);
    }

    // ==================
    // Methods
    // ==================

    public function test_mark_as_done(): void
    {
        $this->actingAs($this->admin);
        $task = MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();

        $task->markAsDone();
        $task->refresh();

        $this->assertEquals('done', $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertEquals($this->admin->id, $task->completed_by);
    }

    public function test_mark_as_done_updates_goal_progress(): void
    {
        $this->actingAs($this->admin);
        $goal = MinistryGoal::factory()->forMinistry($this->ministry)->create(['progress' => 0]);

        $task1 = MinistryTask::factory()->forGoal($goal)->todo()->create();
        MinistryTask::factory()->forGoal($goal)->todo()->create();

        $task1->markAsDone();
        $goal->refresh();

        $this->assertEquals(50, $goal->progress);
    }

    public function test_mark_as_todo(): void
    {
        $this->actingAs($this->admin);
        $task = MinistryTask::factory()->forMinistry($this->ministry)->done()->create();

        $task->markAsTodo();
        $task->refresh();

        $this->assertEquals('todo', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertNull($task->completed_by);
    }

    public function test_toggle_from_todo_to_done(): void
    {
        $this->actingAs($this->admin);
        $task = MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();

        $task->toggle();
        $task->refresh();

        $this->assertEquals('done', $task->status);
    }

    public function test_toggle_from_done_to_todo(): void
    {
        $this->actingAs($this->admin);
        $task = MinistryTask::factory()->forMinistry($this->ministry)->done()->create();

        $task->toggle();
        $task->refresh();

        $this->assertEquals('todo', $task->status);
    }

    // ==================
    // Scopes
    // ==================

    public function test_todo_scope(): void
    {
        MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();
        MinistryTask::factory()->forMinistry($this->ministry)->done()->create();

        $this->assertCount(1, MinistryTask::todo()->get());
    }

    public function test_in_progress_scope(): void
    {
        MinistryTask::factory()->forMinistry($this->ministry)->inProgress()->create();
        MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();

        $this->assertCount(1, MinistryTask::inProgress()->get());
    }

    public function test_done_scope(): void
    {
        MinistryTask::factory()->forMinistry($this->ministry)->done()->create();
        MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();

        $this->assertCount(1, MinistryTask::done()->get());
    }

    public function test_not_done_scope(): void
    {
        MinistryTask::factory()->forMinistry($this->ministry)->todo()->create();
        MinistryTask::factory()->forMinistry($this->ministry)->inProgress()->create();
        MinistryTask::factory()->forMinistry($this->ministry)->done()->create();

        $this->assertCount(2, MinistryTask::notDone()->get());
    }

    public function test_overdue_scope(): void
    {
        MinistryTask::factory()->forMinistry($this->ministry)->create([
            'status' => 'todo',
            'due_date' => now()->subDay(),
        ]);
        MinistryTask::factory()->forMinistry($this->ministry)->create([
            'status' => 'todo',
            'due_date' => now()->addWeek(),
        ]);
        MinistryTask::factory()->forMinistry($this->ministry)->done()->create([
            'due_date' => now()->subDay(),
        ]);

        $this->assertCount(1, MinistryTask::overdue()->get());
    }
}
