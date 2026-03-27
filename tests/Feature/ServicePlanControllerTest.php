<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\ServicePlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServicePlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Ministry $ministry;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
        $this->event = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
    }

    // ==================
    // Store
    // ==================

    public function test_guest_cannot_add_plan_item(): void
    {
        $response = $this->postJson("/events/{$this->event->id}/plan", [
            'title' => 'Worship',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_add_plan_item(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Worship',
                'type' => 'worship',
                'start_time' => '10:00',
                'end_time' => '10:30',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('service_plan_items', [
            'event_id' => $this->event->id,
            'title' => 'Worship',
            'type' => 'worship',
        ]);
    }

    public function test_plan_item_gets_auto_incremented_sort_order(): void
    {
        // Create first item
        ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'First',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Second',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('service_plan_items', [
            'event_id' => $this->event->id,
            'title' => 'Second',
            'sort_order' => 2,
        ]);
    }

    public function test_store_validates_title_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'type' => 'worship',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_title_max_length(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => str_repeat('A', 256),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_type_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Test',
                'type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_store_with_responsible_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Sermon',
                'type' => 'sermon',
                'responsible_id' => $person->id,
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('service_plan_items', [
            'event_id' => $this->event->id,
            'title' => 'Sermon',
            'responsible_id' => $person->id,
        ]);
    }

    public function test_store_validates_responsible_belongs_to_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Sermon',
                'responsible_id' => $otherPerson->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['responsible_id']);
    }

    // ==================
    // Update
    // ==================

    public function test_admin_can_update_plan_item(): void
    {
        $item = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Worship',
            'type' => 'worship',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/events/{$this->event->id}/plan/{$item->id}", [
                'title' => 'Updated Worship',
                'notes' => 'Use acoustic guitar',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('service_plan_items', [
            'id' => $item->id,
            'title' => 'Updated Worship',
            'notes' => 'Use acoustic guitar',
        ]);
    }

    public function test_update_supports_partial_updates(): void
    {
        $item = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Worship',
            'type' => 'worship',
            'sort_order' => 1,
            'notes' => 'Original notes',
        ]);

        // Only update notes, title should remain
        $response = $this->actingAs($this->admin)
            ->putJson("/events/{$this->event->id}/plan/{$item->id}", [
                'notes' => 'Updated notes',
            ]);

        $response->assertJson(['success' => true]);
        $item->refresh();
        $this->assertEquals('Worship', $item->title);
        $this->assertEquals('Updated notes', $item->notes);
    }

    public function test_update_item_must_belong_to_event(): void
    {
        $otherEvent = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $item = ServicePlanItem::create([
            'event_id' => $otherEvent->id,
            'title' => 'Other Item',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/events/{$this->event->id}/plan/{$item->id}", [
                'title' => 'Hacked',
            ]);

        $response->assertStatus(403);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_plan_item(): void
    {
        $item = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Worship',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/events/{$this->event->id}/plan/{$item->id}");

        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('service_plan_items', ['id' => $item->id]);
    }

    public function test_destroy_item_must_belong_to_event(): void
    {
        $otherEvent = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $item = ServicePlanItem::create([
            'event_id' => $otherEvent->id,
            'title' => 'Other Item',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/events/{$this->event->id}/plan/{$item->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_add_plan_item_to_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$otherEvent->id}/plan", [
                'title' => 'Hacked',
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_update_plan_item_of_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $item = ServicePlanItem::create([
            'event_id' => $otherEvent->id,
            'title' => 'Other Church Item',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/events/{$otherEvent->id}/plan/{$item->id}", [
                'title' => 'Hacked',
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_plan_item_of_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $item = ServicePlanItem::create([
            'event_id' => $otherEvent->id,
            'title' => 'Other Church Item',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/events/{$otherEvent->id}/plan/{$item->id}");

        $response->assertStatus(404);
    }

    // ==================
    // Reorder
    // ==================

    public function test_reorder_updates_sort_order(): void
    {
        $item1 = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'First',
            'sort_order' => 1,
        ]);
        $item2 = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Second',
            'sort_order' => 2,
        ]);
        $item3 = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Third',
            'sort_order' => 3,
        ]);

        // Reverse order
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/reorder", [
                'items' => [
                    ['id' => $item1->id, 'sort_order' => 3],
                    ['id' => $item2->id, 'sort_order' => 2],
                    ['id' => $item3->id, 'sort_order' => 1],
                ],
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('service_plan_items', ['id' => $item1->id, 'sort_order' => 3]);
        $this->assertDatabaseHas('service_plan_items', ['id' => $item2->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('service_plan_items', ['id' => $item3->id, 'sort_order' => 1]);
    }

    public function test_reorder_validates_items_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/reorder", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_reorder_ignores_items_from_other_events(): void
    {
        $item = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'My Item',
            'sort_order' => 1,
        ]);

        $otherEvent = Event::factory()->forMinistry($this->ministry)->upcoming()->create();
        $otherItem = ServicePlanItem::create([
            'event_id' => $otherEvent->id,
            'title' => 'Other Item',
            'sort_order' => 1,
        ]);

        // Try to reorder item from other event - it won't match event_id WHERE clause
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/reorder", [
                'items' => [
                    ['id' => $item->id, 'sort_order' => 2],
                    ['id' => $otherItem->id, 'sort_order' => 1],
                ],
            ]);

        $response->assertJson(['success' => true]);
        // Other item should NOT have changed
        $this->assertDatabaseHas('service_plan_items', ['id' => $otherItem->id, 'sort_order' => 1]);
    }

    // ==================
    // Print
    // ==================

    public function test_admin_can_view_print_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/events/{$this->event->id}/plan/print");

        $response->assertStatus(200);
    }

    public function test_cannot_print_plan_of_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)
            ->get("/events/{$otherEvent->id}/plan/print");

        $response->assertStatus(404);
    }

    // ==================
    // Permission checks
    // ==================

    public function test_volunteer_without_events_edit_cannot_manage_plan(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['events' => ['view']]);

        $response = $this->actingAs($volunteer)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Worship',
            ]);

        $response->assertStatus(403);
    }

    public function test_ministry_leader_can_manage_plan(): void
    {
        $leader = $this->createUserWithRole($this->church, 'leader');
        $person = Person::factory()->forChurch($this->church)->create(['user_id' => $leader->id]);
        $this->ministry->update(['leader_id' => $person->id]);

        $response = $this->actingAs($leader)
            ->postJson("/events/{$this->event->id}/plan", [
                'title' => 'Worship',
                'type' => 'worship',
            ]);

        $response->assertJson(['success' => true]);
    }

    // ==================
    // Update Status
    // ==================

    public function test_admin_can_update_item_status(): void
    {
        $item = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Worship',
            'sort_order' => 1,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/{$item->id}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertJson(['success' => true, 'status' => 'confirmed']);
        $this->assertDatabaseHas('service_plan_items', [
            'id' => $item->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_update_status_validates_allowed_values(): void
    {
        $item = ServicePlanItem::create([
            'event_id' => $this->event->id,
            'title' => 'Worship',
            'sort_order' => 1,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/{$item->id}/status", [
                'status' => 'invalid',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    // ==================
    // Quick Add
    // ==================

    public function test_admin_can_quick_add_item(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/quick-add", [
                'type' => 'worship',
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('service_plan_items', [
            'event_id' => $this->event->id,
            'type' => 'worship',
        ]);
    }

    public function test_quick_add_validates_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/events/{$this->event->id}/plan/quick-add", [
                'type' => 'nonexistent',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }
}
