<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // Role Checks
    // ==================

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin_role(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_returns_false_without_role(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_leader_returns_true_for_leader_slug(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'leader', 'name' => 'Leader']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->isLeader());
    }

    public function test_is_leader_returns_true_for_ukrainian_name(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create([
            'slug' => 'custom-leader',
            'name' => 'Старший лідер',
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->isLeader());
    }

    public function test_is_leader_returns_false_for_admin(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertFalse($user->isLeader());
    }

    public function test_is_volunteer_returns_true_for_non_admin_non_leader(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create([
            'slug' => 'volunteer',
            'name' => 'Волонтер',
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->isVolunteer());
    }

    public function test_is_volunteer_returns_false_for_admin(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertFalse($user->isVolunteer());
    }

    public function test_is_volunteer_returns_false_without_role(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($user->isVolunteer());
    }

    // ==================
    // hasRole
    // ==================

    public function test_has_role_with_single_string(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('leader'));
    }

    public function test_has_role_with_array(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'leader']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasRole(['admin', 'leader']));
        $this->assertFalse($user->hasRole(['admin', 'volunteer']));
    }

    public function test_has_role_matches_by_slug(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'musician']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasRole('musician'));
    }

    public function test_has_role_returns_false_without_role(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertFalse($user->hasRole('admin'));
    }

    // ==================
    // Permissions
    // ==================

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'is_super_admin' => true,
        ]);

        $this->assertTrue($user->hasPermission('finances', 'delete'));
        $this->assertTrue($user->hasPermission('people', 'create'));
        $this->assertTrue($user->hasPermission('settings', 'edit'));
    }

    public function test_admin_role_has_all_permissions(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasPermission('finances', 'delete'));
        $this->assertTrue($user->hasPermission('people', 'create'));
    }

    public function test_custom_role_checks_specific_permissions(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'volunteer']);
        ChurchRolePermission::create([
            'church_role_id' => $role->id,
            'module' => 'people',
            'actions' => ['view'],
        ]);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->hasPermission('people', 'view'));
        $this->assertFalse($user->hasPermission('people', 'create'));
        $this->assertFalse($user->hasPermission('finances', 'view'));
    }

    public function test_user_without_church_has_no_permissions(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('SQLite does not allow nullable church_id (migration skipped)');
        }

        $user = User::factory()->create(['church_id' => null]);

        $this->assertFalse($user->hasPermission('people', 'view'));
    }

    public function test_can_view_shortcut(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertTrue($user->canView('finances'));
        $this->assertTrue($user->canCreate('finances'));
        $this->assertTrue($user->canEdit('finances'));
        $this->assertTrue($user->canDelete('finances'));
    }

    // ==================
    // canManageMinistry
    // ==================

    public function test_admin_can_manage_any_ministry(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $this->assertTrue($user->canManageMinistry($ministry));
    }

    public function test_leader_can_manage_own_ministry(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'leader']);
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);
        $ministry = Ministry::factory()->forChurch($this->church)->withLeader($person)->create();

        $this->assertTrue($user->canManageMinistry($ministry));
    }

    public function test_leader_cannot_manage_other_ministry(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['slug' => 'leader']);
        $person = Person::factory()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);
        $person->update(['user_id' => $user->id]);
        $ministry = Ministry::factory()->forChurch($this->church)->create(); // no leader set

        $this->assertFalse($user->canManageMinistry($ministry));
    }

    // ==================
    // Onboarding
    // ==================

    public function test_start_onboarding_initializes_state(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
            'onboarding_completed' => false,
        ]);

        $user->startOnboarding();
        $user->refresh();

        $this->assertEquals('welcome', $user->getCurrentOnboardingStep());
        $this->assertNotNull($user->onboarding_started_at);

        $state = $user->onboarding_state;
        $this->assertArrayHasKey('steps', $state);
        $this->assertCount(count(User::ONBOARDING_STEPS), $state['steps']);
    }

    public function test_complete_onboarding_step_advances(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
            'onboarding_completed' => false,
        ]);
        $user->startOnboarding();

        $user->completeOnboardingStep('welcome');
        $user->refresh();

        $this->assertEquals('church_profile', $user->getCurrentOnboardingStep());
        $step = $user->getOnboardingStep('welcome');
        $this->assertTrue($step['completed']);
    }

    public function test_skip_onboarding_step_skips_non_required(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
            'onboarding_completed' => false,
        ]);
        $user->startOnboarding();
        $user->completeOnboardingStep('welcome');
        $user->completeOnboardingStep('church_profile');

        // first_ministry is not required, so can skip
        $user->skipOnboardingStep('first_ministry');
        $user->refresh();

        $step = $user->getOnboardingStep('first_ministry');
        $this->assertTrue($step['skipped']);
        $this->assertEquals('add_people', $user->getCurrentOnboardingStep());
    }

    public function test_skip_required_step_does_nothing(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
            'onboarding_completed' => false,
        ]);
        $user->startOnboarding();

        // welcome is required, cannot skip
        $user->skipOnboardingStep('welcome');
        $user->refresh();

        $this->assertEquals('welcome', $user->getCurrentOnboardingStep());
    }

    public function test_onboarding_progress_calculation(): void
    {
        $role = ChurchRole::factory()->admin()->forChurch($this->church)->create();
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
            'onboarding_completed' => false,
        ]);
        $user->startOnboarding();

        $progress = $user->getOnboardingProgress();
        $this->assertEquals(0, $progress['completed']);
        $this->assertEquals(count(User::ONBOARDING_STEPS), $progress['total']);
        $this->assertEquals(0, $progress['percentage']);

        $user->completeOnboardingStep('welcome');
        $user->refresh();

        $progress = $user->getOnboardingProgress();
        $this->assertEquals(1, $progress['completed']);
    }

    // ==================
    // Super Admin
    // ==================

    public function test_is_super_admin(): void
    {
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'is_super_admin' => true,
        ]);
        $this->assertTrue($user->isSuperAdmin());

        $user2 = User::factory()->create([
            'church_id' => $this->church->id,
            'is_super_admin' => false,
        ]);
        $this->assertFalse($user2->isSuperAdmin());
    }

    // ==================
    // Role Name Attribute
    // ==================

    public function test_role_name_attribute_with_role(): void
    {
        $role = ChurchRole::factory()->forChurch($this->church)->create(['name' => 'Музикант']);
        $user = User::factory()->create([
            'church_id' => $this->church->id,
            'church_role_id' => $role->id,
        ]);

        $this->assertEquals('Музикант', $user->role_name);
    }

    public function test_role_name_attribute_without_role(): void
    {
        $user = User::factory()->create(['church_id' => $this->church->id]);

        $this->assertEquals('Без ролі', $user->role_name);
    }
}
