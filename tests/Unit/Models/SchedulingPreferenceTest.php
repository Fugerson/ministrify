<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryPreference;
use App\Models\Person;
use App\Models\SchedulingPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingPreferenceTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private Person $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
        $this->person = Person::factory()->forChurch($this->church)->create();
    }

    // ==================
    // getMaxForMinistry
    // ==================

    public function test_get_max_for_ministry_returns_specific(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'max_times_per_month' => 4,
        ]);

        MinistryPreference::create([
            'scheduling_preference_id' => $pref->id,
            'ministry_id' => $ministry->id,
            'max_times_per_month' => 2,
        ]);

        $this->assertEquals(2, $pref->getMaxForMinistry($ministry->id));
    }

    public function test_get_max_for_ministry_falls_back_to_global(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'max_times_per_month' => 4,
        ]);

        $this->assertEquals(4, $pref->getMaxForMinistry($ministry->id));
    }

    // ==================
    // getPreferredForMinistry
    // ==================

    public function test_get_preferred_for_ministry_specific(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'preferred_times_per_month' => 3,
        ]);

        MinistryPreference::create([
            'scheduling_preference_id' => $pref->id,
            'ministry_id' => $ministry->id,
            'preferred_times_per_month' => 1,
        ]);

        $this->assertEquals(1, $pref->getPreferredForMinistry($ministry->id));
    }

    public function test_get_preferred_for_ministry_fallback(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'preferred_times_per_month' => 3,
        ]);

        $this->assertEquals(3, $pref->getPreferredForMinistry($ministry->id));
    }

    // ==================
    // hasHouseholdConflict
    // ==================

    public function test_no_conflict_when_preference_none(): void
    {
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'household_preference' => 'none',
        ]);

        $this->assertFalse($pref->hasHouseholdConflict(1, [2, 3]));
    }

    public function test_together_conflict_when_partner_not_assigned(): void
    {
        $partner = Person::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'household_preference' => 'together',
            'prefer_with_person_id' => $partner->id,
        ]);

        // Partner NOT in assigned list
        $this->assertTrue($pref->hasHouseholdConflict(1, [10, 20]));
    }

    public function test_together_no_conflict_when_partner_assigned(): void
    {
        $partner = Person::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'household_preference' => 'together',
            'prefer_with_person_id' => $partner->id,
        ]);

        $this->assertFalse($pref->hasHouseholdConflict(1, [$partner->id, 10]));
    }

    public function test_separate_conflict_when_partner_assigned(): void
    {
        $partner = Person::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'household_preference' => 'separate',
            'prefer_with_person_id' => $partner->id,
        ]);

        $this->assertTrue($pref->hasHouseholdConflict(1, [$partner->id, 10]));
    }

    public function test_separate_no_conflict_when_partner_not_assigned(): void
    {
        $partner = Person::factory()->forChurch($this->church)->create();
        $pref = SchedulingPreference::factory()->forPerson($this->person)->create([
            'household_preference' => 'separate',
            'prefer_with_person_id' => $partner->id,
        ]);

        $this->assertFalse($pref->hasHouseholdConflict(1, [10, 20]));
    }
}
