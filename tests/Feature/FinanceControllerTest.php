<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceControllerTest extends TestCase
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
    // Dashboard / Index
    // ==================

    public function test_admin_can_view_finance_dashboard(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('FinanceController uses MySQL-specific MONTH() function');
        }

        $response = $this->actingAs($this->admin)->get('/finances');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_finances(): void
    {
        $response = $this->get('/finances');

        $response->assertRedirect('/login');
    }

    public function test_volunteer_cannot_view_finances(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/finances');

        // role:admin,leader middleware redirects
        $response->assertStatus(403);
    }

    public function test_leader_can_view_finances(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('FinanceController uses MySQL-specific MONTH() function');
        }

        $leader = $this->createUserWithRole($this->church, 'leader');

        $response = $this->actingAs($leader)->get('/finances');

        $response->assertStatus(200);
    }

    // ==================
    // Income CRUD
    // ==================

    public function test_admin_can_create_income(): void
    {
        $category = TransactionCategory::factory()->forChurch($this->church)->income()->create();

        $response = $this->actingAs($this->admin)->post('/finances/incomes', [
            'amount' => 5000,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Sunday offering',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'church_id' => $this->church->id,
            'amount' => 5000,
            'direction' => 'in',
        ]);
    }

    // ==================
    // Expense CRUD
    // ==================

    public function test_admin_can_create_expense(): void
    {
        $category = TransactionCategory::factory()->forChurch($this->church)->expense()->create();
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->post('/finances/expenses', [
            'amount' => 2000,
            'category_id' => $category->id,
            'ministry_id' => $ministry->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Equipment purchase',
            'source_type' => 'expense',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'church_id' => $this->church->id,
            'amount' => 2000,
            'direction' => 'out',
        ]);
    }

    // ==================
    // Filters
    // ==================

    public function test_finance_dashboard_filters_by_month(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('FinanceController uses MySQL-specific MONTH() function');
        }

        $response = $this->actingAs($this->admin)->get('/finances?year=2025&month=6');

        $response->assertStatus(200);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_finance_dashboard_shows_only_own_church_data(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('FinanceController uses MySQL-specific MONTH() function');
        }

        $otherChurch = Church::factory()->create();
        Transaction::factory()->forChurch($otherChurch)->income()->completed()->create([
            'amount' => 99999,
        ]);

        $response = $this->actingAs($this->admin)->get('/finances');

        $response->assertStatus(200);
        // The other church's transaction should not appear
        $response->assertDontSee('99999');
    }

    // ==================
    // Budgets
    // ==================

    public function test_admin_can_view_budgets(): void
    {
        $response = $this->actingAs($this->admin)->get('/finances/budgets');

        $response->assertStatus(200);
    }

    // ==================
    // Validation
    // ==================

    public function test_income_requires_amount(): void
    {
        $response = $this->actingAs($this->admin)->post('/finances/incomes', [
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('amount');
    }
}
