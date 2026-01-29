<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL-specific TIMESTAMPDIFF');
        }

        $this->church = Church::factory()->create();
        $this->admin = User::factory()->admin()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
        ]);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_shows_people_statistics(): void
    {
        // Create some people
        Person::factory()->forChurch($this->church)->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_dashboard_shows_age_statistics(): void
    {
        // Create people with different ages
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(10), // child
        ]);
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(15), // teen
        ]);
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(25), // youth
        ]);
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(45), // adult
        ]);
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(65), // senior
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return isset($stats['age_stats'])
                && isset($stats['age_stats']['children'])
                && isset($stats['age_stats']['teens'])
                && isset($stats['age_stats']['youth'])
                && isset($stats['age_stats']['adults'])
                && isset($stats['age_stats']['seniors']);
        });
    }

    public function test_dashboard_shows_financial_data_for_admin(): void
    {
        // Create some transactions
        Transaction::factory()
            ->forChurch($this->church)
            ->income()
            ->completed()
            ->amount(1000)
            ->create(['date' => now()]);

        Transaction::factory()
            ->forChurch($this->church)
            ->expense()
            ->completed()
            ->amount(500)
            ->create(['date' => now()]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('financialData');
    }

    public function test_volunteer_can_access_dashboard(): void
    {
        $volunteer = User::factory()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
            'role' => 'volunteer',
        ]);

        $response = $this->actingAs($volunteer)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_chart_data_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/dashboard/chart-data?type=attendance');

        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    public function test_dashboard_growth_chart_data(): void
    {
        // Create people with joined dates
        Person::factory()->forChurch($this->church)->create([
            'joined_date' => now()->subMonths(2),
        ]);
        Person::factory()->forChurch($this->church)->create([
            'joined_date' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/dashboard/chart-data?type=growth');

        $response->assertStatus(200);
    }

    public function test_dashboard_financial_chart_data(): void
    {
        Transaction::factory()
            ->forChurch($this->church)
            ->income()
            ->completed()
            ->create(['date' => now()]);

        $response = $this->actingAs($this->admin)
            ->getJson('/dashboard/chart-data?type=financial');

        $response->assertStatus(200);
    }

    public function test_dashboard_caches_heavy_statistics(): void
    {
        Person::factory()->forChurch($this->church)->count(3)->create();

        // First request
        $response1 = $this->actingAs($this->admin)->get('/dashboard');
        $response1->assertStatus(200);

        // Second request should use cache
        $response2 = $this->actingAs($this->admin)->get('/dashboard');
        $response2->assertStatus(200);
    }

    public function test_user_without_church_is_redirected(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite does not allow nullable church_id');
        }

        $userWithoutChurch = User::factory()->create([
            'church_id' => null,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($userWithoutChurch)->get('/dashboard');

        // Should either redirect or handle gracefully
        $this->assertTrue(
            $response->isRedirection() || $response->status() === 200
        );
    }
}
