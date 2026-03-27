<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShepherdControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        // Enable shepherds by default for most tests
        $this->church->update(['shepherds_enabled' => true]);
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_shepherds_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/settings/shepherds');

        $response->assertStatus(200);
        $response->assertViewHas('shepherds');
        $response->assertViewHas('availablePeople');
    }

    public function test_index_redirects_when_shepherds_disabled(): void
    {
        $this->church->update(['shepherds_enabled' => false]);

        $response = $this->actingAs($this->admin)->get('/settings/shepherds');

        $response->assertRedirect(route('settings.index'));
    }

    public function test_index_shows_shepherds_with_sheep_count(): void
    {
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();
        Person::factory()->forChurch($this->church)->create(['shepherd_id' => $shepherd->id]);
        Person::factory()->forChurch($this->church)->create(['shepherd_id' => $shepherd->id]);

        $response = $this->actingAs($this->admin)->get('/settings/shepherds');

        $response->assertStatus(200);
        $response->assertViewHas('shepherds', function ($shepherds) use ($shepherd) {
            $s = $shepherds->firstWhere('id', $shepherd->id);

            return $s && $s->sheep_count === 2;
        });
    }

    public function test_volunteer_without_settings_permission_gets_403(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/settings/shepherds');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_shepherds(): void
    {
        $response = $this->get('/settings/shepherds');

        $response->assertRedirect('/login');
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_add_shepherd(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'is_shepherd' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds', [
            'person_id' => $person->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'shepherd']);

        $person->refresh();
        $this->assertTrue($person->is_shepherd);
    }

    public function test_store_sets_is_shepherd_to_true(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'is_shepherd' => false,
        ]);

        $this->actingAs($this->admin)->postJson('/settings/shepherds', [
            'person_id' => $person->id,
        ]);

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'is_shepherd' => true,
        ]);
    }

    public function test_store_returns_shepherd_data_in_json(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_shepherd' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds', [
            'person_id' => $person->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('shepherd.id', $person->id);
        $response->assertJsonPath('shepherd.full_name', $person->full_name);
        $response->assertJsonPath('shepherd.sheep_count', 0);
    }

    public function test_store_validates_person_id_required(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('person_id');
    }

    public function test_store_rejects_person_from_another_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds', [
            'person_id' => $otherPerson->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_store_returns_400_when_feature_disabled(): void
    {
        $this->church->update(['shepherds_enabled' => false]);

        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds', [
            'person_id' => $person->id,
        ]);

        $response->assertStatus(400);
    }

    public function test_volunteer_cannot_add_shepherd(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($volunteer)->postJson('/settings/shepherds', [
            'person_id' => $person->id,
        ]);

        $response->assertStatus(403);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_remove_shepherd(): void
    {
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/settings/shepherds/{$shepherd->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $shepherd->refresh();
        $this->assertFalse($shepherd->is_shepherd);
    }

    public function test_destroy_unassigns_all_sheep(): void
    {
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();
        $sheep1 = Person::factory()->forChurch($this->church)->create(['shepherd_id' => $shepherd->id]);
        $sheep2 = Person::factory()->forChurch($this->church)->create(['shepherd_id' => $shepherd->id]);

        $this->actingAs($this->admin)->deleteJson("/settings/shepherds/{$shepherd->id}");

        $sheep1->refresh();
        $sheep2->refresh();
        $this->assertNull($sheep1->shepherd_id);
        $this->assertNull($sheep2->shepherd_id);
    }

    public function test_cannot_remove_shepherd_from_another_church(): void
    {
        $otherChurch = Church::factory()->create(['shepherds_enabled' => true]);
        $otherShepherd = Person::factory()->forChurch($otherChurch)->shepherd()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/settings/shepherds/{$otherShepherd->id}");

        $response->assertStatus(404);
        $otherShepherd->refresh();
        $this->assertTrue($otherShepherd->is_shepherd);
    }

    public function test_volunteer_cannot_remove_shepherd(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();

        $response = $this->actingAs($volunteer)->deleteJson("/settings/shepherds/{$shepherd->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Toggle Feature
    // ==================

    public function test_admin_can_enable_shepherds_feature(): void
    {
        $this->church->update(['shepherds_enabled' => false]);

        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds/toggle-feature', [
            'enabled' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->church->refresh();
        $this->assertTrue($this->church->shepherds_enabled);
    }

    public function test_admin_can_disable_shepherds_feature(): void
    {
        $this->church->update(['shepherds_enabled' => true]);

        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds/toggle-feature', [
            'enabled' => false,
        ]);

        $response->assertStatus(200);

        $this->church->refresh();
        $this->assertFalse($this->church->shepherds_enabled);
    }

    public function test_toggle_feature_validates_enabled_required(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds/toggle-feature', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('enabled');
    }

    public function test_toggle_feature_validates_enabled_is_boolean(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/settings/shepherds/toggle-feature', [
            'enabled' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('enabled');
    }

    public function test_volunteer_cannot_toggle_feature(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->postJson('/settings/shepherds/toggle-feature', [
            'enabled' => true,
        ]);

        $response->assertStatus(403);
    }
}
