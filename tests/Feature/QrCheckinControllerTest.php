<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCheckinControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    // ==================
    // Scanner page
    // ==================

    public function test_guest_cannot_access_qr_scanner(): void
    {
        $response = $this->get('/qr-scanner');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_qr_scanner(): void
    {
        $response = $this->actingAs($this->admin)->get('/qr-scanner');

        $response->assertStatus(200);
    }

    public function test_volunteer_without_attendance_edit_permission_gets_403(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)->get('/qr-scanner');

        $response->assertStatus(403);
    }

    public function test_volunteer_with_attendance_edit_permission_can_view_scanner(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['attendance' => ['view', 'edit']]);

        $response = $this->actingAs($volunteer)->get('/qr-scanner');

        $response->assertStatus(200);
    }

    // ==================
    // Checkin show (public)
    // ==================

    public function test_invalid_checkin_token_returns_404(): void
    {
        $response = $this->get('/checkin/invalid-token-12345');

        $response->assertStatus(404);
    }

    public function test_valid_checkin_token_shows_checkin_page(): void
    {
        $event = Event::factory()->create([
            'church_id' => $this->church->id,
            'checkin_token' => 'valid-test-token-abc123',
            'qr_checkin_enabled' => true,
        ]);

        $response = $this->get('/checkin/valid-test-token-abc123');

        $response->assertStatus(200);
    }

    public function test_checkin_page_accessible_without_auth(): void
    {
        $event = Event::factory()->create([
            'church_id' => $this->church->id,
            'checkin_token' => 'public-token-xyz',
            'qr_checkin_enabled' => true,
        ]);

        // No actingAs — guest access
        $response = $this->get('/checkin/public-token-xyz');

        $response->assertStatus(200);
    }

    // ==================
    // Today events API
    // ==================

    public function test_guest_cannot_access_today_events_api(): void
    {
        $response = $this->getJson('/api/checkin/today-events');

        $response->assertStatus(401);
    }

    public function test_admin_can_access_today_events_api(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/checkin/today-events');

        $response->assertStatus(200);
        $response->assertJsonStructure(['events']);
    }
}
