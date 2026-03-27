<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\DonationCampaign;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationControllerTest extends TestCase
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
    // Index (Admin)
    // ==================

    public function test_admin_can_view_donations_index(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('DonationController uses MySQL-specific COALESCE in selectRaw');
        }

        $response = $this->actingAs($this->admin)->get('/donations');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_donations(): void
    {
        $response = $this->get('/donations');

        $response->assertRedirect('/login');
    }

    public function test_volunteer_cannot_view_donations(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/donations');

        $response->assertStatus(403);
    }

    public function test_leader_can_view_donations(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('DonationController uses MySQL-specific COALESCE in selectRaw');
        }

        $leader = $this->createUserWithRole($this->church, 'leader');

        $response = $this->actingAs($leader)->get('/donations');

        $response->assertStatus(200);
    }

    public function test_donations_index_shows_only_own_church_data(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('DonationController uses MySQL-specific COALESCE in selectRaw');
        }

        $otherChurch = Church::factory()->create();
        Transaction::factory()->forChurch($otherChurch)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 99999,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->admin)->get('/donations');

        $response->assertStatus(200);
        $response->assertDontSee('99999');
    }

    public function test_donations_index_displays_donation_transactions(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('DonationController uses MySQL-specific COALESCE in selectRaw');
        }

        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 500,
            'donor_name' => 'Test Donor',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations');

        $response->assertStatus(200);
        $response->assertSee('Test Donor');
    }

    // ==================
    // Store Campaign
    // ==================

    public function test_admin_can_create_campaign(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/donations/campaigns', [
                'name' => 'Building Fund',
                'description' => 'New church building',
                'goal_amount' => 100000,
                'start_date' => '2026-04-01',
                'end_date' => '2026-12-31',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('donation_campaigns', [
            'church_id' => $this->church->id,
            'name' => 'Building Fund',
            'goal_amount' => 100000,
            'is_active' => true,
        ]);
    }

    public function test_campaign_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/donations/campaigns', [
            'description' => 'No name campaign',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_campaign_end_date_must_be_after_start_date(): void
    {
        $response = $this->actingAs($this->admin)->post('/donations/campaigns', [
            'name' => 'Test Campaign',
            'start_date' => '2026-06-01',
            'end_date' => '2026-05-01',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_volunteer_cannot_create_campaign(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->post('/donations/campaigns', [
            'name' => 'Unauthorized Campaign',
        ]);

        $response->assertStatus(403);
    }

    public function test_campaign_created_with_correct_church_id(): void
    {
        $this->actingAs($this->admin)->postJson('/donations/campaigns', [
            'name' => 'Church Campaign',
            'description' => null,
            'goal_amount' => null,
            'start_date' => null,
            'end_date' => null,
        ]);

        $campaign = DonationCampaign::where('name', 'Church Campaign')->first();
        $this->assertNotNull($campaign);
        $this->assertEquals($this->church->id, $campaign->church_id);
    }

    public function test_campaign_created_as_active_by_default(): void
    {
        $this->actingAs($this->admin)->postJson('/donations/campaigns', [
            'name' => 'Active by Default',
            'description' => null,
            'goal_amount' => null,
            'start_date' => null,
            'end_date' => null,
        ]);

        $campaign = DonationCampaign::where('name', 'Active by Default')->first();
        $this->assertNotNull($campaign);
        $this->assertTrue($campaign->is_active);
    }

    public function test_campaign_with_nullable_fields(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/donations/campaigns', [
            'name' => 'Minimal Campaign',
            'description' => null,
            'goal_amount' => null,
            'start_date' => null,
            'end_date' => null,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('donation_campaigns', [
            'church_id' => $this->church->id,
            'name' => 'Minimal Campaign',
            'description' => null,
            'goal_amount' => null,
        ]);
    }

    // ==================
    // Toggle Campaign
    // ==================

    public function test_admin_can_toggle_campaign(): void
    {
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Toggle Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/donations/campaigns/{$campaign->id}/toggle");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertFalse($campaign->fresh()->is_active);
    }

    public function test_toggle_campaign_activates_inactive_campaign(): void
    {
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Inactive Campaign',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/donations/campaigns/{$campaign->id}/toggle");

        $this->assertTrue($campaign->fresh()->is_active);
    }

    public function test_cannot_toggle_campaign_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $campaign = DonationCampaign::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Church Campaign',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/donations/campaigns/{$campaign->id}/toggle");

        $response->assertStatus(403);
        // Campaign state should not change
        $this->assertTrue($campaign->fresh()->is_active);
    }

    public function test_volunteer_cannot_toggle_campaign(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'No Toggle',
            'is_active' => true,
        ]);

        $response = $this->actingAs($volunteer)
            ->postJson("/donations/campaigns/{$campaign->id}/toggle");

        $response->assertStatus(403);
    }

    // ==================
    // Destroy Campaign
    // ==================

    public function test_admin_can_delete_campaign(): void
    {
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Delete Me',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/donations/campaigns/{$campaign->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('donation_campaigns', ['id' => $campaign->id]);
    }

    public function test_cannot_delete_campaign_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $campaign = DonationCampaign::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Church',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/donations/campaigns/{$campaign->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('donation_campaigns', ['id' => $campaign->id]);
    }

    public function test_volunteer_cannot_delete_campaign(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Protected',
            'is_active' => true,
        ]);

        $response = $this->actingAs($volunteer)
            ->deleteJson("/donations/campaigns/{$campaign->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('donation_campaigns', ['id' => $campaign->id]);
    }

    public function test_guest_cannot_delete_campaign(): void
    {
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Guest Delete',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/donations/campaigns/{$campaign->id}");

        $response->assertStatus(401);
    }

    // ==================
    // QR Code
    // ==================

    public function test_admin_can_view_qr_code_with_public_site(): void
    {
        $this->church->update(['slug' => 'test-qr-church', 'public_site_enabled' => true]);

        $response = $this->actingAs($this->admin)->get('/donations/qr');

        $response->assertStatus(200);
    }

    public function test_qr_code_returns_error_without_public_site_enabled(): void
    {
        $this->church->update(['public_site_enabled' => false]);

        $response = $this->actingAs($this->admin)
            ->getJson('/donations/qr');

        $response->assertJson(['success' => false]);
    }

    public function test_qr_code_returns_error_without_slug(): void
    {
        $this->church->update(['slug' => null, 'public_site_enabled' => true]);

        $response = $this->actingAs($this->admin)
            ->getJson('/donations/qr');

        $response->assertJson(['success' => false]);
    }

    public function test_volunteer_cannot_view_qr_code(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/donations/qr');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_qr_code(): void
    {
        $response = $this->get('/donations/qr');

        $response->assertRedirect('/login');
    }

    // ==================
    // Export
    // ==================

    public function test_admin_can_export_donations_csv(): void
    {
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 1000,
            'donor_name' => 'Export Donor',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    public function test_export_filters_by_year(): void
    {
        // Transaction in 2025
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 2000,
            'donor_name' => 'Year2025 Donor',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => '2025-06-15',
        ]);

        // Transaction in current year
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 3000,
            'donor_name' => 'CurrentYear Donor',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations/export?year=2025');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Year2025 Donor', $content);
        $this->assertStringNotContainsString('CurrentYear Donor', $content);
    }

    public function test_volunteer_cannot_export_donations(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/donations/export');

        $response->assertStatus(403);
    }

    public function test_export_only_includes_completed_donations(): void
    {
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 3000,
            'donor_name' => 'Completed Donor',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 4000,
            'donor_name' => 'Pending Donor',
            'status' => Transaction::STATUS_PENDING,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations/export');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Completed Donor', $content);
        $this->assertStringNotContainsString('Pending Donor', $content);
    }

    public function test_export_excludes_other_church_donations(): void
    {
        $otherChurch = Church::factory()->create();
        Transaction::factory()->forChurch($otherChurch)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'amount' => 7777,
            'donor_name' => 'Foreign Donor',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations/export');

        $content = $response->streamedContent();
        $this->assertStringNotContainsString('Foreign Donor', $content);
    }

    public function test_export_only_includes_donation_source_type(): void
    {
        // Donation transaction
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'donor_name' => 'Donation Source',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        // Income transaction (not donation)
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_INCOME,
            'direction' => Transaction::DIRECTION_IN,
            'donor_name' => 'Income Source',
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations/export');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Donation Source', $content);
        $this->assertStringNotContainsString('Income Source', $content);
    }

    public function test_export_csv_has_correct_headers(): void
    {
        $response = $this->actingAs($this->admin)->get('/donations/export');

        $content = $response->streamedContent();
        // CSV should contain the header row (after BOM)
        $this->assertStringContainsString('Дата', $content);
        $this->assertStringContainsString('Донатор', $content);
        $this->assertStringContainsString('Сума', $content);
    }

    public function test_export_shows_anonymous_as_anonim(): void
    {
        Transaction::factory()->forChurch($this->church)->create([
            'source_type' => Transaction::SOURCE_DONATION,
            'direction' => Transaction::DIRECTION_IN,
            'donor_name' => null,
            'is_anonymous' => true,
            'status' => Transaction::STATUS_COMPLETED,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get('/donations/export');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Анонім', $content);
    }

    public function test_guest_cannot_export_donations(): void
    {
        $response = $this->get('/donations/export');

        $response->assertRedirect('/login');
    }

    // ==================
    // Public Donate Success Page
    // ==================

    public function test_donate_success_page_loads_for_public_church(): void
    {
        $this->church->update(['slug' => 'success-church', 'public_site_enabled' => true]);

        $response = $this->get('/c/success-church/donate/success');

        $response->assertStatus(200);
    }

    public function test_donate_success_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/c/nonexistent-church/donate/success');

        $response->assertStatus(404);
    }

    public function test_donate_success_returns_404_when_public_site_disabled(): void
    {
        $this->church->update(['slug' => 'disabled-church', 'public_site_enabled' => false]);

        $response = $this->get('/c/disabled-church/donate/success');

        $response->assertStatus(404);
    }

    // ==================
    // Multi-tenancy: Campaign CRUD isolation
    // ==================

    public function test_double_toggle_returns_to_original_state(): void
    {
        $campaign = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Double Toggle',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/donations/campaigns/{$campaign->id}/toggle");
        $this->assertFalse($campaign->fresh()->is_active);

        $this->actingAs($this->admin)
            ->postJson("/donations/campaigns/{$campaign->id}/toggle");
        $this->assertTrue($campaign->fresh()->is_active);
    }

    public function test_deleting_campaign_does_not_affect_other_campaigns(): void
    {
        $campaign1 = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Keep This',
            'is_active' => true,
        ]);
        $campaign2 = DonationCampaign::create([
            'church_id' => $this->church->id,
            'name' => 'Delete This',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson("/donations/campaigns/{$campaign2->id}");

        $this->assertDatabaseHas('donation_campaigns', ['id' => $campaign1->id]);
        $this->assertDatabaseMissing('donation_campaigns', ['id' => $campaign2->id]);
    }
}
