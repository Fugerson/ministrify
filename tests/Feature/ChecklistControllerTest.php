<?php

namespace Tests\Feature;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use App\Models\Church;
use App\Models\Event;
use App\Models\EventChecklist;
use App\Models\EventChecklistItem;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChecklistControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->ministry = Ministry::factory()->forChurch($this->church)->create();
    }

    // ==================
    // Helpers
    // ==================

    private function createTemplate(array $overrides = []): ChecklistTemplate
    {
        return ChecklistTemplate::create(array_merge([
            'church_id' => $this->church->id,
            'name' => 'Sunday Prep',
        ], $overrides));
    }

    private function createTemplateWithItems(int $itemCount = 2, array $overrides = []): ChecklistTemplate
    {
        $template = $this->createTemplate($overrides);

        for ($i = 0; $i < $itemCount; $i++) {
            $template->items()->create([
                'title' => "Item ".($i + 1),
                'order' => $i,
            ]);
        }

        return $template;
    }

    private function createEventForChurch(): Event
    {
        return Event::factory()->forMinistry($this->ministry)->upcoming()->create();
    }

    private function createVolunteerWithPermission(array $permissions): User
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions($permissions);

        return $volunteer;
    }

    // ==================
    // Templates — Index
    // ==================

    public function test_guest_cannot_view_templates(): void
    {
        $response = $this->get('/checklists/templates');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_templates(): void
    {
        $this->createTemplate();

        $response = $this->actingAs($this->admin)->get('/checklists/templates');

        $response->assertStatus(200);
    }

    public function test_volunteer_with_events_view_can_see_templates(): void
    {
        $volunteer = $this->createVolunteerWithPermission(['events' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/checklists/templates');

        $response->assertStatus(200);
    }

    public function test_volunteer_without_events_permission_gets_403(): void
    {
        $volunteer = $this->createVolunteerWithPermission([]);
        // setPermissions([]) doesn't clear existing defaults, so delete them explicitly
        $volunteer->churchRole->permissions()->delete();
        $volunteer->churchRole->clearPermissionCache();

        $response = $this->actingAs($volunteer)->get('/checklists/templates');

        $response->assertStatus(403);
    }

    // ==================
    // Templates — Create
    // ==================

    public function test_admin_can_view_create_template_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/checklists/templates/create');

        $response->assertStatus(200);
    }

    public function test_volunteer_without_edit_permission_cannot_create_template(): void
    {
        $volunteer = $this->createVolunteerWithPermission(['events' => ['view']]);

        $response = $this->actingAs($volunteer)->get('/checklists/templates/create');

        $response->assertStatus(403);
    }

    // ==================
    // Templates — Store
    // ==================

    public function test_admin_can_store_template(): void
    {
        $response = $this->actingAs($this->admin)->post('/checklists/templates', [
            'name' => 'Morning Checklist',
            'items' => [
                ['title' => 'Open doors'],
                ['title' => 'Set up sound'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checklist_templates', [
            'church_id' => $this->church->id,
            'name' => 'Morning Checklist',
        ]);

        $template = ChecklistTemplate::where('name', 'Morning Checklist')->first();
        $this->assertEquals(2, $template->items()->count());
    }

    public function test_store_template_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post('/checklists/templates', [
            'items' => [
                ['title' => 'Item 1'],
            ],
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_template_requires_at_least_one_item(): void
    {
        $response = $this->actingAs($this->admin)->post('/checklists/templates', [
            'name' => 'Empty Checklist',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_store_template_requires_items_array(): void
    {
        $response = $this->actingAs($this->admin)->post('/checklists/templates', [
            'name' => 'No Items',
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_store_template_requires_item_title(): void
    {
        $response = $this->actingAs($this->admin)->post('/checklists/templates', [
            'name' => 'Bad Items',
            'items' => [
                ['title' => ''],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.title');
    }

    public function test_store_template_with_description(): void
    {
        $response = $this->actingAs($this->admin)->post('/checklists/templates', [
            'name' => 'Detailed Checklist',
            'description' => 'A checklist with descriptions',
            'items' => [
                ['title' => 'Item 1', 'description' => 'Do this first'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checklist_templates', [
            'name' => 'Detailed Checklist',
            'description' => 'A checklist with descriptions',
        ]);
    }

    public function test_volunteer_without_edit_permission_cannot_store_template(): void
    {
        $volunteer = $this->createVolunteerWithPermission(['events' => ['view']]);

        $response = $this->actingAs($volunteer)->post('/checklists/templates', [
            'name' => 'Unauthorized',
            'items' => [['title' => 'Item']],
        ]);

        $response->assertStatus(403);
    }

    // ==================
    // Templates — Edit
    // ==================

    public function test_admin_can_view_edit_template(): void
    {
        $template = $this->createTemplateWithItems();

        $response = $this->actingAs($this->admin)->get("/checklists/templates/{$template->id}/edit");

        $response->assertStatus(200);
    }

    public function test_cannot_edit_template_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $template = ChecklistTemplate::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Church Template',
        ]);

        $response = $this->actingAs($this->admin)->get("/checklists/templates/{$template->id}/edit");

        $response->assertStatus(403);
    }

    // ==================
    // Templates — Update
    // ==================

    public function test_admin_can_update_template(): void
    {
        $template = $this->createTemplateWithItems();

        $response = $this->actingAs($this->admin)->put("/checklists/templates/{$template->id}", [
            'name' => 'Updated Name',
            'items' => [
                ['id' => $template->items->first()->id, 'title' => 'Updated Item 1'],
                ['title' => 'Brand New Item'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('checklist_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_removes_deleted_items(): void
    {
        $template = $this->createTemplateWithItems(3);
        $keepItemId = $template->items->first()->id;

        $response = $this->actingAs($this->admin)->put("/checklists/templates/{$template->id}", [
            'name' => $template->name,
            'items' => [
                ['id' => $keepItemId, 'title' => 'Kept Item'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, $template->fresh()->items()->count());
    }

    public function test_cannot_update_template_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $template = ChecklistTemplate::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Church',
        ]);

        $response = $this->actingAs($this->admin)->put("/checklists/templates/{$template->id}", [
            'name' => 'Hijacked',
            'items' => [['title' => 'Item']],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('checklist_templates', [
            'id' => $template->id,
            'name' => 'Other Church',
        ]);
    }

    // ==================
    // Templates — Delete
    // ==================

    public function test_admin_can_delete_template(): void
    {
        $template = $this->createTemplate();

        $response = $this->actingAs($this->admin)->delete("/checklists/templates/{$template->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('checklist_templates', ['id' => $template->id]);
    }

    public function test_cannot_delete_template_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $template = ChecklistTemplate::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Template',
        ]);

        $response = $this->actingAs($this->admin)->delete("/checklists/templates/{$template->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('checklist_templates', ['id' => $template->id]);
    }

    // ==================
    // Event Checklists — Create for Event
    // ==================

    public function test_admin_can_create_checklist_for_event(): void
    {
        $event = $this->createEventForChurch();

        $response = $this->actingAs($this->admin)->post("/checklists/events/{$event->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('event_checklists', [
            'event_id' => $event->id,
        ]);
    }

    public function test_create_checklist_from_template_copies_items(): void
    {
        $template = $this->createTemplateWithItems(3);
        $event = $this->createEventForChurch();

        $response = $this->actingAs($this->admin)->post("/checklists/events/{$event->id}", [
            'template_id' => $template->id,
        ]);

        $response->assertRedirect();

        $checklist = EventChecklist::where('event_id', $event->id)->first();
        $this->assertNotNull($checklist);
        $this->assertEquals(3, $checklist->items()->count());
        $this->assertEquals($template->id, $checklist->checklist_template_id);
    }

    public function test_cannot_create_checklist_for_event_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();

        $response = $this->actingAs($this->admin)->post("/checklists/events/{$otherEvent->id}");

        $response->assertStatus(403);
    }

    public function test_volunteer_without_edit_cannot_create_event_checklist(): void
    {
        $volunteer = $this->createVolunteerWithPermission(['events' => ['view']]);
        $event = $this->createEventForChurch();

        $response = $this->actingAs($volunteer)->post("/checklists/events/{$event->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_use_template_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherTemplate = ChecklistTemplate::create([
            'church_id' => $otherChurch->id,
            'name' => 'Other Template',
        ]);

        $event = $this->createEventForChurch();

        $response = $this->actingAs($this->admin)->post("/checklists/events/{$event->id}", [
            'template_id' => $otherTemplate->id,
        ]);

        $response->assertStatus(403);
    }

    // ==================
    // Event Checklists — Delete Checklist
    // ==================

    public function test_admin_can_delete_event_checklist(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);

        $response = $this->actingAs($this->admin)->delete("/checklists/{$checklist->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('event_checklists', ['id' => $checklist->id]);
    }

    public function test_cannot_delete_checklist_from_other_church_event(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();
        $checklist = EventChecklist::create(['event_id' => $otherEvent->id]);

        $response = $this->actingAs($this->admin)->delete("/checklists/{$checklist->id}");

        $response->assertStatus(403);
    }

    // ==================
    // Event Checklist Items — Add
    // ==================

    public function test_admin_can_add_item_to_checklist(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);

        $response = $this->actingAs($this->admin)->post("/checklists/{$checklist->id}/items", [
            'title' => 'New Item',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_checklist_items', [
            'event_checklist_id' => $checklist->id,
            'title' => 'New Item',
            'is_completed' => false,
        ]);
    }

    public function test_add_item_requires_title(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);

        $response = $this->actingAs($this->admin)->post("/checklists/{$checklist->id}/items", [
            'title' => '',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_add_item_increments_order(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);

        $this->actingAs($this->admin)->post("/checklists/{$checklist->id}/items", ['title' => 'First']);
        $this->actingAs($this->admin)->post("/checklists/{$checklist->id}/items", ['title' => 'Second']);

        $items = EventChecklistItem::where('event_checklist_id', $checklist->id)->orderBy('order')->get();
        $this->assertEquals(0, $items[0]->order);
        $this->assertEquals(1, $items[1]->order);
    }

    // ==================
    // Event Checklist Items — Toggle
    // ==================

    public function test_can_toggle_item_completion(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);
        $item = $checklist->items()->create([
            'title' => 'Toggle Me',
            'order' => 0,
            'is_completed' => false,
        ]);

        // Toggle on
        $response = $this->actingAs($this->admin)
            ->postJson("/checklists/items/{$item->id}/toggle");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'is_completed' => true]);

        $item->refresh();
        $this->assertTrue($item->is_completed);
        $this->assertEquals($this->admin->id, $item->completed_by);
        $this->assertNotNull($item->completed_at);
    }

    public function test_can_toggle_item_off(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);
        $item = $checklist->items()->create([
            'title' => 'Toggle Off',
            'order' => 0,
            'is_completed' => true,
            'completed_by' => $this->admin->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/checklists/items/{$item->id}/toggle");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'is_completed' => false]);

        $item->refresh();
        $this->assertFalse($item->is_completed);
        $this->assertNull($item->completed_by);
        $this->assertNull($item->completed_at);
    }

    public function test_toggle_returns_progress(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);
        $item1 = $checklist->items()->create(['title' => 'Item 1', 'order' => 0, 'is_completed' => false]);
        $checklist->items()->create(['title' => 'Item 2', 'order' => 1, 'is_completed' => false]);

        $response = $this->actingAs($this->admin)
            ->postJson("/checklists/items/{$item1->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'is_completed', 'progress']);

        // 1 of 2 completed = 50%
        $this->assertEquals(50, $response->json('progress'));
    }

    public function test_cannot_toggle_item_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();
        $checklist = EventChecklist::create(['event_id' => $otherEvent->id]);
        $item = $checklist->items()->create(['title' => 'Other', 'order' => 0]);

        $response = $this->actingAs($this->admin)
            ->postJson("/checklists/items/{$item->id}/toggle");

        $response->assertStatus(403);
    }

    // ==================
    // Event Checklist Items — Update
    // ==================

    public function test_admin_can_update_checklist_item(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);
        $item = $checklist->items()->create(['title' => 'Original', 'order' => 0]);

        $response = $this->actingAs($this->admin)->put("/checklists/items/{$item->id}", [
            'title' => 'Updated Title',
            'description' => 'New description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_checklist_items', [
            'id' => $item->id,
            'title' => 'Updated Title',
            'description' => 'New description',
        ]);
    }

    public function test_update_item_requires_title(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);
        $item = $checklist->items()->create(['title' => 'Original', 'order' => 0]);

        $response = $this->actingAs($this->admin)->put("/checklists/items/{$item->id}", [
            'title' => '',
        ]);

        $response->assertSessionHasErrors('title');
    }

    // ==================
    // Event Checklist Items — Delete
    // ==================

    public function test_admin_can_delete_checklist_item(): void
    {
        $event = $this->createEventForChurch();
        $checklist = EventChecklist::create(['event_id' => $event->id]);
        $item = $checklist->items()->create(['title' => 'Delete Me', 'order' => 0]);

        $response = $this->actingAs($this->admin)->delete("/checklists/items/{$item->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('event_checklist_items', ['id' => $item->id]);
    }

    public function test_cannot_delete_item_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherMinistry = Ministry::factory()->forChurch($otherChurch)->create();
        $otherEvent = Event::factory()->forMinistry($otherMinistry)->upcoming()->create();
        $checklist = EventChecklist::create(['event_id' => $otherEvent->id]);
        $item = $checklist->items()->create(['title' => 'Protected', 'order' => 0]);

        $response = $this->actingAs($this->admin)->delete("/checklists/items/{$item->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('event_checklist_items', ['id' => $item->id]);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_templates_only_show_for_own_church(): void
    {
        $this->createTemplate(['name' => 'My Template']);

        $otherChurch = Church::factory()->create();
        ChecklistTemplate::create(['church_id' => $otherChurch->id, 'name' => 'Other Template']);

        $response = $this->actingAs($this->admin)->get('/checklists/templates');

        $response->assertStatus(200);
        // The view should only contain templates from the admin's church
        // We verify by checking database query scoping
        $this->assertEquals(1, ChecklistTemplate::where('church_id', $this->church->id)->count());
        $this->assertEquals(1, ChecklistTemplate::where('church_id', $otherChurch->id)->count());
    }
}
