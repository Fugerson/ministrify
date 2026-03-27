<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
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
    // Access
    // ==================

    public function test_guest_cannot_access_onboarding(): void
    {
        $response = $this->get('/onboarding');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_onboarding(): void
    {
        $response = $this->actingAs($this->admin)->get('/onboarding');

        $response->assertStatus(200);
    }

    public function test_user_with_completed_onboarding_can_access_dashboard(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL-specific MONTH() function.');
        }

        $this->admin->update(['onboarding_completed' => true]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_with_incomplete_onboarding_can_still_access_dashboard(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL-specific MONTH() function.');
        }

        // CheckOnboarding middleware is a no-op now (Driver.js guided tour)
        $this->admin->update(['onboarding_completed' => false]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    // ==================
    // Steps
    // ==================

    public function test_can_view_valid_onboarding_step(): void
    {
        $response = $this->actingAs($this->admin)->get('/onboarding/step/welcome');

        $response->assertStatus(200);
    }

    public function test_invalid_step_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->get('/onboarding/step/nonexistent');

        $response->assertStatus(404);
    }

    // ==================
    // Save step
    // ==================

    public function test_can_save_welcome_step(): void
    {
        // Initialize onboarding state first
        $this->admin->startOnboarding();

        $response = $this->actingAs($this->admin)->postJson('/onboarding/step/welcome', []);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_save_invalid_step_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/onboarding/step/nonexistent', []);

        $response->assertStatus(404);
    }

    // ==================
    // Complete
    // ==================

    public function test_can_complete_onboarding(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/onboarding/complete');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->admin->refresh();
        $this->assertTrue($this->admin->onboarding_completed);
    }

    // ==================
    // Skip step
    // ==================

    public function test_can_skip_optional_step(): void
    {
        $this->admin->startOnboarding();

        // 'first_ministry' is typically optional — find an optional step
        $optionalStep = collect(User::ONBOARDING_STEPS)
            ->filter(fn ($config) => ! $config['required'])
            ->keys()
            ->first();

        if (! $optionalStep) {
            $this->markTestSkipped('No optional onboarding steps found');
        }

        $response = $this->actingAs($this->admin)->postJson("/onboarding/step/{$optionalStep}/skip");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_skip_required_step(): void
    {
        $this->admin->startOnboarding();

        $requiredStep = collect(User::ONBOARDING_STEPS)
            ->filter(fn ($config) => $config['required'])
            ->keys()
            ->first();

        if (! $requiredStep) {
            $this->markTestSkipped('No required onboarding steps found');
        }

        $response = $this->actingAs($this->admin)->postJson("/onboarding/step/{$requiredStep}/skip");

        $response->assertStatus(400);
    }

    // ==================
    // Restart
    // ==================

    public function test_can_restart_onboarding(): void
    {
        $this->admin->update(['onboarding_completed' => true]);

        $response = $this->actingAs($this->admin)->postJson('/onboarding/restart');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
