<?php

namespace Tests\Unit\Policies;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Person;
use App\Models\User;
use App\Policies\PersonPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PersonPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PersonPolicy $policy;
    private Church $church;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->policy = new PersonPolicy();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
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

    // ==================
    // viewAny
    // ==================

    public function test_view_any_with_permission(): void
    {
        $user = $this->createUserWithPermission('people', 'view');
        $this->assertTrue($this->policy->viewAny($user));
    }

    // ==================
    // view
    // ==================

    public function test_view_own_profile(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
        ]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->view($user, $person));
    }

    public function test_view_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $person = Person::factory()->forChurch($otherChurch)->create();

        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($this->policy->view($user, $person));
    }

    // ==================
    // create
    // ==================

    public function test_create_with_permission(): void
    {
        $user = $this->createUserWithPermission('people', 'create');
        $this->assertTrue($this->policy->create($user));
    }

    // ==================
    // update
    // ==================

    public function test_update_own_profile(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->update($user, $person));
    }

    public function test_update_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $person = Person::factory()->forChurch($otherChurch)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($this->policy->update($user, $person));
    }

    // ==================
    // delete
    // ==================

    public function test_delete_denied_for_self(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertFalse($this->policy->delete($user, $person));
    }

    public function test_delete_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $person = Person::factory()->forChurch($otherChurch)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($this->policy->delete($user, $person));
    }

    // ==================
    // manageOwn
    // ==================

    public function test_manage_own_true_for_own_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->manageOwn($user, $person));
    }

    public function test_manage_own_false_for_other_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($this->policy->manageOwn($user, $person));
    }

    // ==================
    // viewFinances
    // ==================

    public function test_view_own_finances(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->viewFinances($user, $person));
    }

    public function test_view_finances_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $person = Person::factory()->forChurch($otherChurch)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($this->policy->viewFinances($user, $person));
    }

    // ==================
    // sendMessage
    // ==================

    public function test_send_message_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $person = Person::factory()->forChurch($otherChurch)->create();
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($this->policy->sendMessage($user, $person));
    }
}
