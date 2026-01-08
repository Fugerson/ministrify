<?php

namespace Tests\Feature\Auth;

use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create([
            'church_id' => $church->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create([
            'church_id' => $church->id,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create([
            'church_id' => $church->id,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_unverified_users_cannot_access_dashboard(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->unverified()->create([
            'church_id' => $church->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/email/verify');
    }
}
