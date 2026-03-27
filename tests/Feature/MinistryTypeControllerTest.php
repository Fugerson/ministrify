<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryTypeControllerTest extends TestCase
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
    // Store
    // ==================

    public function test_admin_can_create_ministry_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/settings/ministry-types', [
            'name' => 'Worship',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministry_types', [
            'church_id' => $this->church->id,
            'name' => 'Worship',
        ]);
    }

    public function test_store_sets_sort_order_incrementally(): void
    {
        MinistryType::create(['church_id' => $this->church->id, 'name' => 'First', 'sort_order' => 0]);

        $this->actingAs($this->admin)->post('/settings/ministry-types', [
            'name' => 'Second',
        ]);

        $second = MinistryType::where('church_id', $this->church->id)->where('name', 'Second')->first();
        $this->assertNotNull($second);
        $this->assertEquals(1, $second->sort_order);
    }

    public function test_store_validates_name_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/settings/ministry-types', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validates_name_max_length(): void
    {
        $response = $this->actingAs($this->admin)->post('/settings/ministry-types', [
            'name' => str_repeat('A', 256),
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_volunteer_cannot_create_ministry_type(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->post('/settings/ministry-types', [
            'name' => 'Worship',
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_ministry_type(): void
    {
        $response = $this->post('/settings/ministry-types', [
            'name' => 'Worship',
        ]);

        $response->assertRedirect('/login');
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_ministry_type(): void
    {
        $type = MinistryType::create(['church_id' => $this->church->id, 'name' => 'Worship', 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->put("/settings/ministry-types/{$type->id}", [
            'name' => 'Praise & Worship',
        ]);

        $response->assertRedirect();
        $type->refresh();
        $this->assertEquals('Praise & Worship', $type->name);
    }

    public function test_cannot_update_ministry_type_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $type = MinistryType::create(['church_id' => $otherChurch->id, 'name' => 'Other', 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->put("/settings/ministry-types/{$type->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(404);
        $type->refresh();
        $this->assertEquals('Other', $type->name);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_ministry_type(): void
    {
        $type = MinistryType::create(['church_id' => $this->church->id, 'name' => 'Worship', 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->delete("/settings/ministry-types/{$type->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('ministry_types', ['id' => $type->id]);
    }

    public function test_cannot_delete_type_used_by_ministries(): void
    {
        $type = MinistryType::create(['church_id' => $this->church->id, 'name' => 'Worship', 'sort_order' => 0]);
        Ministry::factory()->forChurch($this->church)->create(['type_id' => $type->id]);

        $response = $this->actingAs($this->admin)->delete("/settings/ministry-types/{$type->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('ministry_types', ['id' => $type->id]);
    }

    public function test_cannot_delete_ministry_type_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $type = MinistryType::create(['church_id' => $otherChurch->id, 'name' => 'Other', 'sort_order' => 0]);

        $response = $this->actingAs($this->admin)->delete("/settings/ministry-types/{$type->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('ministry_types', ['id' => $type->id]);
    }

    // ==================
    // Update Ministry Type
    // ==================

    public function test_admin_can_assign_type_to_ministry(): void
    {
        $type = MinistryType::create(['church_id' => $this->church->id, 'name' => 'Worship', 'sort_order' => 0]);
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->put("/settings/ministries/{$ministry->id}/type", [
            'type_id' => $type->id,
        ]);

        $response->assertRedirect();
        $ministry->refresh();
        $this->assertEquals($type->id, $ministry->type_id);
    }

    public function test_admin_can_clear_type_from_ministry(): void
    {
        $type = MinistryType::create(['church_id' => $this->church->id, 'name' => 'Worship', 'sort_order' => 0]);
        $ministry = Ministry::factory()->forChurch($this->church)->create(['type_id' => $type->id]);

        $response = $this->actingAs($this->admin)->put("/settings/ministries/{$ministry->id}/type", [
            'type_id' => null,
        ]);

        $response->assertRedirect();
        $ministry->refresh();
        $this->assertNull($ministry->type_id);
    }

    public function test_cannot_assign_type_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherType = MinistryType::create(['church_id' => $otherChurch->id, 'name' => 'Foreign', 'sort_order' => 0]);
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->put("/settings/ministries/{$ministry->id}/type", [
            'type_id' => $otherType->id,
        ]);

        // Should fail validation or return 404
        $ministry->refresh();
        $this->assertNull($ministry->type_id);
    }

    public function test_cannot_update_type_for_ministry_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $ministry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->put("/settings/ministries/{$ministry->id}/type", [
            'type_id' => null,
        ]);

        $response->assertStatus(404);
    }

    // ==================
    // Destroy Ministry
    // ==================

    public function test_admin_can_delete_ministry_from_settings(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->delete("/settings/ministries/{$ministry->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('ministries', ['id' => $ministry->id]);
    }

    public function test_cannot_delete_ministry_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $ministry = Ministry::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->delete("/settings/ministries/{$ministry->id}");

        $response->assertStatus(404);
    }
}
