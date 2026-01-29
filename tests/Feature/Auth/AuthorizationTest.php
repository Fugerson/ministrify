<?php

namespace Tests\Feature\Auth;

use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL-specific TIMESTAMPDIFF');
        }

        $church = Church::factory()->create();
        $user = User::factory()->create([
            'church_id' => $church->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_settings(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->admin()->create([
            'church_id' => $church->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
    }

    public function test_volunteer_cannot_access_settings(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create([
            'church_id' => $church->id,
            'email_verified_at' => now(),
            'role' => 'volunteer',
        ]);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_user_roles_work_correctly(): void
    {
        $church = Church::factory()->create();

        $admin = User::factory()->admin()->create(['church_id' => $church->id]);
        $admin->refresh();
        $leader = User::factory()->leader()->create(['church_id' => $church->id]);
        $leader->refresh();
        $volunteer = User::factory()->volunteer()->create(['church_id' => $church->id]);
        $volunteer->refresh();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isLeader());
        $this->assertFalse($admin->isVolunteer());

        $this->assertFalse($leader->isAdmin());
        $this->assertTrue($leader->isLeader());
        $this->assertFalse($leader->isVolunteer());

        $this->assertFalse($volunteer->isAdmin());
        $this->assertFalse($volunteer->isLeader());
        $this->assertTrue($volunteer->isVolunteer());
    }

    public function test_has_role_method_works_with_array(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->admin()->create(['church_id' => $church->id]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole(['admin', 'leader']));
        $this->assertFalse($user->hasRole(['leader', 'volunteer']));
    }
}
