<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private User $admin;
    private Ministry $ministry;
    private TransactionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->church = Church::factory()->create();
        $this->admin = User::factory()->admin()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
        ]);
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
        $this->category = TransactionCategory::factory()
            ->forChurch($this->church)
            ->expense()
            ->create();
    }

    public function test_admin_can_view_expenses_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/finances/expenses');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_expenses(): void
    {
        $response = $this->get('/finances/expenses');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_create_expense(): void
    {
        $expenseData = [
            'ministry_id' => $this->ministry->id,
            'amount' => 1500.00,
            'description' => 'Test expense',
            'category_id' => $this->category->id,
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)->post('/finances/expenses', $expenseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'church_id' => $this->church->id,
            'ministry_id' => $this->ministry->id,
            'amount' => 1500.00,
            'direction' => Transaction::DIRECTION_OUT,
        ]);
    }

    public function test_admin_cannot_create_expense_for_other_church_ministry(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();

        $expenseData = [
            'ministry_id' => $otherMinistry->id,
            'amount' => 1500.00,
            'description' => 'Test expense',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)->post('/finances/expenses', $expenseData);

        $response->assertSessionHasErrors('ministry_id');
        $this->assertDatabaseMissing('transactions', [
            'ministry_id' => $otherMinistry->id,
        ]);
    }

    public function test_admin_cannot_use_category_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherCategory = TransactionCategory::factory()
            ->forChurch($otherChurch)
            ->expense()
            ->create();

        $expenseData = [
            'ministry_id' => $this->ministry->id,
            'amount' => 1500.00,
            'description' => 'Test expense',
            'category_id' => $otherCategory->id,
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)->post('/finances/expenses', $expenseData);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_leader_can_create_expense_for_own_ministry(): void
    {
        $leader = User::factory()->leader()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
        ]);
        $leader->refresh();
        $leaderPerson = Person::factory()->forChurch($this->church)->create([
            'user_id' => $leader->id,
        ]);

        $leaderMinistry = Ministry::factory()
            ->forChurch($this->church)
            ->withLeader($leaderPerson)
            ->create();

        $expenseData = [
            'ministry_id' => $leaderMinistry->id,
            'amount' => 500.00,
            'description' => 'Leader expense',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($leader)->post('/finances/expenses', $expenseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'ministry_id' => $leaderMinistry->id,
            'amount' => 500.00,
        ]);
    }

    public function test_admin_can_update_expense(): void
    {
        $expense = Transaction::factory()
            ->forChurch($this->church)
            ->forMinistry($this->ministry)
            ->expense()
            ->create();

        $updateData = [
            'ministry_id' => $this->ministry->id,
            'amount' => 2000.00,
            'description' => 'Updated expense',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)
            ->put("/finances/expenses/{$expense->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'id' => $expense->id,
            'amount' => 2000.00,
            'description' => 'Updated expense',
        ]);
    }

    public function test_admin_can_delete_expense(): void
    {
        $expense = Transaction::factory()
            ->forChurch($this->church)
            ->forMinistry($this->ministry)
            ->expense()
            ->create();

        $response = $this->actingAs($this->admin)
            ->delete("/finances/expenses/{$expense->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('transactions', ['id' => $expense->id]);
    }

    public function test_volunteer_cannot_access_expenses(): void
    {
        $volunteer = User::factory()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
            'role' => 'volunteer',
        ]);

        $response = $this->actingAs($volunteer)->get('/finances/expenses');

        $response->assertStatus(403);
    }

    public function test_expense_requires_valid_amount(): void
    {
        $expenseData = [
            'ministry_id' => $this->ministry->id,
            'amount' => -100,
            'description' => 'Invalid expense',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)->post('/finances/expenses', $expenseData);

        $response->assertSessionHasErrors('amount');
    }

    public function test_expense_requires_description(): void
    {
        $expenseData = [
            'ministry_id' => $this->ministry->id,
            'amount' => 100,
            'description' => '',
            'date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)->post('/finances/expenses', $expenseData);

        $response->assertSessionHasErrors('description');
    }
}
