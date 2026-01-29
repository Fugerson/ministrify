<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\Expense;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryTest extends TestCase
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
    // Budget
    // ==================

    public function test_can_add_expense_without_budget(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => null,
        ]);

        $result = $ministry->canAddExpense(500);
        $this->assertTrue($result['allowed']);
        $this->assertNull($result['warning']);
    }

    public function test_can_add_expense_within_budget(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 10000,
        ]);

        $result = $ministry->canAddExpense(2000);
        $this->assertTrue($result['allowed']);
        $this->assertNull($result['warning']);
    }

    public function test_can_add_expense_warning_at_80_percent(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 10000,
        ]);

        // Create existing expenses to bring to 75%
        Expense::create([
            'church_id' => $this->church->id,
            'ministry_id' => $ministry->id,
            'user_id' => $this->admin->id,
            'amount' => 7500,
            'description' => 'Test',
            'date' => now(),
        ]);

        $result = $ministry->canAddExpense(600);
        $this->assertTrue($result['allowed']);
        $this->assertEquals('high', $result['warning']);
    }

    public function test_can_add_expense_exceeds_budget(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 10000,
        ]);

        Expense::create([
            'church_id' => $this->church->id,
            'ministry_id' => $ministry->id,
            'user_id' => $this->admin->id,
            'amount' => 9500,
            'description' => 'Test',
            'date' => now(),
        ]);

        $result = $ministry->canAddExpense(1000);
        $this->assertFalse($result['allowed']);
        $this->assertEquals('exceeded', $result['warning']);
    }

    public function test_is_budget_warning(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 10000,
        ]);

        Expense::create([
            'church_id' => $this->church->id,
            'ministry_id' => $ministry->id,
            'user_id' => $this->admin->id,
            'amount' => 8500,
            'description' => 'Test',
            'date' => now(),
        ]);

        $this->assertTrue($ministry->isBudgetWarning());
    }

    public function test_is_budget_exceeded(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 5000,
        ]);

        Expense::create([
            'church_id' => $this->church->id,
            'ministry_id' => $ministry->id,
            'user_id' => $this->admin->id,
            'amount' => 5500,
            'description' => 'Test',
            'date' => now(),
        ]);

        $this->assertTrue($ministry->isBudgetExceeded());
    }

    public function test_budget_usage_percent(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 10000,
        ]);

        Expense::create([
            'church_id' => $this->church->id,
            'ministry_id' => $ministry->id,
            'user_id' => $this->admin->id,
            'amount' => 5000,
            'description' => 'Test',
            'date' => now(),
        ]);

        $this->assertEquals(50.0, $ministry->budget_usage_percent);
    }

    public function test_budget_usage_percent_zero_budget(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 0,
        ]);

        $this->assertEquals(0, $ministry->budget_usage_percent);
    }

    public function test_remaining_budget(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'monthly_budget' => 10000,
        ]);

        Expense::create([
            'church_id' => $this->church->id,
            'ministry_id' => $ministry->id,
            'user_id' => $this->admin->id,
            'amount' => 3000,
            'description' => 'Test',
            'date' => now(),
        ]);

        $this->assertEquals(7000, $ministry->remaining_budget);
    }

    // ==================
    // Visibility / Access
    // ==================

    public function test_can_access_public_ministry(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_PUBLIC,
        ]);
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($ministry->canAccess($user));
    }

    public function test_admin_can_access_any_ministry(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_SPECIFIC,
        ]);

        // Use the admin user created by createChurchWithAdmin() in setUp
        $this->assertTrue($ministry->canAccess($this->admin));
    }

    public function test_members_visibility_allows_member(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_MEMBERS,
        ]);
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);
        $ministry->members()->attach($person);

        $this->assertTrue($ministry->canAccess($user));
    }

    public function test_members_visibility_blocks_outsider(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_MEMBERS,
        ]);
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);

        $this->assertFalse($ministry->canAccess($user));
    }

    public function test_specific_visibility_allows_listed_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);

        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_SPECIFIC,
            'allowed_person_ids' => [$person->id],
        ]);

        $this->assertTrue($ministry->canAccess($user));
    }

    public function test_guest_cannot_access(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'visibility' => Ministry::VISIBILITY_PUBLIC,
        ]);

        $this->assertFalse($ministry->canAccess(null));
    }

    // ==================
    // isMember
    // ==================

    public function test_is_member_for_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'leader']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);

        $ministry = Ministry::factory()->forChurch($this->church)->withLeader($person)->create();

        $this->assertTrue($ministry->isMember($user));
    }

    public function test_is_member_for_member(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);

        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $ministry->members()->attach($person);

        $this->assertTrue($ministry->isMember($user));
    }

    public function test_is_member_false_for_outsider(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);

        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $this->assertFalse($ministry->isMember($user));
    }

    public function test_is_member_false_without_person(): void
    {
        $user = User::factory()->create([
            'church_id' => $this->church->id,
        ]);
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $this->assertFalse($ministry->isMember($user));
    }
}
