<?php

namespace Tests\Unit\Policies;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TransactionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TransactionPolicy $policy;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->policy = new TransactionPolicy();
        $this->church = Church::factory()->create();
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
        $user = $this->createUserWithPermission('finances', 'view');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $tx = Transaction::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->view($user, $tx));
    }

    public function test_view_own_donation(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $tx = Transaction::factory()->forChurch($this->church)->create([
            'person_id' => $person->id,
        ]);

        $this->assertTrue($this->policy->view($user, $tx));
    }

    public function test_view_as_ministry_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $leaderRole = ChurchRole::where('church_id', $this->church->id)
            ->where('slug', 'leader')
            ->first();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $leaderRole->id,
        ]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $tx = Transaction::factory()->forChurch($this->church)->forMinistry($ministry)->create();

        $this->assertTrue($this->policy->view($user, $tx));
    }

    public function test_create_income_with_permission(): void
    {
        $user = $this->createUserWithPermission('finances', 'create');
        $this->assertTrue($this->policy->createIncome($user));
    }

    public function test_create_expense_with_permission(): void
    {
        $user = $this->createUserWithPermission('finances', 'create');
        $this->assertTrue($this->policy->createExpense($user));
    }

    public function test_update_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $tx = Transaction::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->update($user, $tx));
    }

    public function test_update_as_ministry_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $tx = Transaction::factory()->forChurch($this->church)->forMinistry($ministry)->create();

        $this->assertTrue($this->policy->update($user, $tx));
    }

    public function test_delete_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $tx = Transaction::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->delete($user, $tx));
    }

    public function test_delete_with_permission(): void
    {
        $user = $this->createUserWithPermission('finances', 'delete');
        $tx = Transaction::factory()->forChurch($this->church)->create();

        $this->assertTrue($this->policy->delete($user, $tx));
    }

    public function test_view_reports_with_permission(): void
    {
        $user = $this->createUserWithPermission('finances', 'view');
        $this->assertTrue($this->policy->viewReports($user));
    }

    public function test_export_with_permission(): void
    {
        $user = $this->createUserWithPermission('finances', 'view');
        $this->assertTrue($this->policy->export($user));
    }

    public function test_manage_categories_with_permission(): void
    {
        $user = $this->createUserWithPermission('finances', 'edit');
        $this->assertTrue($this->policy->manageCategories($user));
    }
}
