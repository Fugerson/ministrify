<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Church;
use App\Models\Event;
use App\Models\Group;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
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
    // Auth
    // ==================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->getJson('/search?q=test');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_quick_actions(): void
    {
        $response = $this->getJson('/quick-actions');

        $response->assertStatus(401);
    }

    // ==================
    // Search — short query
    // ==================

    public function test_search_with_short_query_returns_empty_results(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/search?q=a');

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }

    public function test_search_with_empty_query_returns_empty_results(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/search');

        $response->assertOk();
        $response->assertJson(['results' => []]);
    }

    // ==================
    // Search — People
    // ==================

    public function test_search_finds_person_by_first_name(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Олександр',
            'last_name' => 'Петров',
            'membership_status' => 'member',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=Олександр');

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['type' => 'person', 'title' => 'Олександр Петров']);
    }

    public function test_search_finds_person_by_last_name(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Іван',
            'last_name' => 'Шевченко',
            'membership_status' => 'member',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=Шевченко');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'person']);
    }

    public function test_search_finds_person_by_email(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'unique-search-test@example.com',
            'membership_status' => 'member',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=unique-search-test');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'person']);
    }

    public function test_search_finds_person_by_phone(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Phone',
            'last_name' => 'Person',
            'phone' => '+380501234567',
            'membership_status' => 'member',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=050123');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'person']);
    }

    public function test_search_excludes_guests(): void
    {
        Person::factory()->forChurch($this->church)->guest()->create([
            'first_name' => 'GuestPerson',
            'last_name' => 'Hidden',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=GuestPerson');

        $response->assertOk();
        $personResults = collect($response->json('results'))->where('type', 'person');
        $this->assertCount(0, $personResults);
    }

    // ==================
    // Multi-tenancy — People
    // ==================

    public function test_search_does_not_find_person_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        Person::factory()->forChurch($otherChurch)->create([
            'first_name' => 'OtherChurchMember',
            'last_name' => 'Secret',
            'membership_status' => 'member',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=OtherChurchMember');

        $response->assertOk();
        $personResults = collect($response->json('results'))->where('type', 'person');
        $this->assertCount(0, $personResults);
    }

    // ==================
    // Search — Ministries (requires permission)
    // ==================

    public function test_search_finds_ministry_for_user_with_permission(): void
    {
        Ministry::factory()->forChurch($this->church)->create([
            'name' => 'UniqueWorshipMinistry',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=UniqueWorship');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'ministry']);
    }

    public function test_search_does_not_return_ministries_without_permission(): void
    {
        Ministry::factory()->forChurch($this->church)->create([
            'name' => 'HiddenMinistry',
        ]);

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)->getJson('/search?q=HiddenMinistry');

        $response->assertOk();
        $ministryResults = collect($response->json('results'))->where('type', 'ministry');
        $this->assertCount(0, $ministryResults);
    }

    public function test_search_does_not_find_ministry_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        Ministry::factory()->forChurch($otherChurch)->create([
            'name' => 'OtherChurchMinistry',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=OtherChurchMinistry');

        $response->assertOk();
        $ministryResults = collect($response->json('results'))->where('type', 'ministry');
        $this->assertCount(0, $ministryResults);
    }

    // ==================
    // Search — Groups (requires permission)
    // ==================

    public function test_search_finds_group_for_user_with_permission(): void
    {
        Group::factory()->forChurch($this->church)->create([
            'name' => 'UniqueTestGroup',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=UniqueTestGroup');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'group']);
    }

    public function test_search_does_not_return_groups_without_permission(): void
    {
        Group::factory()->forChurch($this->church)->create([
            'name' => 'SecretGroup',
        ]);

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)->getJson('/search?q=SecretGroup');

        $response->assertOk();
        $groupResults = collect($response->json('results'))->where('type', 'group');
        $this->assertCount(0, $groupResults);
    }

    // ==================
    // Search — Events (requires permission)
    // ==================

    public function test_search_finds_recent_event_for_user_with_permission(): void
    {
        Event::factory()->forChurch($this->church)->create([
            'title' => 'UniqueConference2025',
            'date' => now()->addDays(3),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=UniqueConference');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'event']);
    }

    public function test_search_does_not_return_events_without_permission(): void
    {
        Event::factory()->forChurch($this->church)->create([
            'title' => 'HiddenEvent',
            'date' => now()->addDays(1),
        ]);

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)->getJson('/search?q=HiddenEvent');

        $response->assertOk();
        $eventResults = collect($response->json('results'))->where('type', 'event');
        $this->assertCount(0, $eventResults);
    }

    public function test_search_does_not_find_old_events(): void
    {
        Event::factory()->forChurch($this->church)->create([
            'title' => 'AncientEvent',
            'date' => now()->subDays(30),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=AncientEvent');

        $response->assertOk();
        $eventResults = collect($response->json('results'))->where('type', 'event');
        $this->assertCount(0, $eventResults);
    }

    // ==================
    // Search — Boards (requires permission)
    // ==================

    public function test_search_finds_board_for_user_with_permission(): void
    {
        Board::create([
            'church_id' => $this->church->id,
            'name' => 'UniqueProjectBoard',
            'is_archived' => false,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=UniqueProject');

        $response->assertOk();
        $response->assertJsonFragment(['type' => 'board']);
    }

    public function test_search_does_not_return_boards_without_permission(): void
    {
        Board::create([
            'church_id' => $this->church->id,
            'name' => 'SecretBoard',
            'is_archived' => false,
        ]);

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)->getJson('/search?q=SecretBoard');

        $response->assertOk();
        $boardResults = collect($response->json('results'))->where('type', 'board');
        $this->assertCount(0, $boardResults);
    }

    public function test_search_does_not_find_archived_boards(): void
    {
        Board::create([
            'church_id' => $this->church->id,
            'name' => 'ArchivedBoard',
            'is_archived' => true,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=ArchivedBoard');

        $response->assertOk();
        $boardResults = collect($response->json('results'))->where('type', 'board');
        $this->assertCount(0, $boardResults);
    }

    // ==================
    // Search — Result structure
    // ==================

    public function test_search_results_contain_expected_fields(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'StructureTest',
            'last_name' => 'Person',
            'membership_status' => 'member',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/search?q=StructureTest');

        $response->assertOk();
        $response->assertJsonStructure([
            'results' => [
                '*' => ['type', 'icon', 'title', 'subtitle', 'url'],
            ],
        ]);
    }

    // ==================
    // Search — Combined results with permissions
    // ==================

    public function test_volunteer_with_partial_permissions_gets_only_allowed_results(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'SharedTerm',
            'last_name' => 'Person',
            'membership_status' => 'member',
        ]);
        Ministry::factory()->forChurch($this->church)->create([
            'name' => 'SharedTerm Ministry',
        ]);
        Group::factory()->forChurch($this->church)->create([
            'name' => 'SharedTerm Group',
        ]);

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        // Only ministries permission, no groups
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->setPermissions(['ministries' => ['view']]);

        $response = $this->actingAs($volunteer)->getJson('/search?q=SharedTerm');

        $response->assertOk();
        $results = collect($response->json('results'));

        // People are always visible to authenticated users
        $this->assertTrue($results->where('type', 'person')->isNotEmpty());
        // Ministries visible with permission
        $this->assertTrue($results->where('type', 'ministry')->isNotEmpty());
        // Groups not visible without permission
        $this->assertTrue($results->where('type', 'group')->isEmpty());
    }

    // ==================
    // Quick Actions
    // ==================

    public function test_quick_actions_returns_actions_array(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/quick-actions');

        $response->assertOk();
        $response->assertJsonStructure([
            'actions' => [
                '*' => ['key', 'label', 'url', 'icon'],
            ],
        ]);
    }

    public function test_quick_actions_includes_new_person_action(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/quick-actions');

        $response->assertOk();
        $actions = collect($response->json('actions'));
        $this->assertTrue($actions->where('key', 'n')->isNotEmpty());
    }

    public function test_quick_actions_includes_new_event_action(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/quick-actions');

        $response->assertOk();
        $actions = collect($response->json('actions'));
        $this->assertTrue($actions->where('key', 'e')->isNotEmpty());
    }

    public function test_quick_actions_includes_expense_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/quick-actions');

        $response->assertOk();
        $actions = collect($response->json('actions'));
        $this->assertTrue($actions->where('key', 'x')->isNotEmpty());
    }

    public function test_quick_actions_excludes_expense_for_volunteer(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->getJson('/quick-actions');

        $response->assertOk();
        $actions = collect($response->json('actions'));
        $this->assertTrue($actions->where('key', 'x')->isEmpty());
    }

    public function test_quick_actions_includes_group_and_board_actions(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/quick-actions');

        $response->assertOk();
        $actions = collect($response->json('actions'));
        $this->assertTrue($actions->where('key', 'g')->isNotEmpty());
        $this->assertTrue($actions->where('key', 'b')->isNotEmpty());
    }
}
