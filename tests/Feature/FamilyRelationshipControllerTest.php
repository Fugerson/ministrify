<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\ChurchRolePermission;
use App\Models\FamilyRelationship;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FamilyRelationshipControllerTest extends TestCase
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

    public function test_admin_can_create_family_relationship(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personB->id,
                'relationship_type' => 'sibling',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('family_relationships', [
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'sibling',
        ]);
    }

    public function test_store_validates_related_person_id_required(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/family", [
                'relationship_type' => 'sibling',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('related_person_id');
    }

    public function test_store_validates_relationship_type(): void
    {
        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personB->id,
                'relationship_type' => 'cousin',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('relationship_type');
    }

    // ==================
    // Self-relationship prevention
    // ==================

    public function test_cannot_create_self_relationship(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$person->id}/family", [
                'related_person_id' => $person->id,
                'relationship_type' => 'sibling',
            ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('family_relationships', [
            'person_id' => $person->id,
            'related_person_id' => $person->id,
        ]);
    }

    // ==================
    // Duplicate prevention
    // ==================

    public function test_cannot_create_duplicate_relationship(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'sibling',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personB->id,
                'relationship_type' => 'sibling',
            ]);

        $response->assertJson(['success' => false]);
        $this->assertEquals(1, FamilyRelationship::where('person_id', $personA->id)
            ->where('related_person_id', $personB->id)
            ->count());
    }

    // ==================
    // Contradictory relationship prevention
    // ==================

    public function test_cannot_create_contradictory_relationship(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        // A is parent of B
        FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'parent',
        ]);

        // Try to make B parent of A (contradictory)
        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personB->id}/family", [
                'related_person_id' => $personA->id,
                'relationship_type' => 'parent',
            ]);

        $response->assertJson(['success' => false]);
    }

    public function test_cannot_create_reverse_direction_relationship(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        // A->B sibling exists
        FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'sibling',
        ]);

        // Try B->A sibling (contradictory / already exists in reverse)
        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personB->id}/family", [
                'related_person_id' => $personA->id,
                'relationship_type' => 'sibling',
            ]);

        $response->assertJson(['success' => false]);
    }

    // ==================
    // Spouse constraints
    // ==================

    public function test_cannot_assign_spouse_if_person_already_has_spouse(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();
        $personC = Person::factory()->forChurch($this->church)->create();

        // A is married to B
        FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'spouse',
        ]);

        // Try to make A spouse of C (A already has a spouse)
        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personC->id,
                'relationship_type' => 'spouse',
            ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('family_relationships', [
            'person_id' => $personA->id,
            'related_person_id' => $personC->id,
        ]);
    }

    public function test_cannot_assign_spouse_if_related_person_already_has_spouse(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Store operations use MySQL-specific features');
        }

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();
        $personC = Person::factory()->forChurch($this->church)->create();

        // B is married to C
        FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personB->id,
            'related_person_id' => $personC->id,
            'relationship_type' => 'spouse',
        ]);

        // Try to make A spouse of B (B already has a spouse)
        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personB->id,
                'relationship_type' => 'spouse',
            ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseMissing('family_relationships', [
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'spouse',
        ]);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_related_person_must_be_from_same_church(): void
    {
        $personA = Person::factory()->forChurch($this->church)->create();

        $otherChurch = Church::factory()->create();
        $personFromOtherChurch = Person::factory()->forChurch($otherChurch)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personFromOtherChurch->id,
                'relationship_type' => 'sibling',
            ]);

        // BelongsToChurch rule or church_id check should reject
        $this->assertTrue(in_array($response->status(), [404, 422]));
        $this->assertDatabaseMissing('family_relationships', [
            'person_id' => $personA->id,
            'related_person_id' => $personFromOtherChurch->id,
        ]);
    }

    public function test_cannot_create_relationship_for_person_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherPerson = Person::factory()->forChurch($otherChurch)->create();
        $localPerson = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/people/{$otherPerson->id}/family", [
                'related_person_id' => $localPerson->id,
                'relationship_type' => 'parent',
            ]);

        $response->assertStatus(403);
    }

    // ==================
    // Destroy
    // ==================

    public function test_admin_can_delete_family_relationship(): void
    {
        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        $relationship = FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'sibling',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/family/{$relationship->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('family_relationships', ['id' => $relationship->id]);
    }

    public function test_cannot_delete_relationship_from_other_church(): void
    {
        $otherChurch = Church::factory()->create();
        $personA = Person::factory()->forChurch($otherChurch)->create();
        $personB = Person::factory()->forChurch($otherChurch)->create();

        $relationship = FamilyRelationship::create([
            'church_id' => $otherChurch->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'sibling',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/family/{$relationship->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('family_relationships', ['id' => $relationship->id]);
    }

    // ==================
    // Search
    // ==================

    public function test_admin_can_search_family_members(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/people/{$person->id}/family/search?q=John");

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'John Doe']);
    }

    public function test_search_excludes_current_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'first_name' => 'John',
            'last_name' => 'Main',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/people/{$person->id}/family/search?q=John");

        $response->assertOk();
        // Should not include the person themselves
        $ids = collect($response->json())->pluck('id')->toArray();
        $this->assertNotContains($person->id, $ids);
    }

    public function test_search_only_returns_people_from_same_church(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $otherChurch = Church::factory()->create();
        Person::factory()->forChurch($otherChurch)->create([
            'first_name' => 'External',
            'last_name' => 'Person',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/people/{$person->id}/family/search?q=External");

        $response->assertOk();
        $response->assertJsonCount(0);
    }

    // ==================
    // Permission checks
    // ==================

    public function test_volunteer_without_people_edit_cannot_create_relationship(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['people' => ['view']]);

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($volunteer)
            ->postJson("/people/{$personA->id}/family", [
                'related_person_id' => $personB->id,
                'relationship_type' => 'sibling',
            ]);

        $response->assertStatus(403);
    }

    public function test_volunteer_without_people_edit_cannot_delete_relationship(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['people' => ['view']]);

        $personA = Person::factory()->forChurch($this->church)->create();
        $personB = Person::factory()->forChurch($this->church)->create();

        $relationship = FamilyRelationship::create([
            'church_id' => $this->church->id,
            'person_id' => $personA->id,
            'related_person_id' => $personB->id,
            'relationship_type' => 'sibling',
        ]);

        $response = $this->actingAs($volunteer)
            ->deleteJson("/family/{$relationship->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('family_relationships', ['id' => $relationship->id]);
    }

    public function test_volunteer_with_people_view_can_search_family(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        $volunteer->churchRole->setPermissions(['people' => ['view']]);

        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($volunteer)
            ->getJson("/people/{$person->id}/family/search?q=test");

        $response->assertOk();
    }

    public function test_volunteer_without_people_view_cannot_search_family(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');
        // setPermissions([]) is a no-op — must delete existing permissions to truly clear them
        ChurchRolePermission::where('church_role_id', $volunteer->church_role_id)->delete();

        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->actingAs($volunteer)
            ->getJson("/people/{$person->id}/family/search?q=test");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_family_relationship(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $response = $this->postJson("/people/{$person->id}/family", [
            'related_person_id' => 1,
            'relationship_type' => 'sibling',
        ]);

        $response->assertStatus(401);
    }
}
