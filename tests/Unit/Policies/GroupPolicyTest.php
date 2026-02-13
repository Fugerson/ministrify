<?php

namespace Tests\Unit\Policies;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Group;
use App\Models\Person;
use App\Models\User;
use App\Policies\GroupPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GroupPolicyTest extends TestCase
{
    use RefreshDatabase;

    private GroupPolicy $policy;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->policy = new GroupPolicy();
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
        $user = $this->createUserWithPermission('groups', 'view');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $group = Group::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->view($user, $group));
    }

    public function test_create_with_permission(): void
    {
        $user = $this->createUserWithPermission('groups', 'create');
        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_as_group_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $group = Group::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->update($user, $group));
    }

    public function test_update_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $group = Group::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->update($user, $group));
    }

    public function test_delete_as_group_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $group = Group::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->delete($user, $group));
    }

    public function test_delete_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $group = Group::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->delete($user, $group));
    }

    public function test_delete_with_permission(): void
    {
        $user = $this->createUserWithPermission('groups', 'delete');
        $group = Group::factory()->forChurch($this->church)->create();

        $this->assertTrue($this->policy->delete($user, $group));
    }
}
