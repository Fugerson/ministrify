<?php

namespace Tests\Unit\Models;

use App\Models\BlockoutDate;
use App\Models\Church;
use App\Models\Ministry;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockoutDateTest extends TestCase
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
    // coversDateTime
    // ==================

    public function test_covers_date_within_range(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-07',
            'all_day' => true,
            'recurrence' => 'none',
        ]);

        $this->assertTrue($blockout->coversDateTime('2025-06-03', null));
    }

    public function test_does_not_cover_date_outside_range(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-07',
            'all_day' => true,
            'recurrence' => 'none',
        ]);

        $this->assertFalse($blockout->coversDateTime('2025-06-10', null));
    }

    public function test_covers_date_time_within_range(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-01',
            'all_day' => false,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'recurrence' => 'none',
        ]);

        $this->assertTrue($blockout->coversDateTime('2025-06-01', '12:00'));
    }

    public function test_does_not_cover_time_outside_range(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-01',
            'all_day' => false,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'recurrence' => 'none',
        ]);

        $this->assertFalse($blockout->coversDateTime('2025-06-01', '18:00'));
    }

    // ==================
    // Recurrence
    // ==================

    public function test_weekly_recurrence_matches(): void
    {
        // Monday blockout
        $monday = Carbon::parse('2025-06-02'); // Monday
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => $monday->toDateString(),
            'end_date' => $monday->copy()->addMonths(3)->toDateString(),
            'all_day' => true,
            'recurrence' => 'weekly',
        ]);

        // Next Monday
        $nextMonday = $monday->copy()->addWeek();
        $this->assertTrue($blockout->coversDateTime($nextMonday->toDateString(), null));
    }

    public function test_weekly_recurrence_does_not_match_wrong_day(): void
    {
        $monday = Carbon::parse('2025-06-02'); // Monday
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => $monday->toDateString(),
            'end_date' => $monday->toDateString(),
            'all_day' => true,
            'recurrence' => 'weekly',
        ]);

        $tuesday = $monday->copy()->addDay(); // Next day (Tuesday)
        $this->assertFalse($blockout->coversDateTime($tuesday->toDateString(), null));
    }

    public function test_biweekly_recurrence_matches_every_other_week(): void
    {
        $start = Carbon::parse('2025-06-02'); // Monday
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addMonths(3)->toDateString(),
            'all_day' => true,
            'recurrence' => 'biweekly',
        ]);

        // Two weeks later
        $twoWeeks = $start->copy()->addWeeks(2);
        $this->assertTrue($blockout->coversDateTime($twoWeeks->toDateString(), null));
    }

    public function test_biweekly_recurrence_does_not_match_odd_week(): void
    {
        $start = Carbon::parse('2025-06-02');
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => $start->toDateString(),
            'end_date' => $start->toDateString(),
            'all_day' => true,
            'recurrence' => 'biweekly',
        ]);

        // One week later
        $oneWeek = $start->copy()->addWeek();
        $this->assertFalse($blockout->coversDateTime($oneWeek->toDateString(), null));
    }

    public function test_monthly_recurrence_matches_same_day_of_month(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-15',
            'end_date' => '2025-12-31',
            'all_day' => true,
            'recurrence' => 'monthly',
        ]);

        $this->assertTrue($blockout->coversDateTime('2025-07-15', null));
    }

    public function test_monthly_recurrence_does_not_match_different_day(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-15',
            'end_date' => '2025-06-15',
            'all_day' => true,
            'recurrence' => 'monthly',
        ]);

        $this->assertFalse($blockout->coversDateTime('2025-07-20', null));
    }

    // ==================
    // getAllDates
    // ==================

    public function test_get_all_dates_non_recurring(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-03',
            'all_day' => true,
            'recurrence' => 'none',
        ]);

        $dates = $blockout->getAllDates();
        $this->assertCount(3, $dates);
    }

    // ==================
    // expireOld
    // ==================

    public function test_expire_old_marks_past_blockouts(): void
    {
        $old = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => now()->subMonth(),
            'end_date' => now()->subWeek(),
            'status' => 'active',
            'recurrence' => 'none',
        ]);

        BlockoutDate::expireOld();
        $old->refresh();

        $this->assertEquals('expired', $old->status);
    }

    public function test_expire_old_does_not_expire_future_blockouts(): void
    {
        $future = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => now()->addDay(),
            'end_date' => now()->addWeek(),
            'status' => 'active',
            'recurrence' => 'none',
        ]);

        BlockoutDate::expireOld();
        $future->refresh();

        $this->assertEquals('active', $future->status);
    }

    // ==================
    // Scopes
    // ==================

    public function test_active_scope(): void
    {
        BlockoutDate::factory()->forPerson($this->person)->active()->create();
        BlockoutDate::factory()->forPerson($this->person)->expired()->create();

        $active = BlockoutDate::active()->get();
        $this->assertCount(1, $active);
    }

    public function test_overlapping_scope(): void
    {
        BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-05',
            'end_date' => '2025-06-10',
        ]);
        BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-20',
            'end_date' => '2025-06-25',
        ]);

        $overlapping = BlockoutDate::overlapping('2025-06-08', '2025-06-12')->get();
        $this->assertCount(1, $overlapping);
    }

    public function test_for_date_scope(): void
    {
        BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-07',
        ]);

        $forDate = BlockoutDate::forDate('2025-06-03')->get();
        $this->assertCount(1, $forDate);
    }

    public function test_for_ministry_scope(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'applies_to_all' => false,
        ]);
        $blockout->ministries()->attach($ministry->id);

        BlockoutDate::factory()->forPerson($this->person)->create([
            'applies_to_all' => true,
        ]);

        $forMinistry = BlockoutDate::forMinistry($ministry->id)->get();
        $this->assertCount(2, $forMinistry);
    }

    // ==================
    // Accessors
    // ==================

    public function test_reason_label(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'reason' => 'vacation',
        ]);

        $this->assertEquals('Відпустка', $blockout->reason_label);
    }

    public function test_date_range_format(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-07',
        ]);

        $this->assertNotEmpty($blockout->date_range);
    }

    public function test_time_range_all_day(): void
    {
        $blockout = BlockoutDate::factory()->forPerson($this->person)->create([
            'all_day' => true,
        ]);

        $this->assertEquals('Весь день', $blockout->time_range);
    }
}
