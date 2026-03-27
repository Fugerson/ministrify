<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    private function createTicket(array $overrides = [], ?User $user = null): SupportTicket
    {
        $user = $user ?? $this->admin;

        return SupportTicket::create(array_merge([
            'user_id' => $user->id,
            'church_id' => $this->church->id,
            'subject' => 'Test Bug Report',
            'category' => 'bug',
            'priority' => 'normal',
            'status' => 'open',
            'last_reply_at' => now(),
        ], $overrides));
    }

    private function createMessage(SupportTicket $ticket, array $overrides = []): SupportMessage
    {
        return SupportMessage::create(array_merge([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'message' => 'Test message content',
            'is_from_admin' => false,
        ], $overrides));
    }

    // ==================
    // Auth / Guest
    // ==================

    public function test_guest_is_redirected_to_login_from_index(): void
    {
        $response = $this->get('/support');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_create(): void
    {
        $response = $this->get('/support/create');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_store(): void
    {
        $response = $this->post('/support', [
            'subject' => 'Test',
            'category' => 'bug',
            'message' => 'Content',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_show(): void
    {
        $ticket = $this->createTicket();

        $response = $this->get("/support/{$ticket->id}");

        $response->assertRedirect('/login');
    }

    // ==================
    // Index
    // ==================

    public function test_user_can_view_support_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/support');

        $response->assertStatus(200);
    }

    public function test_index_shows_only_own_tickets(): void
    {
        $this->createTicket(['subject' => 'My Ticket']);

        $otherUser = $this->createUserWithRole($this->church, 'volunteer');
        $this->createTicket(['subject' => 'Other Ticket', 'user_id' => $otherUser->id]);

        $response = $this->actingAs($this->admin)->get('/support');

        $response->assertStatus(200);
        $response->assertSee('My Ticket');
        $response->assertDontSee('Other Ticket');
    }

    // ==================
    // Create
    // ==================

    public function test_user_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/support/create');

        $response->assertStatus(200);
    }

    // ==================
    // Store
    // ==================

    public function test_user_can_create_ticket(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'subject' => 'New Bug Report',
            'category' => 'bug',
            'message' => 'Something is broken.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->admin->id,
            'church_id' => $this->church->id,
            'subject' => 'New Bug Report',
            'category' => 'bug',
            'priority' => 'normal',
            'status' => 'open',
        ]);

        $ticket = SupportTicket::where('subject', 'New Bug Report')->first();
        $this->assertNotNull($ticket);
        $this->assertDatabaseHas('support_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'Something is broken.',
            'is_from_admin' => false,
        ]);
    }

    public function test_store_validates_subject_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'category' => 'bug',
            'message' => 'Content here.',
        ]);

        $response->assertSessionHasErrors('subject');
    }

    public function test_store_validates_subject_max_255(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'subject' => str_repeat('A', 256),
            'category' => 'bug',
            'message' => 'Content here.',
        ]);

        $response->assertSessionHasErrors('subject');
    }

    public function test_store_validates_category_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'subject' => 'Bug',
            'message' => 'Content here.',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_store_validates_category_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'subject' => 'Bug',
            'category' => 'invalid_category',
            'message' => 'Content here.',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_store_validates_message_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'subject' => 'Bug',
            'category' => 'bug',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_validates_message_max_10000(): void
    {
        $response = $this->actingAs($this->admin)->post('/support', [
            'subject' => 'Bug',
            'category' => 'bug',
            'message' => str_repeat('A', 10001),
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_accepts_all_valid_categories(): void
    {
        foreach (['bug', 'question', 'feature', 'other'] as $category) {
            $response = $this->actingAs($this->admin)->post('/support', [
                'subject' => "Test {$category}",
                'category' => $category,
                'message' => 'Valid message.',
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('support_tickets', [
                'subject' => "Test {$category}",
                'category' => $category,
            ]);
        }
    }

    // ==================
    // Show
    // ==================

    public function test_user_can_view_own_ticket(): void
    {
        $ticket = $this->createTicket();
        $this->createMessage($ticket);

        $response = $this->actingAs($this->admin)->get("/support/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertSee('Test Bug Report');
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $otherUser = $this->createUserWithRole($this->church, 'volunteer');
        $ticket = $this->createTicket(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->admin)->get("/support/{$ticket->id}");

        $response->assertStatus(404);
    }

    public function test_show_marks_admin_messages_as_read(): void
    {
        $ticket = $this->createTicket();
        $adminMessage = $this->createMessage($ticket, [
            'is_from_admin' => true,
            'is_internal' => false,
            'read_at' => null,
        ]);

        $this->assertNull($adminMessage->fresh()->read_at);

        $this->actingAs($this->admin)->get("/support/{$ticket->id}");

        $this->assertNotNull($adminMessage->fresh()->read_at);
    }

    public function test_show_does_not_mark_internal_messages_as_read(): void
    {
        $ticket = $this->createTicket();
        $internalMessage = $this->createMessage($ticket, [
            'is_from_admin' => true,
            'is_internal' => true,
            'read_at' => null,
        ]);

        $this->actingAs($this->admin)->get("/support/{$ticket->id}");

        $this->assertNull($internalMessage->fresh()->read_at);
    }

    // ==================
    // Reply
    // ==================

    public function test_user_can_reply_to_own_ticket(): void
    {
        $ticket = $this->createTicket();
        $this->createMessage($ticket);

        $response = $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", [
            'message' => 'This is my reply.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->admin->id,
            'message' => 'This is my reply.',
            'is_from_admin' => false,
        ]);
    }

    public function test_user_cannot_reply_to_another_users_ticket(): void
    {
        $otherUser = $this->createUserWithRole($this->church, 'volunteer');
        $ticket = $this->createTicket(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", [
            'message' => 'Should not work.',
        ]);

        $response->assertStatus(404);
    }

    public function test_reply_changes_status_from_waiting_to_open(): void
    {
        $ticket = $this->createTicket(['status' => 'waiting']);
        $this->createMessage($ticket);

        $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", [
            'message' => 'User responds.',
        ]);

        $this->assertEquals('open', $ticket->fresh()->status);
    }

    public function test_reply_does_not_change_status_if_not_waiting(): void
    {
        $ticket = $this->createTicket(['status' => 'in_progress']);
        $this->createMessage($ticket);

        $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", [
            'message' => 'Another reply.',
        ]);

        $this->assertEquals('in_progress', $ticket->fresh()->status);
    }

    public function test_reply_updates_last_reply_at(): void
    {
        $ticket = $this->createTicket(['last_reply_at' => now()->subDay()]);
        $this->createMessage($ticket);
        $beforeReply = $ticket->last_reply_at;

        $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", [
            'message' => 'New reply.',
        ]);

        $this->assertTrue($ticket->fresh()->last_reply_at->greaterThan($beforeReply));
    }

    public function test_reply_validates_message_required(): void
    {
        $ticket = $this->createTicket();

        $response = $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", []);

        $response->assertSessionHasErrors('message');
    }

    public function test_reply_validates_message_max_10000(): void
    {
        $ticket = $this->createTicket();

        $response = $this->actingAs($this->admin)->post("/support/{$ticket->id}/reply", [
            'message' => str_repeat('A', 10001),
        ]);

        $response->assertSessionHasErrors('message');
    }

    // ==================
    // Close
    // ==================

    public function test_user_can_close_own_ticket(): void
    {
        $ticket = $this->createTicket(['status' => 'open']);

        $response = $this->actingAs($this->admin)->post("/support/{$ticket->id}/close");

        $response->assertRedirect();
        $this->assertEquals('closed', $ticket->fresh()->status);
    }

    public function test_user_cannot_close_another_users_ticket(): void
    {
        $otherUser = $this->createUserWithRole($this->church, 'volunteer');
        $ticket = $this->createTicket(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->admin)->post("/support/{$ticket->id}/close");

        $response->assertStatus(404);
    }

    public function test_closing_ticket_sets_status_to_closed(): void
    {
        $ticket = $this->createTicket(['status' => 'in_progress']);

        $this->actingAs($this->admin)->post("/support/{$ticket->id}/close");

        $this->assertEquals('closed', $ticket->fresh()->status);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_ticket_from_another_church_returns_404(): void
    {
        [$otherChurch, $otherAdmin] = $this->createChurchWithAdmin();
        $otherTicket = SupportTicket::create([
            'user_id' => $otherAdmin->id,
            'church_id' => $otherChurch->id,
            'subject' => 'Other Church Ticket',
            'category' => 'question',
            'priority' => 'normal',
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        // Even if somehow user_id matched (it doesn't here), church isolation should hold
        $response = $this->actingAs($this->admin)->get("/support/{$otherTicket->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Volunteer access
    // ==================

    public function test_volunteer_can_create_and_view_own_ticket(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        // Create ticket
        $response = $this->actingAs($volunteer)->post('/support', [
            'subject' => 'Volunteer Bug',
            'category' => 'bug',
            'message' => 'Something broken.',
        ]);

        $response->assertRedirect();

        $ticket = SupportTicket::where('user_id', $volunteer->id)->first();
        $this->assertNotNull($ticket);

        // View own ticket
        $response = $this->actingAs($volunteer)->get("/support/{$ticket->id}");
        $response->assertStatus(200);
    }

    // ==================
    // Model attributes
    // ==================

    public function test_ticket_has_correct_category_label(): void
    {
        $ticket = $this->createTicket(['category' => 'feature']);

        $this->assertEquals('Пропозиція', $ticket->category_label);
    }

    public function test_ticket_has_correct_status_label(): void
    {
        $ticket = $this->createTicket(['status' => 'waiting']);

        $this->assertEquals('Очікує відповіді', $ticket->status_label);
    }

    public function test_ticket_has_correct_priority_label(): void
    {
        $ticket = $this->createTicket(['priority' => 'urgent']);

        $this->assertEquals('Терміновий', $ticket->priority_label);
    }

    public function test_unread_messages_for_user_counts_admin_non_internal_messages(): void
    {
        $ticket = $this->createTicket();

        // Admin message, not internal, not read
        $this->createMessage($ticket, [
            'is_from_admin' => true,
            'is_internal' => false,
            'read_at' => null,
        ]);

        // Admin internal message (should not count)
        $this->createMessage($ticket, [
            'is_from_admin' => true,
            'is_internal' => true,
            'read_at' => null,
        ]);

        // User's own message (should not count)
        $this->createMessage($ticket, [
            'is_from_admin' => false,
            'read_at' => null,
        ]);

        // Already read admin message (should not count)
        $this->createMessage($ticket, [
            'is_from_admin' => true,
            'is_internal' => false,
            'read_at' => now(),
        ]);

        $this->assertEquals(1, $ticket->unreadMessagesForUser());
    }
}
