<?php

namespace Tests\Unit\Models;

use App\Models\Assignment;
use App\Models\Church;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
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
    // Registration
    // ==================

    public function test_can_accept_registrations_when_open(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'allow_registration' => true,
            'date' => now()->addWeek(),
        ]);

        $this->assertTrue($event->canAcceptRegistrations());
    }

    public function test_cannot_accept_registrations_when_disabled(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'allow_registration' => false,
            'date' => now()->addWeek(),
        ]);

        $this->assertFalse($event->canAcceptRegistrations());
    }

    public function test_cannot_accept_registrations_past_deadline(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'allow_registration' => true,
            'date' => now()->addWeek(),
            'registration_deadline' => now()->subDay(),
        ]);

        $this->assertFalse($event->canAcceptRegistrations());
    }

    public function test_cannot_accept_registrations_for_past_event(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'allow_registration' => true,
            'date' => now()->subDay(),
        ]);

        $this->assertFalse($event->canAcceptRegistrations());
    }

    public function test_cannot_accept_registrations_when_full(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'allow_registration' => true,
            'registration_limit' => 2,
            'date' => now()->addWeek(),
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'church_id' => $this->church->id,
            'first_name' => 'Person',
            'last_name' => 'One',
            'email' => 'p1@test.com',
            'status' => 'confirmed',
            'guests' => 0,
            'confirmation_token' => 'token1',
        ]);
        EventRegistration::create([
            'event_id' => $event->id,
            'church_id' => $this->church->id,
            'first_name' => 'Person',
            'last_name' => 'Two',
            'email' => 'p2@test.com',
            'status' => 'confirmed',
            'guests' => 0,
            'confirmation_token' => 'token2',
        ]);

        $this->assertFalse($event->canAcceptRegistrations());
    }

    public function test_remaining_spaces_with_limit(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'allow_registration' => true,
            'registration_limit' => 10,
            'date' => now()->addWeek(),
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'church_id' => $this->church->id,
            'first_name' => 'Person',
            'last_name' => 'One',
            'email' => 'p1@test.com',
            'status' => 'confirmed',
            'guests' => 2,
            'confirmation_token' => 'token1',
        ]);

        // 1 person + 2 guests = 3 spots taken
        $this->assertEquals(7, $event->remaining_spaces);
    }

    public function test_remaining_spaces_without_limit(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'registration_limit' => null,
            'date' => now()->addWeek(),
        ]);

        $this->assertNull($event->remaining_spaces);
    }

    // ==================
    // Staffing
    // ==================

    public function test_is_fully_staffed_with_all_positions_filled(): void
    {
        $position = Position::factory()->forMinistry($this->ministry)->create();
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->addWeek(),
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        Assignment::factory()->create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
        ]);

        $this->assertTrue($event->isFullyStaffed());
    }

    public function test_is_not_fully_staffed_with_unfilled_positions(): void
    {
        Position::factory()->forMinistry($this->ministry)->create();
        Position::factory()->forMinistry($this->ministry)->create();
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->addWeek(),
        ]);

        $this->assertFalse($event->isFullyStaffed());
    }

    public function test_unfilled_positions(): void
    {
        $pos1 = Position::factory()->forMinistry($this->ministry)->create(['name' => 'Вокал']);
        $pos2 = Position::factory()->forMinistry($this->ministry)->create(['name' => 'Гітара']);
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->addWeek(),
        ]);
        $person = Person::factory()->forChurch($this->church)->create();

        Assignment::factory()->create([
            'event_id' => $event->id,
            'position_id' => $pos1->id,
            'person_id' => $person->id,
        ]);

        $unfilled = $event->unfilled_positions;
        $this->assertCount(1, $unfilled);
        $this->assertEquals($pos2->id, $unfilled->first()->id);
    }

    // ==================
    // QR Check-in
    // ==================

    public function test_generate_checkin_token(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->addWeek(),
        ]);

        $token = $event->generateCheckinToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(32, strlen($token)); // 16 bytes = 32 hex chars
        $event->refresh();
        $this->assertEquals($token, $event->checkin_token);
    }

    public function test_can_qr_checkin_on_event_day(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'qr_checkin_enabled' => true,
            'date' => now(),
        ]);

        $this->assertTrue($event->canQrCheckin());
    }

    public function test_cannot_qr_checkin_when_disabled(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'qr_checkin_enabled' => false,
            'date' => now(),
        ]);

        $this->assertFalse($event->canQrCheckin());
    }

    public function test_cannot_qr_checkin_far_from_event(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'qr_checkin_enabled' => true,
            'date' => now()->addWeek(),
        ]);

        $this->assertFalse($event->canQrCheckin());
    }

    // ==================
    // Scopes
    // ==================

    public function test_upcoming_scope(): void
    {
        Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->addWeek(),
        ]);
        Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->subWeek(),
        ]);

        $upcoming = Event::upcoming()->get();
        $this->assertCount(1, $upcoming);
    }

    public function test_for_month_scope(): void
    {
        $now = now();
        Event::factory()->forMinistry($this->ministry)->create([
            'date' => $now,
        ]);
        Event::factory()->forMinistry($this->ministry)->create([
            'date' => $now->copy()->addMonths(2),
        ]);

        $monthEvents = Event::forMonth($now->year, $now->month)->get();
        $this->assertCount(1, $monthEvents);
    }

    public function test_services_scope(): void
    {
        Event::factory()->forMinistry($this->ministry)->service()->create([
            'date' => now()->addWeek(),
        ]);
        Event::factory()->forMinistry($this->ministry)->create([
            'date' => now()->addWeek(),
            'is_service' => false,
        ]);

        $services = Event::services()->get();
        $this->assertCount(1, $services);
    }

    // ==================
    // Service Type Labels
    // ==================

    public function test_service_type_label(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'is_service' => true,
            'service_type' => Event::SERVICE_SUNDAY,
            'date' => now()->addWeek(),
        ]);

        $this->assertEquals('Недільне служіння', $event->service_type_label);
    }

    public function test_service_type_label_null_when_not_service(): void
    {
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'service_type' => null,
            'date' => now()->addWeek(),
        ]);

        $this->assertNull($event->service_type_label);
    }
}
