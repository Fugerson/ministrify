<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryGoal;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MinistryGoalControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Store Goal
    // ==================

    public function test_admin_can_create_goal(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        Event::fake();

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => 'Growth Goal',
                'description' => 'Grow the ministry by 20%',
                'priority' => 'high',
                'due_date' => '2026-12-31',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_goals', [
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'title' => 'Growth Goal',
            'status' => 'active',
            'priority' => 'high',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_store_validates_title_required(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'description' => 'No title provided',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_validates_title_max_255(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_validates_priority_values(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => 'Test Goal',
                'priority' => 'urgent',
            ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_store_validates_due_date_is_date(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => 'Test Goal',
                'due_date' => 'not-a-date',
            ]);

        $response->assertSessionHasErrors('due_date');
    }

    public function test_store_allows_nullable_fields(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        Event::fake();

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => 'Minimal Goal',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_goals', [
            'ministry_id' => $this->ministry->id,
            'title' => 'Minimal Goal',
            'description' => null,
            'period' => null,
            'due_date' => null,
            'priority' => null,
        ]);
    }

    public function test_cannot_create_goal_for_other_church_ministry(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->post("/ministries/{$otherMinistry->id}/goals", [
                'title' => 'Hacked Goal',
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('ministry_goals', ['title' => 'Hacked Goal']);
    }

    // ==================
    // Update Goal
    // ==================

    public function test_admin_can_update_goal(): void
    {
        Event::fake();

        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'title' => 'Old Goal',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/goals/{$goal->id}", [
                'title' => 'Updated Goal',
                'priority' => 'medium',
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_goals', [
            'id' => $goal->id,
            'title' => 'Updated Goal',
            'priority' => 'medium',
        ]);
    }

    public function test_update_validates_status_values(): void
    {
        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'title' => 'Test Goal',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/goals/{$goal->id}", [
                'title' => 'Test Goal',
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_completing_goal_sets_progress_100_and_completed_at(): void
    {
        Event::fake();

        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'title' => 'Goal To Complete',
            'status' => 'active',
            'progress' => 50,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/goals/{$goal->id}", [
                'title' => 'Goal To Complete',
                'status' => 'completed',
            ]);

        $response->assertRedirect();
        $goal->refresh();
        $this->assertEquals('completed', $goal->status);
        $this->assertEquals(100, $goal->progress);
        $this->assertNotNull($goal->completed_at);
    }

    public function test_updating_already_completed_goal_does_not_reset_completed_at(): void
    {
        Event::fake();

        $completedAt = now()->subDays(5);
        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'title' => 'Already Done',
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => $completedAt,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/goals/{$goal->id}", [
                'title' => 'Already Done Updated',
                'status' => 'completed',
            ]);

        $response->assertRedirect();
        $goal->refresh();
        // completed_at should remain the original value since status didn't change
        $this->assertEquals($completedAt->startOfSecond()->toDateTimeString(), $goal->completed_at->startOfSecond()->toDateTimeString());
    }

    public function test_goal_must_belong_to_ministry(): void
    {
        $otherMinistry = Ministry::factory()->forChurch($this->church)->create();
        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $otherMinistry->id,
            'title' => 'Other Goal',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/ministries/{$this->ministry->id}/goals/{$goal->id}", [
                'title' => 'Moved Goal',
            ]);

        $response->assertStatus(404);
    }

    // ==================
    // Destroy Goal
    // ==================

    public function test_admin_can_delete_goal(): void
    {
        Event::fake();

        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'title' => 'To Delete',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/ministries/{$this->ministry->id}/goals/{$goal->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('ministry_goals', ['id' => $goal->id]);
    }

    public function test_cannot_delete_goal_from_wrong_ministry(): void
    {
        $otherMinistry = Ministry::factory()->forChurch($this->church)->create();
        $goal = MinistryGoal::create([
            'church_id' => $this->church->id,
            'ministry_id' => $otherMinistry->id,
            'title' => 'Protected Goal',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/ministries/{$this->ministry->id}/goals/{$goal->id}");

        $response->assertStatus(404);
    }

    public function test_cannot_delete_goal_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $goal = MinistryGoal::create([
            'church_id' => $otherChurch->id,
            'ministry_id' => $otherMinistry->id,
            'title' => 'Other Church Goal',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/ministries/{$otherMinistry->id}/goals/{$goal->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Volunteer access
    // ==================

    public function test_volunteer_not_in_ministry_cannot_create_goal(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $response = $this->actingAs($volunteer)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => 'Unauthorized Goal',
            ]);

        $response->assertStatus(403);
    }

    public function test_ministry_member_can_create_goal(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        Event::fake();

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        $this->ministry->members()->attach($person);

        $response = $this->actingAs($volunteer)
            ->post("/ministries/{$this->ministry->id}/goals", [
                'title' => 'Member Goal',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_goals', [
            'ministry_id' => $this->ministry->id,
            'title' => 'Member Goal',
        ]);
    }
}
