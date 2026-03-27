<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    private function createAnnouncement(array $overrides = []): Announcement
    {
        return Announcement::create(array_merge([
            'church_id' => $this->church->id,
            'author_id' => $this->admin->id,
            'title' => 'Test Announcement',
            'content' => 'Test content for announcement.',
            'is_published' => true,
            'published_at' => now(),
            'is_pinned' => false,
        ], $overrides));
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_announcements_index(): void
    {
        $this->createAnnouncement();

        $response = $this->actingAs($this->admin)->get('/announcements');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_announcements(): void
    {
        $response = $this->get('/announcements');

        $response->assertRedirect('/login');
    }

    public function test_volunteer_with_view_permission_can_view_announcements(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/announcements');

        $response->assertStatus(200);
    }

    public function test_volunteer_without_permission_is_redirected_from_index(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        // setPermissions([]) doesn't clear existing defaults, so delete them explicitly
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->clearPermissionCache();

        $response = $this->actingAs($volunteer)->get('/announcements');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_index_shows_only_published_announcements(): void
    {
        $this->createAnnouncement(['title' => 'Published One']);
        $this->createAnnouncement(['title' => 'Unpublished', 'is_published' => false]);

        $response = $this->actingAs($this->admin)->get('/announcements');

        $response->assertStatus(200);
        $response->assertSee('Published One');
        $response->assertDontSee('Unpublished');
    }

    public function test_index_excludes_expired_announcements(): void
    {
        $this->createAnnouncement(['title' => 'Active']);
        $this->createAnnouncement(['title' => 'Expired', 'expires_at' => now()->subDay()]);

        $response = $this->actingAs($this->admin)->get('/announcements');

        $response->assertStatus(200);
        $response->assertSee('Active');
        $response->assertDontSee('Expired');
    }

    // ==================
    // Show
    // ==================

    public function test_admin_can_view_single_announcement(): void
    {
        $announcement = $this->createAnnouncement();

        $response = $this->actingAs($this->admin)->get("/announcements/{$announcement->id}");

        $response->assertStatus(200);
        $response->assertSee('Test Announcement');
    }

    public function test_show_marks_announcement_as_read(): void
    {
        $announcement = $this->createAnnouncement();

        $this->actingAs($this->admin)->get("/announcements/{$announcement->id}");

        $this->assertTrue($announcement->isReadBy($this->admin));
    }

    public function test_show_marks_read_only_once(): void
    {
        $announcement = $this->createAnnouncement();

        $this->actingAs($this->admin)->get("/announcements/{$announcement->id}");
        $this->actingAs($this->admin)->get("/announcements/{$announcement->id}");

        $this->assertSame(1, $announcement->readByUsers()->where('user_id', $this->admin->id)->count());
    }

    public function test_guest_cannot_view_announcement(): void
    {
        $announcement = $this->createAnnouncement();

        $response = $this->get("/announcements/{$announcement->id}");

        $response->assertRedirect('/login');
    }

    public function test_volunteer_without_view_permission_gets_403_on_show(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        // setPermissions([]) doesn't clear existing defaults, so delete them explicitly
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->clearPermissionCache();
        $announcement = $this->createAnnouncement();

        $response = $this->actingAs($volunteer)->get("/announcements/{$announcement->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_view_announcement_from_another_church(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();
        $otherAnnouncement = Announcement::create([
            'church_id' => $otherChurch->id,
            'author_id' => $otherAdmin->id,
            'title' => 'Other Church Announcement',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get("/announcements/{$otherAnnouncement->id}");

        $response->assertStatus(404);
    }

    public function test_cannot_edit_announcement_from_another_church(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();
        $otherAnnouncement = Announcement::create([
            'church_id' => $otherChurch->id,
            'author_id' => $otherAdmin->id,
            'title' => 'Other Church',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get("/announcements/{$otherAnnouncement->id}/edit");

        $response->assertStatus(404);
    }

    public function test_cannot_delete_announcement_from_another_church(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();
        $otherAnnouncement = Announcement::create([
            'church_id' => $otherChurch->id,
            'author_id' => $otherAdmin->id,
            'title' => 'Other Church',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete("/announcements/{$otherAnnouncement->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Create / Store
    // ==================

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/announcements/create');

        $response->assertStatus(200);
    }

    public function test_volunteer_without_create_permission_gets_403_on_create_form(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/announcements/create');

        $response->assertStatus(403);
    }

    public function test_admin_can_store_announcement(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)->post('/announcements', [
            'title' => 'New Announcement',
            'content' => 'Announcement body text.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'church_id' => $this->church->id,
            'author_id' => $this->admin->id,
            'title' => 'New Announcement',
            'is_published' => true,
        ]);
    }

    public function test_store_sets_author_and_church(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $this->actingAs($this->admin)->post('/announcements', [
            'title' => 'Authored',
            'content' => 'By admin.',
        ]);

        $announcement = Announcement::where('title', 'Authored')->first();
        $this->assertNotNull($announcement);
        $this->assertEquals($this->church->id, $announcement->church_id);
        $this->assertEquals($this->admin->id, $announcement->author_id);
        $this->assertTrue($announcement->is_published);
        $this->assertNotNull($announcement->published_at);
    }

    public function test_store_with_pin_and_expiry(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $expiresAt = now()->addWeek()->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->admin)->post('/announcements', [
            'title' => 'Pinned',
            'content' => 'Pinned announcement.',
            'is_pinned' => true,
            'expires_at' => $expiresAt,
        ]);

        $response->assertRedirect();
        $announcement = Announcement::where('title', 'Pinned')->first();
        $this->assertTrue($announcement->is_pinned);
        $this->assertNotNull($announcement->expires_at);
    }

    public function test_store_requires_title(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)->post('/announcements', [
            'content' => 'No title provided.',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_requires_content(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)->post('/announcements', [
            'title' => 'No Content',
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_store_rejects_title_over_255_chars(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)->post('/announcements', [
            'title' => str_repeat('A', 256),
            'content' => 'Valid content.',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_rejects_expires_at_in_the_past(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $response = $this->actingAs($this->admin)->post('/announcements', [
            'title' => 'Past Expiry',
            'content' => 'Content.',
            'expires_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('expires_at');
    }

    public function test_volunteer_without_create_permission_cannot_store(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $response = $this->actingAs($volunteer)->post('/announcements', [
            'title' => 'Forbidden',
            'content' => 'Should not be created.',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('announcements', ['title' => 'Forbidden']);
    }

    // ==================
    // Edit / Update
    // ==================

    public function test_admin_can_view_edit_form(): void
    {
        $announcement = $this->createAnnouncement();

        $response = $this->actingAs($this->admin)->get("/announcements/{$announcement->id}/edit");

        $response->assertStatus(200);
    }

    public function test_admin_can_update_announcement(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $announcement = $this->createAnnouncement();

        $response = $this->actingAs($this->admin)->put("/announcements/{$announcement->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated Title',
            'content' => 'Updated content.',
        ]);
    }

    public function test_author_can_edit_own_announcement_without_edit_permission(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view', 'create']]);

        $announcement = $this->createAnnouncement(['author_id' => $volunteer->id]);

        $response = $this->actingAs($volunteer)->put("/announcements/{$announcement->id}", [
            'title' => 'Author Edited',
            'content' => 'Author updated content.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Author Edited',
        ]);
    }

    public function test_non_author_with_edit_permission_can_edit(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view', 'edit']]);

        $announcement = $this->createAnnouncement(); // authored by admin

        $response = $this->actingAs($volunteer)->put("/announcements/{$announcement->id}", [
            'title' => 'Edited by Volunteer',
            'content' => 'Volunteer updated content.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Edited by Volunteer',
        ]);
    }

    public function test_non_author_without_edit_permission_cannot_edit(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $announcement = $this->createAnnouncement(); // authored by admin

        $response = $this->actingAs($volunteer)->get("/announcements/{$announcement->id}/edit");

        $response->assertStatus(403);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_announcement(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $announcement = $this->createAnnouncement();

        $response = $this->actingAs($this->admin)->delete("/announcements/{$announcement->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_author_can_delete_own_announcement_without_delete_permission(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view', 'create', 'delete']]);

        $announcement = $this->createAnnouncement(['author_id' => $volunteer->id]);

        $response = $this->actingAs($volunteer)->delete("/announcements/{$announcement->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_non_author_with_delete_permission_can_delete(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view', 'delete']]);

        $announcement = $this->createAnnouncement(); // authored by admin

        $response = $this->actingAs($volunteer)->delete("/announcements/{$announcement->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_non_author_without_delete_permission_cannot_delete(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $announcement = $this->createAnnouncement(); // authored by admin

        $response = $this->actingAs($volunteer)->delete("/announcements/{$announcement->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Toggle Pin
    // ==================

    public function test_admin_can_toggle_pin(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $announcement = $this->createAnnouncement(['is_pinned' => false]);

        $response = $this->actingAs($this->admin)->post("/announcements/{$announcement->id}/pin");

        $response->assertRedirect();
        $this->assertTrue($announcement->fresh()->is_pinned);
    }

    public function test_toggle_pin_flips_value(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $announcement = $this->createAnnouncement(['is_pinned' => true]);

        $this->actingAs($this->admin)->post("/announcements/{$announcement->id}/pin");

        $this->assertFalse($announcement->fresh()->is_pinned);
    }

    public function test_author_can_toggle_pin_without_edit_permission(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view', 'create', 'edit']]);

        $announcement = $this->createAnnouncement(['author_id' => $volunteer->id, 'is_pinned' => false]);

        $response = $this->actingAs($volunteer)->post("/announcements/{$announcement->id}/pin");

        $response->assertRedirect();
        $this->assertTrue($announcement->fresh()->is_pinned);
    }

    public function test_non_author_without_edit_permission_cannot_toggle_pin(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $announcement = $this->createAnnouncement(['is_pinned' => false]);

        $response = $this->actingAs($volunteer)->post("/announcements/{$announcement->id}/pin");

        $response->assertStatus(403);
    }

    // ==================
    // Mark All Read
    // ==================

    public function test_mark_all_read(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $a1 = $this->createAnnouncement(['title' => 'Unread 1']);
        $a2 = $this->createAnnouncement(['title' => 'Unread 2']);

        $this->assertFalse($a1->isReadBy($this->admin));
        $this->assertFalse($a2->isReadBy($this->admin));

        $response = $this->actingAs($this->admin)->post('/announcements/mark-all-read');

        $response->assertRedirect();
        $this->assertTrue($a1->fresh()->isReadBy($this->admin));
        $this->assertTrue($a2->fresh()->isReadBy($this->admin));
    }

    public function test_mark_all_read_does_not_affect_other_users(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['announcements' => ['view']]);

        $announcement = $this->createAnnouncement();

        $this->actingAs($this->admin)->post('/announcements/mark-all-read');

        $this->assertTrue($announcement->isReadBy($this->admin));
        $this->assertFalse($announcement->isReadBy($volunteer));
    }

    public function test_unread_count_decreases_after_viewing(): void
    {
        $a1 = $this->createAnnouncement(['title' => 'First']);
        $a2 = $this->createAnnouncement(['title' => 'Second']);

        $this->assertEquals(2, Announcement::unreadCount($this->church->id, $this->admin->id));

        $this->actingAs($this->admin)->get("/announcements/{$a1->id}");

        $this->assertEquals(1, Announcement::unreadCount($this->church->id, $this->admin->id));
    }

    // ==================
    // Multi-tenancy for index
    // ==================

    public function test_index_shows_only_own_church_announcements(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $this->createAnnouncement(['title' => 'My Church']);
        Announcement::create([
            'church_id' => $otherChurch->id,
            'author_id' => $otherAdmin->id,
            'title' => 'Other Church',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/announcements');

        $response->assertStatus(200);
        $response->assertSee('My Church');
        $response->assertDontSee('Other Church');
    }
}
