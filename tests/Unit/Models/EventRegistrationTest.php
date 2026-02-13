<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Ministry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $this->event = Event::factory()->forMinistry($ministry)->create([
            'date' => now()->addWeek(),
        ]);
    }

    // ==================
    // Status Methods
    // ==================

    public function test_confirm_sets_status_and_timestamp(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->pending()->create();

        $reg->confirm();
        $reg->refresh();

        $this->assertEquals('confirmed', $reg->status);
        $this->assertNotNull($reg->confirmed_at);
    }

    public function test_cancel_sets_status(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->confirmed()->create();

        $reg->cancel();
        $reg->refresh();

        $this->assertEquals('cancelled', $reg->status);
    }

    public function test_mark_attended(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->confirmed()->create();

        $reg->markAttended();
        $reg->refresh();

        $this->assertEquals('attended', $reg->status);
    }

    // ==================
    // Accessors
    // ==================

    public function test_full_name(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->create([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
        ]);

        $this->assertEquals('Іван Петренко', $reg->full_name);
    }

    public function test_total_guests_includes_registrant(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->create([
            'guests' => 3,
        ]);

        // 1 (self) + 3 guests = 4
        $this->assertEquals(4, $reg->total_guests);
    }

    public function test_total_guests_with_zero_guests(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->create([
            'guests' => 0,
        ]);

        $this->assertEquals(1, $reg->total_guests);
    }

    public function test_status_color(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->create(['status' => 'pending']);
        $this->assertEquals('yellow', $reg->status_color);

        $reg->update(['status' => 'confirmed']);
        $this->assertEquals('blue', $reg->status_color);

        $reg->update(['status' => 'cancelled']);
        $this->assertEquals('red', $reg->status_color);

        $reg->update(['status' => 'attended']);
        $this->assertEquals('green', $reg->status_color);
    }

    public function test_status_label(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->create(['status' => 'pending']);
        $this->assertEquals('Очікує', $reg->status_label);

        $reg->update(['status' => 'confirmed']);
        $this->assertEquals('Підтверджено', $reg->status_label);

        $reg->update(['status' => 'attended']);
        $this->assertEquals('Відвідав', $reg->status_label);
    }

    // ==================
    // Confirmation Token
    // ==================

    public function test_confirmation_token_generated_on_create(): void
    {
        $reg = EventRegistration::factory()->forEvent($this->event)->create();

        $this->assertNotNull($reg->confirmation_token);
        $this->assertEquals(32, strlen($reg->confirmation_token));
    }

    public function test_confirmation_token_is_unique(): void
    {
        $reg1 = EventRegistration::factory()->forEvent($this->event)->create();
        $reg2 = EventRegistration::factory()->forEvent($this->event)->create();

        $this->assertNotEquals($reg1->confirmation_token, $reg2->confirmation_token);
    }
}
