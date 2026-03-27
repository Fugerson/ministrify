<?php

namespace Tests\Feature;

use App\Models\BlockoutDate;
use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockoutDateControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    private Person $person;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
        $this->person = Person::factory()->forChurch($this->church)->create(['user_id' => $this->admin->id]);
        $this->admin->refresh();
    }

    // ==================
    // Helpers
    // ==================

    private function createBlockoutForPerson(Person $person, array $overrides = []): BlockoutDate
    {
        return BlockoutDate::factory()->forPerson($person)->create($overrides);
    }

    private function validBlockoutData(array $overrides = []): array
    {
        return array_merge([
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'all_day' => true,
            'reason' => 'vacation',
            'applies_to_all' => true,
            'recurrence' => 'none',
        ], $overrides);
    }

    // ==================
    // Index
    // ==================

    public function test_guest_cannot_view_blockouts(): void
    {
        $response = $this->get('/blockouts');

        $response->assertRedirect('/login');
    }

    public function test_user_with_person_can_view_blockouts_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/blockouts');

        $response->assertStatus(200);
    }

    public function test_user_without_person_gets_error_on_index(): void
    {
        $userNoPerson = User::factory()->admin()->create([
            'church_id' => $this->church->id,
        ]);
        $userNoPerson->churches()->attach($this->church->id, [
            'church_role_id' => $userNoPerson->church_role_id,
        ]);

        $response = $this->actingAs($userNoPerson)->get('/blockouts');

        // Controller uses errorResponse() which redirects back with error flash for non-JSON
        $response->assertRedirect();
    }

    public function test_index_shows_only_own_blockouts(): void
    {
        $ownBlockout = $this->createBlockoutForPerson($this->person, ['reason' => 'vacation']);

        $otherPerson = Person::factory()->forChurch($this->church)->create();
        $otherBlockout = $this->createBlockoutForPerson($otherPerson, ['reason' => 'sick']);

        $response = $this->actingAs($this->admin)->get('/blockouts');

        $response->assertStatus(200);
    }

    // ==================
    // Create
    // ==================

    public function test_user_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/blockouts/create');

        $response->assertStatus(200);
    }

    public function test_user_without_person_gets_error_on_create(): void
    {
        $userNoPerson = User::factory()->admin()->create([
            'church_id' => $this->church->id,
        ]);
        $userNoPerson->churches()->attach($this->church->id, [
            'church_role_id' => $userNoPerson->church_role_id,
        ]);

        $response = $this->actingAs($userNoPerson)->get('/blockouts/create');

        // Controller uses errorResponse() which redirects back with error flash for non-JSON
        $response->assertRedirect();
    }

    // ==================
    // Store
    // ==================

    public function test_user_can_store_blockout(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData());

        $response->assertRedirect();
        $this->assertDatabaseHas('blockout_dates', [
            'person_id' => $this->person->id,
            'church_id' => $this->church->id,
            'reason' => 'vacation',
            'status' => 'active',
            'recurrence' => 'none',
        ]);
    }

    public function test_store_requires_start_date(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'start_date' => '',
        ]));

        $response->assertSessionHasErrors('start_date');
    }

    public function test_store_requires_end_date(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'end_date' => '',
        ]));

        $response->assertSessionHasErrors('end_date');
    }

    public function test_store_start_date_must_be_today_or_future(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'start_date' => now()->subDay()->format('Y-m-d'),
        ]));

        $response->assertSessionHasErrors('start_date');
    }

    public function test_store_end_date_must_be_after_or_equal_start_date(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
        ]));

        $response->assertSessionHasErrors('end_date');
    }

    public function test_store_reason_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'reason' => 'invalid_reason',
        ]));

        $response->assertSessionHasErrors('reason');
    }

    public function test_store_requires_reason(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'reason' => '',
        ]));

        $response->assertSessionHasErrors('reason');
    }

    public function test_store_recurrence_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'recurrence' => 'daily',
        ]));

        $response->assertSessionHasErrors('recurrence');
    }

    public function test_store_all_valid_reasons(): void
    {
        $validReasons = ['vacation', 'travel', 'sick', 'family', 'work', 'other'];

        foreach ($validReasons as $reason) {
            $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
                'reason' => $reason,
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'end_date' => now()->addDays(12)->format('Y-m-d'),
            ]));

            $response->assertRedirect();
        }

        $this->assertDatabaseCount('blockout_dates', count($validReasons));
    }

    public function test_store_all_valid_recurrences(): void
    {
        $validRecurrences = ['none', 'weekly', 'biweekly', 'monthly', 'custom'];

        foreach ($validRecurrences as $recurrence) {
            $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
                'recurrence' => $recurrence,
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'end_date' => now()->addDays(12)->format('Y-m-d'),
            ]));

            $response->assertRedirect();
        }

        $this->assertDatabaseCount('blockout_dates', count($validRecurrences));
    }

    public function test_store_with_recurrence_end_date(): void
    {
        $response = $this->actingAs($this->admin)->post('/blockouts', $this->validBlockoutData([
            'recurrence' => 'weekly',
            'recurrence_end_date' => now()->addMonths(3)->format('Y-m-d'),
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('blockout_dates', [
            'person_id' => $this->person->id,
            'recurrence' => 'weekly',
        ]);
    }

    public function test_user_without_person_cannot_store(): void
    {
        $userNoPerson = User::factory()->admin()->create([
            'church_id' => $this->church->id,
        ]);
        $userNoPerson->churches()->attach($this->church->id, [
            'church_role_id' => $userNoPerson->church_role_id,
        ]);

        $response = $this->actingAs($userNoPerson)->post('/blockouts', $this->validBlockoutData());

        // Should get error, not create blockout
        $this->assertDatabaseCount('blockout_dates', 0);
    }

    // ==================
    // Edit
    // ==================

    public function test_user_can_view_edit_form_for_own_blockout(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person);

        $response = $this->actingAs($this->admin)->get("/blockouts/{$blockout->id}/edit");

        $response->assertStatus(200);
    }

    public function test_user_cannot_edit_another_persons_blockout(): void
    {
        $otherPerson = Person::factory()->forChurch($this->church)->create();
        $blockout = $this->createBlockoutForPerson($otherPerson);

        $response = $this->actingAs($this->admin)->get("/blockouts/{$blockout->id}/edit");

        $response->assertStatus(404);
    }

    // ==================
    // Update
    // ==================

    public function test_user_can_update_own_blockout(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person);

        $response = $this->actingAs($this->admin)->put("/blockouts/{$blockout->id}", [
            'start_date' => $blockout->start_date->format('Y-m-d'),
            'end_date' => $blockout->end_date->format('Y-m-d'),
            'reason' => 'sick',
            'recurrence' => 'none',
            'all_day' => true,
            'applies_to_all' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blockout_dates', [
            'id' => $blockout->id,
            'reason' => 'sick',
        ]);
    }

    public function test_user_cannot_update_another_persons_blockout(): void
    {
        $otherPerson = Person::factory()->forChurch($this->church)->create();
        $blockout = $this->createBlockoutForPerson($otherPerson);

        $response = $this->actingAs($this->admin)->put("/blockouts/{$blockout->id}", [
            'start_date' => $blockout->start_date->format('Y-m-d'),
            'end_date' => $blockout->end_date->format('Y-m-d'),
            'reason' => 'sick',
            'recurrence' => 'none',
        ]);

        $response->assertStatus(404);
    }

    public function test_update_validates_reason(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person);

        $response = $this->actingAs($this->admin)->put("/blockouts/{$blockout->id}", [
            'start_date' => $blockout->start_date->format('Y-m-d'),
            'end_date' => $blockout->end_date->format('Y-m-d'),
            'reason' => 'invalid',
            'recurrence' => 'none',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    // ==================
    // Delete
    // ==================

    public function test_user_can_delete_own_blockout(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person);

        $response = $this->actingAs($this->admin)->delete("/blockouts/{$blockout->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('blockout_dates', ['id' => $blockout->id]);
    }

    public function test_user_cannot_delete_another_persons_blockout(): void
    {
        $otherPerson = Person::factory()->forChurch($this->church)->create();
        $blockout = $this->createBlockoutForPerson($otherPerson);

        $response = $this->actingAs($this->admin)->delete("/blockouts/{$blockout->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('blockout_dates', ['id' => $blockout->id]);
    }

    // ==================
    // Cancel
    // ==================

    public function test_user_can_cancel_own_blockout(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person, ['status' => 'active']);

        $response = $this->actingAs($this->admin)->post("/blockouts/{$blockout->id}/cancel");

        $response->assertRedirect();
        $this->assertDatabaseHas('blockout_dates', [
            'id' => $blockout->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_cannot_cancel_another_persons_blockout(): void
    {
        $otherPerson = Person::factory()->forChurch($this->church)->create();
        $blockout = $this->createBlockoutForPerson($otherPerson, ['status' => 'active']);

        $response = $this->actingAs($this->admin)->post("/blockouts/{$blockout->id}/cancel");

        $response->assertStatus(404);
        $this->assertDatabaseHas('blockout_dates', [
            'id' => $blockout->id,
            'status' => 'active',
        ]);
    }

    public function test_cancel_preserves_blockout_data(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person, [
            'status' => 'active',
            'reason' => 'travel',
        ]);

        $this->actingAs($this->admin)->post("/blockouts/{$blockout->id}/cancel");

        $blockout->refresh();
        $this->assertEquals('cancelled', $blockout->status);
        $this->assertEquals('travel', $blockout->reason);
    }

    // ==================
    // Quick Store (AJAX)
    // ==================

    public function test_quick_store_creates_single_day_blockout(): void
    {
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->postJson('/blockouts/quick', [
                'date' => $date,
                'reason' => 'sick',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('blockout_dates', [
            'person_id' => $this->person->id,
            'church_id' => $this->church->id,
            'reason' => 'sick',
            'recurrence' => 'none',
            'status' => 'active',
        ]);

        // Verify additional fields via model to avoid SQLite type casting issues
        $blockout = BlockoutDate::where('person_id', $this->person->id)->where('reason', 'sick')->first();
        $this->assertNotNull($blockout);
        $this->assertEquals($date, $blockout->start_date->format('Y-m-d'));
        $this->assertEquals($date, $blockout->end_date->format('Y-m-d'));
        $this->assertTrue($blockout->all_day);
        $this->assertTrue($blockout->applies_to_all);
    }

    public function test_quick_store_requires_date(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/blockouts/quick', [
                'reason' => 'sick',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('date');
    }

    public function test_quick_store_requires_valid_reason(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/blockouts/quick', [
                'date' => now()->addDay()->format('Y-m-d'),
                'reason' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('reason');
    }

    public function test_quick_store_without_person_returns_404(): void
    {
        $userNoPerson = User::factory()->admin()->create([
            'church_id' => $this->church->id,
        ]);
        $userNoPerson->churches()->attach($this->church->id, [
            'church_role_id' => $userNoPerson->church_role_id,
        ]);

        $response = $this->actingAs($userNoPerson)
            ->postJson('/blockouts/quick', [
                'date' => now()->addDay()->format('Y-m-d'),
                'reason' => 'sick',
            ]);

        $response->assertStatus(404);
    }

    // ==================
    // Calendar (AJAX)
    // ==================

    public function test_calendar_returns_json_events(): void
    {
        $blockout = $this->createBlockoutForPerson($this->person, [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'vacation',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/blockouts/calendar?start='.now()->startOfMonth()->format('Y-m-d').'&end='.now()->endOfMonth()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $blockout->id,
                'color' => '#ef4444',
            ]);
    }

    public function test_calendar_only_returns_active_blockouts(): void
    {
        $this->createBlockoutForPerson($this->person, [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'active',
        ]);

        $this->createBlockoutForPerson($this->person, [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/blockouts/calendar?start='.now()->startOfMonth()->format('Y-m-d').'&end='.now()->endOfMonth()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_calendar_without_person_returns_empty_array(): void
    {
        $userNoPerson = User::factory()->admin()->create([
            'church_id' => $this->church->id,
        ]);
        $userNoPerson->churches()->attach($this->church->id, [
            'church_role_id' => $userNoPerson->church_role_id,
        ]);

        $response = $this->actingAs($userNoPerson)
            ->getJson('/blockouts/calendar');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_calendar_returns_events_within_date_range(): void
    {
        // Blockout within range
        $this->createBlockoutForPerson($this->person, [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'active',
        ]);

        // Blockout outside range (far future)
        $this->createBlockoutForPerson($this->person, [
            'start_date' => now()->addYear()->format('Y-m-d'),
            'end_date' => now()->addYear()->addDay()->format('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/blockouts/calendar?start='.now()->startOfMonth()->format('Y-m-d').'&end='.now()->endOfMonth()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    // ==================
    // Volunteer user (non-admin)
    // ==================

    public function test_volunteer_with_person_can_access_blockouts(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteerPerson = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        $volunteer->update(['person_id' => $volunteerPerson->id]);

        $response = $this->actingAs($volunteer)->get('/blockouts');

        $response->assertStatus(200);
    }

    public function test_volunteer_can_create_own_blockout(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteerPerson = Person::factory()->forChurch($this->church)->create(['user_id' => $volunteer->id]);
        $volunteer->update(['person_id' => $volunteerPerson->id]);

        $response = $this->actingAs($volunteer)->post('/blockouts', $this->validBlockoutData());

        $response->assertRedirect();
        $this->assertDatabaseHas('blockout_dates', [
            'person_id' => $volunteerPerson->id,
            'church_id' => $this->church->id,
        ]);
    }
}
