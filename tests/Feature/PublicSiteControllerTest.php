<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicSiteControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();

        $this->church = Church::factory()->create([
            'slug' => 'test-church',
            'public_site_enabled' => true,
        ]);
    }

    // ── Church main page ───────────────────────────────────

    public function test_public_church_page_returns_200(): void
    {
        $response = $this->get('/c/test-church');

        $response->assertStatus(200);
        $response->assertViewIs('public.church');
        $response->assertViewHas('church');
    }

    public function test_church_with_disabled_public_site_returns_404(): void
    {
        $church = Church::factory()->create([
            'slug' => 'disabled-church',
            'public_site_enabled' => false,
        ]);

        $this->get('/c/disabled-church')->assertStatus(404);
    }

    public function test_invalid_slug_returns_404(): void
    {
        $this->get('/c/nonexistent-church-slug')->assertStatus(404);
    }

    public function test_church_page_shows_upcoming_public_events(): void
    {
        Event::factory()->forChurch($this->church)->upcoming()->create([
            'is_public' => true,
            'title' => 'Visible Public Event',
        ]);

        Event::factory()->forChurch($this->church)->upcoming()->create([
            'is_public' => false,
            'title' => 'Hidden Private Event',
        ]);

        $response = $this->get('/c/test-church');

        $response->assertStatus(200);
        $response->assertViewHas('upcomingEvents', function ($events) {
            return $events->count() === 1
                && $events->first()->title === 'Visible Public Event';
        });
    }

    // ── Preview mode ───────────────────────────────────────

    public function test_preview_mode_allows_church_member_to_view_disabled_site(): void
    {
        $church = Church::factory()->create([
            'slug' => 'preview-church',
            'public_site_enabled' => false,
        ]);

        $user = User::factory()->admin()->create(['church_id' => $church->id]);
        if (! DB::table('church_user')->where('user_id', $user->id)->where('church_id', $church->id)->exists()) {
            $user->churches()->attach($church->id, ['church_role_id' => $user->church_role_id]);
        }

        $response = $this->actingAs($user)->get('/c/preview-church?preview=1');

        $response->assertStatus(200);
    }

    public function test_preview_mode_returns_404_for_guest(): void
    {
        $church = Church::factory()->create([
            'slug' => 'preview-church-guest',
            'public_site_enabled' => false,
        ]);

        $this->get('/c/preview-church-guest?preview=1')->assertStatus(404);
    }

    public function test_preview_mode_returns_404_for_user_from_another_church(): void
    {
        $church = Church::factory()->create([
            'slug' => 'other-preview-church',
            'public_site_enabled' => false,
        ]);

        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($otherAdmin)->get('/c/other-preview-church?preview=1');

        $response->assertStatus(404);
    }

    // ── Events listing ─────────────────────────────────────

    public function test_events_page_returns_200(): void
    {
        $response = $this->get('/c/test-church/events');

        $response->assertStatus(200);
        $response->assertViewIs('public.events');
    }

    public function test_events_page_returns_404_for_disabled_site(): void
    {
        Church::factory()->create([
            'slug' => 'disabled-events',
            'public_site_enabled' => false,
        ]);

        $this->get('/c/disabled-events/events')->assertStatus(404);
    }

    public function test_events_page_only_shows_public_events(): void
    {
        Event::factory()->forChurch($this->church)->upcoming()->create([
            'is_public' => true,
        ]);
        Event::factory()->forChurch($this->church)->upcoming()->create([
            'is_public' => false,
        ]);

        $response = $this->get('/c/test-church/events');

        $response->assertStatus(200);
        $response->assertViewHas('events', function ($events) {
            return $events->total() === 1;
        });
    }

    // ── Single event page ──────────────────────────────────

    public function test_single_event_page_returns_200_for_public_event(): void
    {
        $event = Event::factory()->forChurch($this->church)->upcoming()->create([
            'is_public' => true,
        ]);

        $response = $this->get("/c/test-church/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertViewIs('public.event');
        $response->assertViewHas('event');
    }

    public function test_single_event_page_returns_404_for_private_event(): void
    {
        $event = Event::factory()->forChurch($this->church)->upcoming()->create([
            'is_public' => false,
        ]);

        $this->get("/c/test-church/events/{$event->id}")->assertStatus(404);
    }

    public function test_single_event_page_returns_404_for_other_church_event(): void
    {
        $otherChurch = Church::factory()->create(['public_site_enabled' => true]);
        $event = Event::factory()->forChurch($otherChurch)->upcoming()->create([
            'is_public' => true,
        ]);

        $this->get("/c/test-church/events/{$event->id}")->assertStatus(404);
    }

    // ── Donate page ────────────────────────────────────────

    public function test_donate_page_returns_200(): void
    {
        $response = $this->get('/c/test-church/donate');

        $response->assertStatus(200);
        $response->assertViewIs('public.donate');
        $response->assertViewHas('church');
    }

    public function test_donate_page_returns_404_for_disabled_site(): void
    {
        Church::factory()->create([
            'slug' => 'disabled-donate',
            'public_site_enabled' => false,
        ]);

        $this->get('/c/disabled-donate/donate')->assertStatus(404);
    }

    // ── Contact page ───────────────────────────────────────

    public function test_public_contact_page_returns_200(): void
    {
        $response = $this->get('/c/test-church/contact');

        $response->assertStatus(200);
        $response->assertViewIs('public.contact');
        $response->assertViewHas('church');
    }

    public function test_public_contact_page_returns_404_for_disabled_site(): void
    {
        Church::factory()->create([
            'slug' => 'disabled-contact',
            'public_site_enabled' => false,
        ]);

        $this->get('/c/disabled-contact/contact')->assertStatus(404);
    }

    // ── Donate success page ────────────────────────────────

    public function test_donate_success_page_returns_200(): void
    {
        $response = $this->get('/c/test-church/donate/success');

        $response->assertStatus(200);
        $response->assertViewIs('public.donate-success');
    }
}
