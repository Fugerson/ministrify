<?php

namespace Tests\Unit\Policies;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use App\Policies\MinistryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MinistryPolicyTest extends TestCase
{
    use RefreshDatabase;

    private MinistryPolicy $policy;
    private Church $church;
    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->policy = new MinistryPolicy();
        $this->church = Church::factory()->create();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    private function createUserWithPermission(string $module, string $action): User
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create();
        ChurchRolePermission::create([
            'church_role_id' => $role->id,
            'module' => $module,
            'actions' => [$action],
        ]);
        return User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
    }

    public function test_view_any_with_permission(): void
    {
        $user = $this->createUserWithPermission('ministries', 'view');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->view($user, $this->ministry));
    }

    public function test_create_with_permission(): void
    {
        $user = $this->createUserWithPermission('ministries', 'create');
        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->update($user, $this->ministry));
    }

    public function test_delete_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->delete($user, $this->ministry));
    }

    public function test_manage_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->manage($user, $this->ministry));
    }

    public function test_manage_schedule_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->manageSchedule($user, $this->ministry));
    }

    public function test_view_finances_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->viewFinances($user, $this->ministry));
    }

    public function test_add_expense_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);

        $this->assertFalse($this->policy->addExpense($user, $this->ministry));
    }

    public function test_add_expense_with_create_expenses_permission(): void
    {
        $user = $this->createUserWithPermission('expenses', 'create');
        $this->assertTrue($this->policy->addExpense($user, $this->ministry));
    }

    public function test_delete_with_permission(): void
    {
        $user = $this->createUserWithPermission('ministries', 'delete');
        $this->assertTrue($this->policy->delete($user, $this->ministry));
    }
}
