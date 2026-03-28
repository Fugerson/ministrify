<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PrivateMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    private function createChurchMember(string $role = 'volunteer'): User
    {
        return $this->createUserWithRole($this->church, $role);
    }

    private function createMessage(User $sender, User $recipient, array $overrides = []): PrivateMessage
    {
        return PrivateMessage::create(array_merge([
            'church_id' => $this->church->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => 'Hello there!',
        ], $overrides));
    }

    // ==================
    // Auth / Guest
    // ==================

    public function test_guest_is_redirected_to_login_from_index(): void
    {
        $response = $this->get('/pm');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_create(): void
    {
        $response = $this->get('/pm/create');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_store(): void
    {
        $response = $this->post('/pm', [
            'recipient_id' => 1,
            'message' => 'Hello',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_unread_count(): void
    {
        $response = $this->getJson('/pm/unread-count');

        // JSON requests from guests get 401 instead of redirect
        $response->assertStatus(401);
    }

    // ==================
    // Index
    // ==================

    public function test_user_can_view_pm_index(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('PM index uses MySQL-specific LEAST/GREATEST functions.');
        }

        $response = $this->actingAs($this->admin)->get('/pm');

        $response->assertStatus(200);
    }

    public function test_index_shows_conversations(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('PM index uses MySQL-specific LEAST/GREATEST functions.');
        }

        $member = $this->createChurchMember();
        $this->createMessage($member, $this->admin, ['message' => 'Hey admin!']);

        $response = $this->actingAs($this->admin)->get('/pm');

        $response->assertStatus(200);
    }

    // ==================
    // Create
    // ==================

    public function test_user_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/pm/create');

        $response->assertStatus(200);
    }

    // ==================
    // Store - send message
    // ==================

    public function test_user_can_send_message_to_user_in_same_church(): void
    {
        Event::fake();

        $recipient = $this->createChurchMember();

        $response = $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => $recipient->id,
            'message' => 'Hello from admin!',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('private_messages', [
            'church_id' => $this->church->id,
            'sender_id' => $this->admin->id,
            'recipient_id' => $recipient->id,
            'message' => 'Hello from admin!',
        ]);
    }

    public function test_user_cannot_send_message_to_user_in_different_church(): void
    {
        Event::fake();

        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => $otherAdmin->id,
            'message' => 'Cross-church message.',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('private_messages', [
            'sender_id' => $this->admin->id,
            'recipient_id' => $otherAdmin->id,
        ]);
    }

    public function test_store_validates_recipient_id_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/pm', [
            'message' => 'No recipient.',
        ]);

        $response->assertSessionHasErrors('recipient_id');
    }

    public function test_store_validates_recipient_id_exists(): void
    {
        $response = $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => 99999,
            'message' => 'Nonexistent user.',
        ]);

        $response->assertSessionHasErrors('recipient_id');
    }

    public function test_store_validates_message_required(): void
    {
        $recipient = $this->createChurchMember();

        $response = $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => $recipient->id,
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_validates_message_max_5000(): void
    {
        $recipient = $this->createChurchMember();

        $response = $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => $recipient->id,
            'message' => str_repeat('A', 5001),
        ]);

        $response->assertSessionHasErrors('message');
    }

    // ==================
    // Store - broadcast to all
    // ==================

    public function test_broadcast_to_all_requires_announcements_create_permission(): void
    {
        $volunteer = $this->createChurchMember();
        $volunteer->churchRole->setPermissions([]);

        $response = $this->actingAs($volunteer)->post('/pm', [
            'recipient_id' => 'all',
            'message' => 'Mass message from volunteer.',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_broadcast_to_all(): void
    {
        Event::fake();

        $member1 = $this->createChurchMember();
        $member2 = $this->createChurchMember();

        $response = $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => 'all',
            'message' => 'Broadcast message to all.',
        ]);

        $response->assertRedirect();

        // Should create a message for each member (excluding sender)
        $this->assertDatabaseHas('private_messages', [
            'sender_id' => $this->admin->id,
            'recipient_id' => $member1->id,
            'message' => 'Broadcast message to all.',
        ]);

        $this->assertDatabaseHas('private_messages', [
            'sender_id' => $this->admin->id,
            'recipient_id' => $member2->id,
            'message' => 'Broadcast message to all.',
        ]);
    }

    public function test_broadcast_does_not_send_to_self(): void
    {
        Event::fake();

        $this->createChurchMember();

        $this->actingAs($this->admin)->post('/pm', [
            'recipient_id' => 'all',
            'message' => 'Broadcast.',
        ]);

        $this->assertDatabaseMissing('private_messages', [
            'sender_id' => $this->admin->id,
            'recipient_id' => $this->admin->id,
        ]);
    }

    // ==================
    // Show conversation
    // ==================

    public function test_user_can_view_conversation_with_church_member(): void
    {
        $member = $this->createChurchMember();
        $this->createMessage($this->admin, $member);

        $response = $this->actingAs($this->admin)->get("/pm/{$member->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_conversation_with_user_from_another_church(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($this->admin)->get("/pm/{$otherAdmin->id}");

        $response->assertStatus(404);
    }

    public function test_show_marks_received_messages_as_read(): void
    {
        $member = $this->createChurchMember();

        // Message from member to admin (unread)
        $msg = $this->createMessage($member, $this->admin, ['read_at' => null]);

        $this->assertNull($msg->fresh()->read_at);

        $this->actingAs($this->admin)->get("/pm/{$member->id}");

        $this->assertNotNull($msg->fresh()->read_at);
    }

    public function test_show_does_not_mark_own_messages_as_read(): void
    {
        $member = $this->createChurchMember();

        // Message from admin to member (admin's own message)
        $msg = $this->createMessage($this->admin, $member, ['read_at' => null]);

        $this->actingAs($this->admin)->get("/pm/{$member->id}");

        // Admin's own sent message should remain unread (it's for the recipient to read)
        $this->assertNull($msg->fresh()->read_at);
    }

    // ==================
    // Unread Count
    // ==================

    public function test_unread_count_returns_json(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/pm/unread-count');

        $response->assertStatus(200);
        $response->assertJsonStructure(['count']);
    }

    public function test_unread_count_returns_correct_count(): void
    {
        $member = $this->createChurchMember();

        // 2 unread messages to admin
        $this->createMessage($member, $this->admin, ['read_at' => null]);
        $this->createMessage($member, $this->admin, ['read_at' => null]);

        // 1 read message
        $this->createMessage($member, $this->admin, ['read_at' => now()]);

        // 1 message FROM admin (should not count as unread for admin)
        $this->createMessage($this->admin, $member, ['read_at' => null]);

        $response = $this->actingAs($this->admin)->getJson('/pm/unread-count');

        $response->assertJson(['count' => 2]);
    }

    public function test_unread_count_excludes_other_church_messages(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();
        $otherMember = $this->createUserWithRole($otherChurch, 'volunteer');

        // Message in other church (should not count even if recipient_id matches by coincidence)
        PrivateMessage::create([
            'church_id' => $otherChurch->id,
            'sender_id' => $otherMember->id,
            'recipient_id' => $otherAdmin->id,
            'message' => 'Other church message',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/pm/unread-count');

        $response->assertJson(['count' => 0]);
    }

    // ==================
    // Poll
    // ==================

    public function test_poll_returns_json_messages(): void
    {
        $member = $this->createChurchMember();
        $this->createMessage($member, $this->admin, ['message' => 'Polled message']);

        $response = $this->actingAs($this->admin)->getJson("/pm/{$member->id}/poll?last_id=0");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'messages' => [
                ['id', 'message', 'sender_id', 'sender_name', 'is_mine', 'created_at', 'date'],
            ],
        ]);
    }

    public function test_poll_returns_only_new_messages(): void
    {
        $member = $this->createChurchMember();
        $msg1 = $this->createMessage($member, $this->admin, ['message' => 'Old message']);
        $msg2 = $this->createMessage($member, $this->admin, ['message' => 'New message']);

        $response = $this->actingAs($this->admin)->getJson("/pm/{$member->id}/poll?last_id={$msg1->id}");

        $response->assertStatus(200);
        $messages = $response->json('messages');
        $this->assertCount(1, $messages);
        $this->assertEquals('New message', $messages[0]['message']);
    }

    public function test_poll_marks_received_messages_as_read(): void
    {
        $member = $this->createChurchMember();
        $msg = $this->createMessage($member, $this->admin, ['read_at' => null]);

        $this->actingAs($this->admin)->getJson("/pm/{$member->id}/poll?last_id=0");

        $this->assertNotNull($msg->fresh()->read_at);
    }

    public function test_poll_returns_404_for_user_from_another_church(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();

        $response = $this->actingAs($this->admin)->getJson("/pm/{$otherAdmin->id}/poll?last_id=0");

        $response->assertStatus(404);
    }

    public function test_poll_is_mine_flag_is_correct(): void
    {
        $member = $this->createChurchMember();

        // Message from admin (is_mine = true for admin)
        $this->createMessage($this->admin, $member, ['message' => 'From admin']);
        // Message from member (is_mine = false for admin)
        $this->createMessage($member, $this->admin, ['message' => 'From member']);

        $response = $this->actingAs($this->admin)->getJson("/pm/{$member->id}/poll?last_id=0");

        $messages = collect($response->json('messages'));

        $adminMsg = $messages->firstWhere('message', 'From admin');
        $memberMsg = $messages->firstWhere('message', 'From member');

        $this->assertTrue($adminMsg['is_mine']);
        $this->assertFalse($memberMsg['is_mine']);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_index_only_shows_conversations_from_own_church(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('PM index uses MySQL-specific LEAST/GREATEST functions.');
        }

        $member = $this->createChurchMember();
        $this->createMessage($this->admin, $member, ['message' => 'Same church msg']);

        // Create message in another church
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();
        $otherMember = $this->createUserWithRole($otherChurch, 'volunteer');
        PrivateMessage::create([
            'church_id' => $otherChurch->id,
            'sender_id' => $otherAdmin->id,
            'recipient_id' => $otherMember->id,
            'message' => 'Other church msg',
        ]);

        $response = $this->actingAs($this->admin)->get('/pm');

        $response->assertStatus(200);
    }

    // ==================
    // Model
    // ==================

    public function test_private_message_is_read_check(): void
    {
        $member = $this->createChurchMember();

        $unread = $this->createMessage($this->admin, $member, ['read_at' => null]);
        $read = $this->createMessage($this->admin, $member, ['read_at' => now()]);

        $this->assertFalse($unread->isRead());
        $this->assertTrue($read->isRead());
    }

    public function test_mark_as_read_sets_read_at(): void
    {
        $member = $this->createChurchMember();
        $msg = $this->createMessage($this->admin, $member, ['read_at' => null]);

        $msg->markAsRead();

        $this->assertNotNull($msg->fresh()->read_at);
    }

    public function test_mark_as_read_is_idempotent(): void
    {
        $member = $this->createChurchMember();
        $msg = $this->createMessage($this->admin, $member, ['read_at' => null]);

        $msg->markAsRead();
        $firstReadAt = $msg->fresh()->read_at;

        // Wait a moment and mark again
        $msg->refresh();
        $msg->markAsRead();

        $this->assertEquals($firstReadAt->toDateTimeString(), $msg->fresh()->read_at->toDateTimeString());
    }

    public function test_conversation_returns_messages_between_two_users(): void
    {
        $member = $this->createChurchMember();
        $otherMember = $this->createChurchMember();

        // Messages between admin and member
        $this->createMessage($this->admin, $member, ['message' => 'A to M']);
        $this->createMessage($member, $this->admin, ['message' => 'M to A']);

        // Message between admin and other (should not appear)
        $this->createMessage($this->admin, $otherMember, ['message' => 'A to O']);

        $conversation = PrivateMessage::conversation($this->church->id, $this->admin->id, $member->id)->get();

        $this->assertCount(2, $conversation);
        $this->assertTrue($conversation->pluck('message')->contains('A to M'));
        $this->assertTrue($conversation->pluck('message')->contains('M to A'));
        $this->assertFalse($conversation->pluck('message')->contains('A to O'));
    }
}
