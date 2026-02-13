<?php

namespace Tests\Unit\Policies;

use App\Models\Attendance;
use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use App\Policies\AttendancePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AttendancePolicyTest extends TestCase
{
    use RefreshDatabase;

    private AttendancePolicy $policy;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->policy = new AttendancePolicy();
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
        $user = $this->createUserWithPermission('attendance', 'view');
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_as_group_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $group = Group::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'attendable_type' => Group::class,
            'attendable_id' => $group->id,
        ]);

        $this->assertTrue($this->policy->view($user, $attendance));
    }

    public function test_view_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $attendance = Attendance::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->view($user, $attendance));
    }

    public function test_record_for_group_as_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $group = Group::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->recordForGroup($user, $group));
    }

    public function test_record_for_group_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $group = Group::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->recordForGroup($user, $group));
    }

    public function test_record_for_event_as_ministry_leader(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $ministry = Ministry::factory()->forChurch($this->church)->create([
            'leader_id' => $person->id,
        ]);
        $event = Event::factory()->forMinistry($ministry)->create(['date' => now()]);

        $user = User::factory()->create(['church_id' => $this->church->id]);
        $person->update(['user_id' => $user->id]);
        $user->refresh();

        $this->assertTrue($this->policy->recordForEvent($user, $event));
    }

    public function test_record_for_event_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $event = Event::factory()->forMinistry($ministry)->create(['date' => now()]);

        $this->assertFalse($this->policy->recordForEvent($user, $event));
    }

    public function test_update_by_original_recorder(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);
        $attendance = Attendance::factory()->forChurch($this->church)->create([
            'recorded_by' => $user->id,
        ]);

        $this->assertTrue($this->policy->update($user, $attendance));
    }

    public function test_update_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $attendance = Attendance::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->update($user, $attendance));
    }

    public function test_delete_denied_for_different_church(): void
    {
        $otherChurch = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $otherChurch->id]);
        $attendance = Attendance::factory()->forChurch($this->church)->create();

        $this->assertFalse($this->policy->delete($user, $attendance));
    }

    public function test_delete_with_permission(): void
    {
        $user = $this->createUserWithPermission('attendance', 'delete');
        $attendance = Attendance::factory()->forChurch($this->church)->create();

        $this->assertTrue($this->policy->delete($user, $attendance));
    }

    public function test_view_stats_with_permission(): void
    {
        $user = $this->createUserWithPermission('attendance', 'view');
        $this->assertTrue($this->policy->viewStats($user));
    }
}
