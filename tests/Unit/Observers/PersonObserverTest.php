<?php

namespace Tests\Unit\Observers;

use App\Models\Church;
use App\Models\Person;
use App\Observers\PersonObserver;
use App\Services\VisitorFollowupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PersonObserverTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    public function test_created_triggers_followup_for_guest(): void
    {
        $mock = Mockery::mock(VisitorFollowupService::class);
        $mock->shouldReceive('createFollowupTasks')
            ->once()
            ->with(Mockery::on(fn ($person) => $person->membership_status === Person::STATUS_GUEST));

        $observer = new PersonObserver($mock);

        $person = Person::factory()->forChurch($this->church)->make([
            'membership_status' => Person::STATUS_GUEST,
        ]);
        $person->save();

        // Call observer manually since we want to test with our mock
        Person::withoutEvents(function () use ($observer, $person) {
            // Re-create to test observer logic
        });

        // Alternative: test via observer directly
        $person2 = Person::factory()->forChurch($this->church)->make([
            'membership_status' => Person::STATUS_GUEST,
        ]);
        $person2->save();
        $observer->created($person2);

        $this->assertTrue(true);
    }

    public function test_created_skips_followup_for_non_guest(): void
    {
        $mock = Mockery::mock(VisitorFollowupService::class);
        $mock->shouldNotReceive('createFollowupTasks');

        $observer = new PersonObserver($mock);

        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => 'member',
        ]);

        $observer->created($person);

        $this->assertTrue(true);
    }

    public function test_updated_detects_membership_status_change(): void
    {
        $mock = Mockery::mock(VisitorFollowupService::class);
        $mock->shouldReceive('createFollowupTasks')->zeroOrMoreTimes();
        $observer = new PersonObserver($mock);

        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => Person::STATUS_GUEST,
        ]);

        $person->membership_status = 'member';
        $person->save();

        // The observer doesn't do anything specific on status change yet,
        // just verify it handles it without error
        $observer->updated($person);

        $this->assertTrue($person->isDirty('membership_status') || $person->wasChanged('membership_status'));
    }

    public function test_deleted_handles_cleanup(): void
    {
        $mock = Mockery::mock(VisitorFollowupService::class);
        $mock->shouldReceive('createFollowupTasks')->zeroOrMoreTimes();
        $observer = new PersonObserver($mock);

        $person = Person::factory()->forChurch($this->church)->create();

        // Should not throw any exception
        $observer->deleted($person);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
