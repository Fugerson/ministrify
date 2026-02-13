<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\FamilyRelationship;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyRelationshipTest extends TestCase
{
    use RefreshDatabase;

    // ==================
    // getInverseType
    // ==================

    public function test_inverse_type_spouse_returns_spouse(): void
    {
        $this->assertEquals('spouse', FamilyRelationship::getInverseType('spouse'));
    }

    public function test_inverse_type_child_returns_parent(): void
    {
        $this->assertEquals('parent', FamilyRelationship::getInverseType('child'));
    }

    public function test_inverse_type_parent_returns_child(): void
    {
        $this->assertEquals('child', FamilyRelationship::getInverseType('parent'));
    }

    public function test_inverse_type_sibling_returns_sibling(): void
    {
        $this->assertEquals('sibling', FamilyRelationship::getInverseType('sibling'));
    }

    public function test_inverse_type_unknown_returns_same(): void
    {
        $this->assertEquals('unknown', FamilyRelationship::getInverseType('unknown'));
    }

    // ==================
    // getTypes
    // ==================

    public function test_get_types_returns_all_types(): void
    {
        $types = FamilyRelationship::getTypes();

        $this->assertArrayHasKey('spouse', $types);
        $this->assertArrayHasKey('child', $types);
        $this->assertArrayHasKey('parent', $types);
        $this->assertArrayHasKey('sibling', $types);
        $this->assertCount(4, $types);
    }

    public function test_get_type_label_for_spouse(): void
    {
        $church = Church::factory()->create();
        $person = Person::factory()->forChurch($church)->create();
        $related = Person::factory()->forChurch($church)->create();

        $rel = FamilyRelationship::factory()->create([
            'church_id' => $church->id,
            'person_id' => $person->id,
            'related_person_id' => $related->id,
            'relationship_type' => 'spouse',
        ]);

        $this->assertEquals('Чоловік/Дружина', $rel->getTypeLabel());
    }

    public function test_get_type_label_for_child(): void
    {
        $church = Church::factory()->create();
        $person = Person::factory()->forChurch($church)->create();
        $related = Person::factory()->forChurch($church)->create();

        $rel = FamilyRelationship::factory()->create([
            'church_id' => $church->id,
            'person_id' => $person->id,
            'related_person_id' => $related->id,
            'relationship_type' => 'child',
        ]);

        $this->assertEquals('Дитина', $rel->getTypeLabel());
    }
}
