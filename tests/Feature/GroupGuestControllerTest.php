<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Group;
use App\Models\GroupGuest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupGuestControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->group = Group::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_add_guest_to_group(): void
    {
        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/guests", [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'notes' => 'New visitor',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('group_guests', [
            'church_id' => $this->church->id,
            'group_id' => $this->group->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'notes' => 'New visitor',
        ]);
    }

    public function test_store_sets_church_id_automatically(): void
    {
        $this->actingAs($this->admin)->post("/groups/{$this->group->id}/guests", [
            'first_name' => 'Jane',
        ]);

        $guest = GroupGuest::where('group_id', $this->group->id)->first();
        $this->assertNotNull($guest);
        $this->assertEquals($this->church->id, $guest->church_id);
    }

    public function test_store_with_birth_date(): void
    {
        $this->actingAs($this->admin)->post("/groups/{$this->group->id}/guests", [
            'first_name' => 'Anna',
            'birth_date' => '1990-05-15',
        ]);

        $guest = GroupGuest::where('group_id', $this->group->id)
            ->where('first_name', 'Anna')
            ->first();
        $this->assertNotNull($guest);
        $this->assertEquals('1990-05-15', $guest->birth_date->format('Y-m-d'));
    }

    public function test_store_validates_first_name_required(): void
    {
        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/guests", [
            'last_name' => 'Doe',
        ]);

        $response->assertSessionHasErrors(['first_name']);
    }

    public function test_store_validates_first_name_max_length(): void
    {
        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/guests", [
            'first_name' => str_repeat('A', 256),
        ]);

        $response->assertSessionHasErrors(['first_name']);
    }

    public function test_store_validates_birth_date_not_in_future(): void
    {
        $response = $this->actingAs($this->admin)->post("/groups/{$this->group->id}/guests", [
            'first_name' => 'Future',
            'birth_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['birth_date']);
    }

    public function test_guest_user_cannot_add_guest(): void
    {
        $response = $this->post("/groups/{$this->group->id}/guests", [
            'first_name' => 'Anon',
        ]);

        $response->assertRedirect('/login');
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_guest(): void
    {
        $guest = GroupGuest::create([
            'church_id' => $this->church->id,
            'group_id' => $this->group->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->put("/groups/{$this->group->id}/guests/{$guest->id}", [
            'first_name' => 'Johnny',
            'last_name' => 'Smith',
        ]);

        $response->assertRedirect();
        $guest->refresh();
        $this->assertEquals('Johnny', $guest->first_name);
        $this->assertEquals('Smith', $guest->last_name);
    }

    public function test_update_validates_first_name_required(): void
    {
        $guest = GroupGuest::create([
            'church_id' => $this->church->id,
            'group_id' => $this->group->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->put("/groups/{$this->group->id}/guests/{$guest->id}", [
            'first_name' => '',
        ]);

        $response->assertSessionHasErrors(['first_name']);
    }

    public function test_update_returns_404_if_guest_belongs_to_different_group(): void
    {
        $otherGroup = Group::factory()->forChurch($this->church)->create();
        $guest = GroupGuest::create([
            'church_id' => $this->church->id,
            'group_id' => $otherGroup->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->put("/groups/{$this->group->id}/guests/{$guest->id}", [
            'first_name' => 'Updated',
        ]);

        $response->assertStatus(404);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_guest(): void
    {
        $guest = GroupGuest::create([
            'church_id' => $this->church->id,
            'group_id' => $this->group->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->delete("/groups/{$this->group->id}/guests/{$guest->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('group_guests', ['id' => $guest->id]);
    }

    public function test_destroy_returns_404_if_guest_belongs_to_different_group(): void
    {
        $otherGroup = Group::factory()->forChurch($this->church)->create();
        $guest = GroupGuest::create([
            'church_id' => $this->church->id,
            'group_id' => $otherGroup->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->delete("/groups/{$this->group->id}/guests/{$guest->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_add_guest_to_group_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherGroup = Group::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->post("/groups/{$otherGroup->id}/guests", [
            'first_name' => 'Hacker',
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_update_guest_in_group_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherGroup = Group::factory()->forChurch($otherChurch)->create();
        $guest = GroupGuest::create([
            'church_id' => $otherChurch->id,
            'group_id' => $otherGroup->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->put("/groups/{$otherGroup->id}/guests/{$guest->id}", [
            'first_name' => 'Hacked',
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_guest_from_group_in_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherGroup = Group::factory()->forChurch($otherChurch)->create();
        $guest = GroupGuest::create([
            'church_id' => $otherChurch->id,
            'group_id' => $otherGroup->id,
            'first_name' => 'John',
        ]);

        $response = $this->actingAs($this->admin)->delete("/groups/{$otherGroup->id}/guests/{$guest->id}");

        $response->assertStatus(404);
    }
}
