<?php

namespace Tests\Unit\Observers;

use App\Models\Church;
use App\Models\Person;
use App\Observers\PersonObserver;
use App\Services\DashboardCacheService;
use App\Services\VisitorFollowupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PersonObserverTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private $cacheMock;

    private $followupMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $this->cacheMock = Mockery::mock(DashboardCacheService::class);
        $this->cacheMock->shouldReceive('forgetPeopleRelated')->zeroOrMoreTimes();
        $this->followupMock = Mockery::mock(VisitorFollowupService::class);
    }

    public function test_created_clears_dashboard_cache(): void
    {
        $cacheMock = Mockery::mock(DashboardCacheService::class);
        $cacheMock->shouldReceive('forgetPeopleRelated')
            ->once()
            ->with($this->church->id);

        $observer = new PersonObserver($this->followupMock, $cacheMock);

        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => Person::STATUS_GUEST,
        ]);

        $observer->created($person);

        $this->assertTrue(true);
    }

    public function test_created_skips_cache_clear_without_church(): void
    {
        $cacheMock = Mockery::mock(DashboardCacheService::class);
        $cacheMock->shouldNotReceive('forgetPeopleRelated');

        $observer = new PersonObserver($this->followupMock, $cacheMock);

        $person = new Person(['church_id' => null]);

        $observer->created($person);

        $this->assertTrue(true);
    }

    public function test_updated_clears_dashboard_cache(): void
    {
        $cacheMock = Mockery::mock(DashboardCacheService::class);
        $cacheMock->shouldReceive('forgetPeopleRelated')
            ->once()
            ->with($this->church->id);

        $observer = new PersonObserver($this->followupMock, $cacheMock);

        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => Person::STATUS_GUEST,
        ]);

        $person->membership_status = 'member';
        $person->save();

        $observer->updated($person);

        $this->assertTrue(true);
    }

    public function test_deleted_clears_dashboard_cache(): void
    {
        $cacheMock = Mockery::mock(DashboardCacheService::class);
        $cacheMock->shouldReceive('forgetPeopleRelated')
            ->once()
            ->with($this->church->id);

        $observer = new PersonObserver($this->followupMock, $cacheMock);

        $person = Person::factory()->forChurch($this->church)->create();

        $observer->deleted($person);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
