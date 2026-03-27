<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── Home page ──────────────────────────────────────────

    public function test_home_page_returns_200(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_contains_stats(): void
    {
        Church::factory()->count(2)->create();
        $church = Church::first();
        Person::factory()->count(5)->create(['church_id' => $church->id]);
        Event::factory()->count(3)->forChurch($church)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['churches'] >= 2
                && $stats['members'] >= 5
                && $stats['events'] >= 3;
        });
    }

    public function test_home_page_has_cache_control_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    // ── Features page ──────────────────────────────────────

    public function test_features_page_returns_200(): void
    {
        $this->get('/features')->assertStatus(200);
    }

    public function test_features_page_has_cache_control_header(): void
    {
        $response = $this->get('/features');
        $response->assertHeader('Cache-Control');
    }

    // ── Contact page ───────────────────────────────────────

    public function test_contact_page_returns_200(): void
    {
        $this->get('/contact')->assertStatus(200);
    }

    public function test_contact_page_has_cache_control_header(): void
    {
        $response = $this->get('/contact');
        $response->assertHeader('Cache-Control');
    }

    // ── Docs page ──────────────────────────────────────────

    public function test_docs_page_returns_200(): void
    {
        $this->get('/docs')->assertStatus(200);
    }

    // ── FAQ page ───────────────────────────────────────────

    public function test_faq_page_returns_200(): void
    {
        $this->get('/faq')->assertStatus(200);
    }

    // ── Terms page ─────────────────────────────────────────

    public function test_terms_page_returns_200(): void
    {
        $this->get('/terms')->assertStatus(200);
    }

    // ── Privacy page ───────────────────────────────────────

    public function test_privacy_page_returns_200(): void
    {
        $this->get('/privacy')->assertStatus(200);
    }

    // ── Account deletion page ──────────────────────────────

    public function test_account_deletion_page_returns_200(): void
    {
        $this->get('/account-deletion')->assertStatus(200);
    }

    // ── Register church page ───────────────────────────────

    public function test_register_church_page_returns_200(): void
    {
        $this->get('/register-church')->assertStatus(200);
    }

    public function test_register_church_page_has_cache_control_header(): void
    {
        $response = $this->get('/register-church');
        $response->assertHeader('Cache-Control');
    }

    // ── All static pages have Cache-Control ────────────────

    /**
     * @dataProvider staticPagesProvider
     */
    public function test_static_pages_have_cache_control(string $uri): void
    {
        $response = $this->get($uri);

        $response->assertStatus(200);
        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    public static function staticPagesProvider(): array
    {
        return [
            'home' => ['/'],
            'features' => ['/features'],
            'contact' => ['/contact'],
            'docs' => ['/docs'],
            'faq' => ['/faq'],
            'terms' => ['/terms'],
            'privacy' => ['/privacy'],
            'account-deletion' => ['/account-deletion'],
            'register-church' => ['/register-church'],
        ];
    }
}
