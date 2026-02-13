<?php

namespace Tests\Unit\Policies;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use App\Policies\EventPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    private EventPolicy $policy;
    private Church $church;
    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->policy = new EventPolicy();
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
        $user = $this->createUserWithPermission('events', 'view');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertFalse($this->policy->view($user, $event));
    }

    public function test_create_as_ministry_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $this->ministry->update(['leader_id' => $person->id]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_own_event(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now(),
            'created_by' => $user->id,
        ]);

        $this->assertTrue($this->policy->update($user, $event));
    }

    public function test_update_as_ministry_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $this->ministry->update(['leader_id' => $person->id]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertTrue($this->policy->update($user, $event));
    }

    public function test_update_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertFalse($this->policy->update($user, $event));
    }

    public function test_delete_own_event(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $event = Event::factory()->forMinistry($this->ministry)->create([
            'date' => now(),
            'created_by' => $user->id,
        ]);

        $this->assertTrue($this->policy->delete($user, $event));
    }

    public function test_delete_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertFalse($this->policy->delete($user, $event));
    }

    public function test_manage_plan_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertFalse($this->policy->managePlan($user, $event));
    }

    public function test_manage_plan_with_edit_permission(): void
    {
        $user = $this->createUserWithPermission('events', 'edit');
        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertTrue($this->policy->managePlan($user, $event));
    }

    public function test_manage_plan_as_ministry_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $this->ministry->update(['leader_id' => $person->id]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $event = Event::factory()->forMinistry($this->ministry)->create(['date' => now()]);

        $this->assertTrue($this->policy->managePlan($user, $event));
    }
}
