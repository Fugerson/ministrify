<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
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
    // Index (redirects to settings)
    // ==================

    public function test_index_redirects_to_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/tags');

        $response->assertRedirect(route('settings.index'));
    }

    public function test_guest_cannot_access_tags(): void
    {
        $response = $this->get('/tags');

        $response->assertRedirect('/login');
    }

    // ==================
    // Store
    // ==================

    public function test_admin_can_create_tag(): void
    {
        $response = $this->actingAs($this->admin)->post('/tags', [
            'name' => 'Youth',
            'color' => '#ff0000',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'church_id' => $this->church->id,
            'name' => 'Youth',
            'color' => '#ff0000',
        ]);
    }

    public function test_admin_can_create_tag_without_color(): void
    {
        $response = $this->actingAs($this->admin)->post('/tags', [
            'name' => 'Volunteers',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'church_id' => $this->church->id,
            'name' => 'Volunteers',
            'color' => null,
        ]);
    }

    public function test_store_sets_church_id_from_current_church(): void
    {
        $this->actingAs($this->admin)->post('/tags', [
            'name' => 'Test Tag',
        ]);

        $tag = Tag::where('name', 'Test Tag')->first();
        $this->assertNotNull($tag);
        $this->assertEquals($this->church->id, $tag->church_id);
    }

    public function test_store_validates_name_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/tags', [
            'color' => '#00ff00',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validates_name_max_255(): void
    {
        $response = $this->actingAs($this->admin)->post('/tags', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validates_color_max_7(): void
    {
        $response = $this->actingAs($this->admin)->post('/tags', [
            'name' => 'Test',
            'color' => '#ff00ff00',
        ]);

        $response->assertSessionHasErrors(['color']);
    }

    public function test_volunteer_without_settings_edit_cannot_create_tag(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['settings' => ['view']]);

        $response = $this->actingAs($volunteer)->post('/tags', [
            'name' => 'Forbidden Tag',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('tags', ['name' => 'Forbidden Tag']);
    }

    public function test_volunteer_with_settings_edit_can_create_tag(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['settings' => ['view', 'edit']]);

        $response = $this->actingAs($volunteer)->post('/tags', [
            'name' => 'Allowed Tag',
            'color' => '#abcdef',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'church_id' => $this->church->id,
            'name' => 'Allowed Tag',
        ]);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_tag(): void
    {
        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'Old Name',
            'color' => '#111111',
        ]);

        $response = $this->actingAs($this->admin)->put("/tags/{$tag->id}", [
            'name' => 'New Name',
            'color' => '#222222',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
            'color' => '#222222',
        ]);
    }

    public function test_update_validates_name_required(): void
    {
        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'Existing',
        ]);

        $response = $this->actingAs($this->admin)->put("/tags/{$tag->id}", [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_cannot_update_tag_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherTag = Tag::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Tag',
        ]);

        $response = $this->actingAs($this->admin)->put("/tags/{$otherTag->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('tags', [
            'id' => $otherTag->id,
            'name' => 'Other Tag',
        ]);
    }

    public function test_volunteer_without_permission_cannot_update_tag(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions([]);

        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'Protected',
        ]);

        $response = $this->actingAs($volunteer)->put("/tags/{$tag->id}", [
            'name' => 'Changed',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Protected',
        ]);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_tag(): void
    {
        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'To Delete',
        ]);

        $response = $this->actingAs($this->admin)->delete("/tags/{$tag->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_cannot_delete_tag_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherTag = Tag::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Tag',
        ]);

        $response = $this->actingAs($this->admin)->delete("/tags/{$otherTag->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('tags', ['id' => $otherTag->id]);
    }

    public function test_volunteer_without_permission_cannot_delete_tag(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['settings' => ['view']]);

        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'Protected',
        ]);

        $response = $this->actingAs($volunteer)->delete("/tags/{$tag->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_volunteer_with_settings_edit_can_delete_tag(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['settings' => ['view', 'edit']]);

        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'Deletable',
        ]);

        $response = $this->actingAs($volunteer)->delete("/tags/{$tag->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    // ==================
    // JSON responses (AJAX)
    // ==================

    public function test_store_returns_json_when_requested(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/tags', [
                'name' => 'Ajax Tag',
                'color' => '#aabbcc',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tags', ['name' => 'Ajax Tag']);
    }

    public function test_update_returns_json_when_requested(): void
    {
        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'Before',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/tags/{$tag->id}", [
                'name' => 'After',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_destroy_returns_json_when_requested(): void
    {
        $tag = Tag::create([
            'church_id' => $this->church->id,
            'name' => 'To Delete',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/tags/{$tag->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
