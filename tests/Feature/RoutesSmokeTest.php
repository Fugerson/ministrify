<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Board;
use App\Models\BoardCard;
use App\Models\Church;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Resource;
use App\Models\Song;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests — hit every GET route to catch 500 errors
 * (undefined methods, missing views, syntax errors, etc.)
 */
class RoutesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private User $admin;
    private Person $person;
    private Ministry $ministry;
    private Group $group;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->church, $this->admin] = $this->createChurchWithAdmin();

        $this->person = Person::factory()->forChurch($this->church)->create();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
        $this->group = Group::factory()->forChurch($this->church)->active()->create();
        $this->event = Event::factory()->forChurch($this->church)->forMinistry($this->ministry)->upcoming()->create();
    }

    /**
     * Helper: assert route does NOT return 500.
     * Accepts 200, 302, 403, 404 — anything except server error.
     */
    private function assertNotServerError(string $uri): void
    {
        $response = $this->actingAs($this->admin)->get($uri);
        $this->assertNotEquals(
            500,
            $response->getStatusCode(),
            "Route [{$uri}] returned 500 Server Error"
        );
    }

    private function skipIfSqlite(): void
    {
        if (\DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Uses MySQL-specific SQL functions');
        }
    }

    // ==========================================
    // Dashboard
    // ==========================================

    public function test_dashboard(): void
    {
        $this->skipIfSqlite(); // TIMESTAMPDIFF
        $this->assertNotServerError('/dashboard');
    }

    public function test_dashboard_birthdays(): void
    {
        $this->assertNotServerError('/dashboard/birthdays?month=2');
    }

    public function test_dashboard_charts(): void
    {
        $this->skipIfSqlite(); // MySQL aggregates
        $this->assertNotServerError('/dashboard/charts?type=attendance');
    }

    // ==========================================
    // People
    // ==========================================

    public function test_people_index(): void
    {
        $this->skipIfSqlite(); // MySQL GROUP_CONCAT
        $this->assertNotServerError('/people');
    }

    public function test_people_show(): void
    {
        $this->assertNotServerError("/people/{$this->person->id}");
    }

    public function test_people_create(): void
    {
        $this->assertNotServerError('/people/create');
    }

    public function test_people_edit(): void
    {
        $this->assertNotServerError("/people/{$this->person->id}/edit");
    }

    public function test_my_profile(): void
    {
        // Link admin to a person
        $this->admin->update(['person_id' => $this->person->id]);
        $this->assertNotServerError('/my-profile');
    }

    // ==========================================
    // Groups
    // ==========================================

    public function test_groups_index(): void
    {
        $this->assertNotServerError('/groups');
    }

    public function test_groups_show(): void
    {
        $this->assertNotServerError("/groups/{$this->group->id}");
    }

    public function test_groups_create(): void
    {
        $this->assertNotServerError('/groups/create');
    }

    public function test_groups_edit(): void
    {
        $this->assertNotServerError("/groups/{$this->group->id}/edit");
    }

    public function test_groups_attendance_index(): void
    {
        $this->assertNotServerError("/groups/{$this->group->id}/attendance");
    }

    // ==========================================
    // Ministries
    // ==========================================

    public function test_ministries_index(): void
    {
        $this->assertNotServerError('/ministries');
    }

    public function test_ministries_show(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}");
    }

    public function test_ministries_show_resources_tab(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}?tab=resources");
    }

    public function test_ministries_show_goals_tab(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}?tab=goals");
    }

    public function test_ministries_create(): void
    {
        $this->assertNotServerError('/ministries/create');
    }

    public function test_ministries_edit(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}/edit");
    }

    public function test_ministries_goals(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}/goals");
    }

    public function test_ministries_resources(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}/resources");
    }

    public function test_ministries_meetings(): void
    {
        $this->assertNotServerError("/ministries/{$this->ministry->id}/meetings");
    }

    // ==========================================
    // Events
    // ==========================================

    public function test_events_index(): void
    {
        $this->assertNotServerError('/events');
    }

    public function test_events_show(): void
    {
        $this->assertNotServerError("/events/{$this->event->id}");
    }

    public function test_events_create(): void
    {
        $this->assertNotServerError('/events/create');
    }

    public function test_events_edit(): void
    {
        $this->assertNotServerError("/events/{$this->event->id}/edit");
    }

    public function test_calendar(): void
    {
        $this->assertNotServerError('/calendar');
    }

    public function test_schedule(): void
    {
        $this->assertNotServerError('/schedule');
    }

    public function test_my_schedule(): void
    {
        $this->assertNotServerError('/my-schedule');
    }

    // ==========================================
    // Attendance
    // ==========================================

    public function test_attendance_index(): void
    {
        $this->assertNotServerError('/attendance');
    }

    public function test_attendance_create(): void
    {
        $this->assertNotServerError('/attendance/create');
    }

    // ==========================================
    // Finances
    // ==========================================

    public function test_finances_index(): void
    {
        $this->skipIfSqlite(); // MySQL date functions
        $this->assertNotServerError('/finances');
    }

    public function test_finances_journal(): void
    {
        $this->assertNotServerError('/finances/journal');
    }

    public function test_finances_budgets(): void
    {
        $this->assertNotServerError('/finances/budgets');
    }

    public function test_finances_expenses_index(): void
    {
        $this->assertNotServerError('/finances/expenses');
    }

    public function test_finances_incomes(): void
    {
        $this->assertNotServerError('/finances/incomes');
    }

    public function test_finances_categories(): void
    {
        $this->assertNotServerError('/finances/categories');
    }

    // ==========================================
    // Donations
    // ==========================================

    public function test_donations_index(): void
    {
        $this->assertNotServerError('/donations');
    }

    public function test_donations_qr(): void
    {
        $this->assertNotServerError('/donations/qr');
    }

    // ==========================================
    // Announcements
    // ==========================================

    public function test_announcements_index(): void
    {
        $this->assertNotServerError('/announcements');
    }

    public function test_announcements_create(): void
    {
        $this->assertNotServerError('/announcements/create');
    }

    public function test_announcements_show(): void
    {
        $announcement = Announcement::create([
            'church_id' => $this->church->id,
            'author_id' => $this->admin->id,
            'title' => 'Test',
            'content' => 'Test content',
            'is_published' => true,
        ]);
        $this->assertNotServerError("/announcements/{$announcement->id}");
    }

    // ==========================================
    // Boards (Task Tracker)
    // ==========================================

    public function test_boards_index(): void
    {
        $this->assertNotServerError('/boards');
    }

    public function test_boards_create(): void
    {
        $this->assertNotServerError('/boards/create');
    }

    public function test_boards_show(): void
    {
        $board = Board::create([
            'church_id' => $this->church->id,
            'name' => 'Test Board',
        ]);
        $this->assertNotServerError("/boards/{$board->id}");
    }

    public function test_boards_archived(): void
    {
        $this->assertNotServerError('/boards/archived');
    }

    // ==========================================
    // Songs
    // ==========================================

    public function test_songs_index(): void
    {
        $this->assertNotServerError('/songs');
    }

    public function test_songs_create(): void
    {
        $this->assertNotServerError('/songs/create');
    }

    public function test_songs_show(): void
    {
        $song = Song::create([
            'church_id' => $this->church->id,
            'title' => 'Amazing Grace',
        ]);
        $this->assertNotServerError("/songs/{$song->id}");
    }

    // ==========================================
    // Resources
    // ==========================================

    public function test_resources_index(): void
    {
        $this->assertNotServerError('/resources');
    }

    // ==========================================
    // Tags
    // ==========================================

    public function test_tags_index(): void
    {
        $this->assertNotServerError('/tags');
    }

    public function test_tags_create(): void
    {
        $this->assertNotServerError('/tags/create');
    }

    // ==========================================
    // Messages
    // ==========================================

    public function test_messages_index(): void
    {
        $this->assertNotServerError('/messages');
    }

    public function test_messages_create(): void
    {
        $this->assertNotServerError('/messages/create');
    }

    // ==========================================
    // Blockouts
    // ==========================================

    public function test_blockouts_index(): void
    {
        $this->assertNotServerError('/blockouts');
    }

    public function test_blockouts_create(): void
    {
        $this->assertNotServerError('/blockouts/create');
    }

    // ==========================================
    // Reports
    // ==========================================

    public function test_reports_index(): void
    {
        $this->assertNotServerError('/reports');
    }

    public function test_reports_attendance(): void
    {
        $this->assertNotServerError('/reports/attendance');
    }

    public function test_reports_finances(): void
    {
        $this->assertNotServerError('/reports/finances');
    }

    public function test_reports_volunteers(): void
    {
        $this->skipIfSqlite(); // MySQL date functions
        $this->assertNotServerError('/reports/volunteers');
    }

    // ==========================================
    // Rotation
    // ==========================================

    public function test_rotation_index(): void
    {
        $this->assertNotServerError('/rotation');
    }

    public function test_rotation_ministry(): void
    {
        $this->assertNotServerError("/rotation/ministry/{$this->ministry->id}");
    }

    // ==========================================
    // Settings
    // ==========================================

    public function test_settings_index(): void
    {
        $this->assertNotServerError('/settings');
    }

    public function test_settings_church_roles(): void
    {
        $this->assertNotServerError('/settings/church-roles');
    }

    public function test_settings_expense_categories(): void
    {
        $this->assertNotServerError('/settings/expense-categories');
    }

    public function test_settings_users(): void
    {
        $this->assertNotServerError('/settings/users');
    }

    public function test_settings_users_create(): void
    {
        $this->assertNotServerError('/settings/users/create');
    }

    public function test_settings_audit_logs(): void
    {
        $this->assertNotServerError('/settings/audit-logs');
    }

    public function test_settings_shepherds(): void
    {
        $this->assertNotServerError('/settings/shepherds');
    }

    public function test_settings_permissions(): void
    {
        $this->assertNotServerError('/settings/permissions');
    }

    // ==========================================
    // Scheduling Preferences
    // ==========================================

    public function test_scheduling_preferences(): void
    {
        $this->assertNotServerError('/scheduling-preferences');
    }

    // ==========================================
    // Two-Factor Auth
    // ==========================================

    public function test_two_factor_show(): void
    {
        $this->assertNotServerError('/two-factor');
    }

    // ==========================================
    // Prayer Requests
    // ==========================================

    public function test_prayer_requests_index(): void
    {
        $this->assertNotServerError('/prayer-requests');
    }

    // ==========================================
    // Support
    // ==========================================

    public function test_support_index(): void
    {
        $this->assertNotServerError('/support');
    }

    public function test_support_create(): void
    {
        $this->assertNotServerError('/support/create');
    }

    // ==========================================
    // Checklists
    // ==========================================

    public function test_checklists_templates(): void
    {
        $this->assertNotServerError('/checklists/templates');
    }

    // ==========================================
    // Search
    // ==========================================

    public function test_search(): void
    {
        $this->assertNotServerError('/search?q=test');
    }

    // ==========================================
    // Music Stand
    // ==========================================

    public function test_music_stand(): void
    {
        $this->assertNotServerError('/music-stand');
    }

    // ==========================================
    // Website Builder
    // ==========================================

    public function test_website_builder_index(): void
    {
        $this->assertNotServerError('/website-builder');
    }

    public function test_website_builder_design(): void
    {
        $this->assertNotServerError('/website-builder/design');
    }

    public function test_website_builder_sections(): void
    {
        $this->assertNotServerError('/website-builder/sections');
    }

    public function test_website_builder_about(): void
    {
        $this->assertNotServerError('/website-builder/about');
    }

    public function test_website_builder_team(): void
    {
        $this->assertNotServerError('/website-builder/team');
    }

    public function test_website_builder_faq(): void
    {
        $this->assertNotServerError('/website-builder/faq');
    }

    public function test_website_builder_blog(): void
    {
        $this->assertNotServerError('/website-builder/blog');
    }

    public function test_website_builder_gallery(): void
    {
        $this->assertNotServerError('/website-builder/gallery');
    }

    public function test_website_builder_sermons(): void
    {
        $this->assertNotServerError('/website-builder/sermons');
    }

    public function test_website_builder_testimonials(): void
    {
        $this->assertNotServerError('/website-builder/testimonials');
    }

    public function test_website_builder_templates(): void
    {
        $this->assertNotServerError('/website-builder/templates');
    }

    public function test_website_builder_prayer_inbox(): void
    {
        $this->assertNotServerError('/website-builder/prayer-inbox');
    }

    // ==========================================
    // Telegram
    // ==========================================

    public function test_telegram_broadcast(): void
    {
        $this->assertNotServerError('/telegram/broadcast');
    }

    public function test_telegram_chat(): void
    {
        $this->assertNotServerError('/telegram/chat');
    }

    // ==========================================
    // Service Plan Templates
    // ==========================================

    public function test_service_plan_templates(): void
    {
        $this->assertNotServerError('/service-plan-templates');
    }

    // ==========================================
    // Expenses (legacy)
    // ==========================================

    public function test_expenses_index(): void
    {
        $this->assertNotServerError('/expenses');
    }

    // ==========================================
    // System Admin (requires super admin)
    // ==========================================

    public function test_system_admin_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'church_id' => $this->church->id,
        ]);
        $response = $this->actingAs($superAdmin)->get('/system-admin');
        $this->assertNotEquals(500, $response->getStatusCode(), 'Route [/system-admin] returned 500');
    }

    public function test_system_admin_churches(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'church_id' => $this->church->id,
        ]);
        $response = $this->actingAs($superAdmin)->get('/system-admin/churches');
        $this->assertNotEquals(500, $response->getStatusCode(), 'Route [/system-admin/churches] returned 500');
    }

    public function test_system_admin_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'church_id' => $this->church->id,
        ]);
        $response = $this->actingAs($superAdmin)->get('/system-admin/users');
        $this->assertNotEquals(500, $response->getStatusCode(), 'Route [/system-admin/users] returned 500');
    }

    // ==========================================
    // POST routes — smoke test critical actions
    // ==========================================

    public function test_family_relationship_store(): void
    {
        $relatedPerson = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->postJson("/people/{$this->person->id}/family", [
            'related_person_id' => $relatedPerson->id,
            'relationship_type' => 'spouse',
        ]);

        $this->assertNotEquals(500, $response->getStatusCode(), 'Family relationship store returned 500');
    }
}
