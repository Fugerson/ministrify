<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
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
    // Index
    // ==================

    public function test_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/settings');

        $response->assertStatus(200);
    }

    public function test_volunteer_cannot_view_settings(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_leader_cannot_view_settings(): void
    {
        $leader = $this->createUserWithRole($this->church, 'leader');

        $response = $this->actingAs($leader)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_settings(): void
    {
        $response = $this->get('/settings');

        $response->assertRedirect('/login');
    }

    // ==================
    // Update Church
    // ==================

    public function test_admin_can_update_church_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/settings/church', [
            'name' => 'Updated Church Name',
            'city' => 'Київ',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('churches', [
            'id' => $this->church->id,
            'name' => 'Updated Church Name',
        ]);
    }

    public function test_church_settings_require_name_and_city(): void
    {
        $response = $this->actingAs($this->admin)->put('/settings/church', []);

        $response->assertSessionHasErrors(['name', 'city']);
    }

    // ==================
    // Notifications
    // ==================

    public function test_admin_can_update_notification_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/settings/notifications', [
            'reminder_day_before' => true,
            'reminder_same_day' => false,
        ]);

        $response->assertRedirect();
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_settings_updates_only_own_church(): void
    {
        $otherChurch = Church::factory()->create(['name' => 'Other Church']);

        $this->actingAs($this->admin)->put('/settings/church', [
            'name' => 'My Updated Church',
            'city' => 'Львів',
        ]);

        $otherChurch->refresh();
        $this->assertEquals('Other Church', $otherChurch->name);
    }
}
