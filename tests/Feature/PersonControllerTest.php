<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->church = Church::factory()->create(['shepherds_enabled' => true]);
        $this->admin = User::factory()->admin()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_view_people_index(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('PersonController uses MySQL-specific TIMESTAMPDIFF');
        }

        $response = $this->actingAs($this->admin)->get('/people');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_people(): void
    {
        $response = $this->get('/people');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_create_person(): void
    {
        $personData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+380501234567',
            'gender' => 'male',
        ];

        $response = $this->actingAs($this->admin)->post('/people', $personData);

        $response->assertRedirect();
        $this->assertDatabaseHas('people', [
            'church_id' => $this->church->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_admin_can_update_shepherd(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => $shepherd->id,
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'shepherd_id' => $shepherd->id,
        ]);
    }

    public function test_cannot_assign_shepherd_from_other_church(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $otherChurch = Church::factory()->create();
        $otherShepherd = Person::factory()->forChurch($otherChurch)->shepherd()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => $otherShepherd->id,
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'shepherd_id' => null,
        ]);
    }

    public function test_cannot_assign_non_shepherd_as_shepherd(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        $notAShepherd = Person::factory()->forChurch($this->church)->create([
            'is_shepherd' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => $notAShepherd->id,
            ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Ця людина не є опікуном']);
    }

    public function test_person_cannot_be_own_shepherd(): void
    {
        $person = Person::factory()->forChurch($this->church)->shepherd()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => $person->id,
            ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Людина не може бути своїм опікуном']);
    }

    public function test_admin_can_remove_shepherd(): void
    {
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();
        $person = Person::factory()->forChurch($this->church)->create([
            'shepherd_id' => $shepherd->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => null,
            ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'shepherd_id' => null,
        ]);
    }

    public function test_volunteer_cannot_update_shepherd(): void
    {
        $volunteer = User::factory()->create([
            'church_id' => $this->church->id,
            'email_verified_at' => now(),
            'role' => 'volunteer',
        ]);

        $person = Person::factory()->forChurch($this->church)->create();
        $shepherd = Person::factory()->forChurch($this->church)->shepherd()->create();

        $response = $this->actingAs($volunteer)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => $shepherd->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_view_person_details(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->get("/people/{$person->id}");

        $response->assertStatus(200);
    }

    public function test_admin_cannot_view_person_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)->get("/people/{$otherPerson->id}");

        $response->assertStatus(404);
    }

    public function test_admin_can_delete_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)->delete("/people/{$person->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('people', ['id' => $person->id]);
    }

    public function test_shepherd_disabled_returns_error(): void
    {
        $church = Church::factory()->create(['shepherds_enabled' => false]);
        $admin = User::factory()->admin()->create([
            'church_id' => $church->id,
            'email_verified_at' => now(),
        ]);
        $person = Person::factory()->forChurch($church)->create();
        $shepherd = Person::factory()->forChurch($church)->shepherd()->create();

        $response = $this->actingAs($admin)
            ->postJson("/people/{$person->id}/update-shepherd", [
                'shepherd_id' => $shepherd->id,
            ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Функція опікунів вимкнена']);
    }
}
