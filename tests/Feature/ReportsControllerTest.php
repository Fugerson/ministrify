<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    // ==================
    // Index
    // ==================

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_reports_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_reports_index_shows_stats(): void
    {
        // Create some people and events
        Person::factory()->forChurch($this->church)->count(5)->create();
        Event::factory()->count(3)->create([
            'church_id' => $this->church->id,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/reports');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return isset($stats['total_members'])
                && isset($stats['active_members'])
                && isset($stats['total_events'])
                && isset($stats['total_volunteers']);
        });
    }

    public function test_volunteer_without_reports_permission_gets_redirect(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/reports');

        // The permission:reports middleware fires first (403),
        // or the controller redirects to dashboard
        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403,
            'Expected redirect or 403 for volunteer without reports permission'
        );
    }

    // ==================
    // Attendance Report
    // ==================

    public function test_admin_can_view_attendance_report(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Attendance report uses MySQL-specific DAYOFWEEK/SUM functions');
        }

        $this->church->update(['attendance_enabled' => true]);

        $response = $this->actingAs($this->admin)->get('/reports/attendance');

        $response->assertStatus(200);
        $response->assertViewHas('monthlyData');
        $response->assertViewHas('weekdayStats');
    }

    public function test_attendance_report_with_disabled_attendance_returns_403(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Attendance report uses MySQL-specific functions');
        }

        $this->church->update(['attendance_enabled' => false]);

        $response = $this->actingAs($this->admin)->get('/reports/attendance');

        $response->assertStatus(403);
    }

    public function test_attendance_report_accepts_year_parameter(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Attendance report uses MySQL-specific functions');
        }

        $this->church->update(['attendance_enabled' => true]);

        $response = $this->actingAs($this->admin)->get('/reports/attendance?year=2025');

        $response->assertStatus(200);
        $response->assertViewHas('year', 2025);
    }

    // ==================
    // Finances Report
    // ==================

    public function test_admin_can_view_finances_report(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Finances report uses MySQL-specific SUM/CASE/COALESCE');
        }

        $response = $this->actingAs($this->admin)->get('/reports/finances');

        $response->assertStatus(200);
        $response->assertViewHas('monthlyData');
        $response->assertViewHas('comparison');
        $response->assertViewHas('incomeByCategory');
        $response->assertViewHas('expenseByMinistry');
    }

    public function test_finances_report_accepts_year_parameter(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Finances report uses MySQL-specific functions');
        }

        $response = $this->actingAs($this->admin)->get('/reports/finances?year=2025');

        $response->assertStatus(200);
        $response->assertViewHas('year', 2025);
    }

    // ==================
    // Volunteers Report
    // ==================

    public function test_admin_can_view_volunteers_report(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Volunteers report uses MySQL-specific JOIN/withTrashed queries');
        }

        $response = $this->actingAs($this->admin)->get('/reports/volunteers');

        $response->assertStatus(200);
        $response->assertViewHas('topVolunteers');
        $response->assertViewHas('monthlyData');
        $response->assertViewHas('byMinistry');
        $response->assertViewHas('inactiveVolunteers');
    }

    public function test_volunteers_report_accepts_year_parameter(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Volunteers report uses MySQL-specific functions');
        }

        $response = $this->actingAs($this->admin)->get('/reports/volunteers?year=2025');

        $response->assertStatus(200);
        $response->assertViewHas('year', 2025);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_reports_stats_are_scoped_to_own_church(): void
    {
        // Create people for own church
        Person::factory()->forChurch($this->church)->count(3)->create();

        // Create people for another church
        $otherChurch = Church::factory()->create();
        Person::factory()->forChurch($otherChurch)->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/reports');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            // Should only count own church members (3), not other church's (5)
            return $stats['total_members'] === 3;
        });
    }

    // ==================
    // Permission checks for sub-reports
    // ==================

    public function test_volunteer_cannot_access_attendance_report(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/reports/attendance');

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403,
            'Expected redirect or 403 for volunteer without reports permission'
        );
    }

    public function test_volunteer_cannot_access_finances_report(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/reports/finances');

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403,
            'Expected redirect or 403 for volunteer without reports/finances permission'
        );
    }

    public function test_volunteer_cannot_access_volunteers_report(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/reports/volunteers');

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403,
            'Expected redirect or 403 for volunteer without reports permission'
        );
    }
}
