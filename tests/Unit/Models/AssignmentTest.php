<?php

namespace Tests\Unit\Models;

use App\Models\Assignment;
use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private Ministry $ministry;
    private Event $event;
    private Position $position;
    private Person $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
        $this->event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $this->position = Position::factory()->forMinistry($this->ministry)->create();
        $this->person = Person::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Status Checks
    // ==================

    public function test_is_pending(): void
    {
        $assignment = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->isPending());
        $this->assertFalse($assignment->isConfirmed());
        $this->assertFalse($assignment->isDeclined());
        $this->assertFalse($assignment->isAttended());
    }

    public function test_is_confirmed(): void
    {
        $assignment = Assignment::factory()->confirmed()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->isConfirmed());
        $this->assertFalse($assignment->isPending());
    }

    public function test_is_declined(): void
    {
        $assignment = Assignment::factory()->declined()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->isDeclined());
    }

    public function test_is_attended(): void
    {
        // 'attended' not in assignments ENUM on SQLite — skip
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite ENUM does not include attended status');
        }

        $assignment = Assignment::factory()->attended()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->isAttended());
    }

    // ==================
    // Status Transitions
    // ==================

    public function test_valid_transitions_from_pending(): void
    {
        $assignment = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->canTransitionTo(Assignment::STATUS_CONFIRMED));
        $this->assertTrue($assignment->canTransitionTo(Assignment::STATUS_DECLINED));
        $this->assertFalse($assignment->canTransitionTo(Assignment::STATUS_ATTENDED));
    }

    public function test_valid_transitions_from_confirmed(): void
    {
        $assignment = Assignment::factory()->confirmed()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->canTransitionTo(Assignment::STATUS_ATTENDED));
        $this->assertTrue($assignment->canTransitionTo(Assignment::STATUS_DECLINED));
        $this->assertFalse($assignment->canTransitionTo(Assignment::STATUS_PENDING));
    }

    public function test_valid_transitions_from_declined(): void
    {
        $assignment = Assignment::factory()->declined()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertTrue($assignment->canTransitionTo(Assignment::STATUS_PENDING));
        $this->assertFalse($assignment->canTransitionTo(Assignment::STATUS_CONFIRMED));
    }

    public function test_attended_is_final_state(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite ENUM does not include attended status');
        }

        $assignment = Assignment::factory()->attended()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertFalse($assignment->canTransitionTo(Assignment::STATUS_PENDING));
        $this->assertFalse($assignment->canTransitionTo(Assignment::STATUS_CONFIRMED));
        $this->assertFalse($assignment->canTransitionTo(Assignment::STATUS_DECLINED));
    }

    // ==================
    // confirm/decline/markAsAttended
    // ==================

    public function test_confirm_from_pending(): void
    {
        $assignment = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $result = $assignment->confirm();

        $this->assertTrue($result);
        $assignment->refresh();
        $this->assertEquals(Assignment::STATUS_CONFIRMED, $assignment->status);
        $this->assertNotNull($assignment->responded_at);
    }

    public function test_confirm_from_declined_fails(): void
    {
        $assignment = Assignment::factory()->declined()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $result = $assignment->confirm();
        $this->assertFalse($result);
    }

    public function test_decline_from_pending(): void
    {
        $assignment = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $result = $assignment->decline();

        $this->assertTrue($result);
        $assignment->refresh();
        $this->assertEquals(Assignment::STATUS_DECLINED, $assignment->status);
        $this->assertNotNull($assignment->responded_at);
    }

    public function test_mark_as_attended_from_confirmed(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite ENUM does not include attended status');
        }

        $assignment = Assignment::factory()->confirmed()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $result = $assignment->markAsAttended();

        $this->assertTrue($result);
        $assignment->refresh();
        $this->assertEquals(Assignment::STATUS_ATTENDED, $assignment->status);
    }

    public function test_mark_as_attended_from_pending_fails(): void
    {
        $assignment = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $result = $assignment->markAsAttended();
        $this->assertFalse($result);
    }

    // ==================
    // Override Flags
    // ==================

    public function test_has_overridden_conflicts(): void
    {
        $assignment = Assignment::factory()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
            'blockout_override' => true,
        ]);

        $this->assertTrue($assignment->hasOverriddenConflicts());
    }

    public function test_has_no_overridden_conflicts(): void
    {
        $assignment = Assignment::factory()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
            'blockout_override' => false,
            'preference_override' => false,
            'conflict_override' => false,
        ]);

        $this->assertFalse($assignment->hasOverriddenConflicts());
    }

    // ==================
    // Scopes
    // ==================

    public function test_pending_scope(): void
    {
        Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);
        $person2 = Person::factory()->forChurch($this->church)->create();
        Assignment::factory()->confirmed()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $person2->id,
        ]);

        $this->assertCount(1, Assignment::pending()->get());
    }

    public function test_confirmed_scope(): void
    {
        Assignment::factory()->confirmed()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);
        $person2 = Person::factory()->forChurch($this->church)->create();
        Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $person2->id,
        ]);

        $this->assertCount(1, Assignment::confirmed()->get());
    }

    public function test_for_upcoming_events_scope(): void
    {
        $futureEvent = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $pastEvent = Event::factory()->forMinistry($this->ministry)->past()->create();

        Assignment::factory()->create([
            'event_id' => $futureEvent->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);
        $person2 = Person::factory()->forChurch($this->church)->create();
        Assignment::factory()->create([
            'event_id' => $pastEvent->id,
            'position_id' => $this->position->id,
            'person_id' => $person2->id,
        ]);

        $this->assertCount(1, Assignment::forUpcomingEvents()->get());
    }

    // ==================
    // Status Display
    // ==================

    public function test_status_label(): void
    {
        $assignment = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);

        $this->assertEquals('Очікує підтвердження', $assignment->status_label);
    }

    public function test_status_color(): void
    {
        $pending = Assignment::factory()->pending()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $this->person->id,
        ]);
        $this->assertEquals('yellow', $pending->status_color);

        $person2 = Person::factory()->forChurch($this->church)->create();
        $confirmed = Assignment::factory()->confirmed()->create([
            'event_id' => $this->event->id,
            'position_id' => $this->position->id,
            'person_id' => $person2->id,
        ]);
        $this->assertEquals('green', $confirmed->status_color);
    }
}
