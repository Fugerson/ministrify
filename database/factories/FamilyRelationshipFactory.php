<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\FamilyRelationship;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyRelationshipFactory extends Factory
{
    protected $model = FamilyRelationship::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'person_id' => Person::factory(),
            'related_person_id' => Person::factory(),
            'relationship_type' => fake()->randomElement(['spouse', 'child', 'parent', 'sibling']),
        ];
    }

    public function spouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship_type' => FamilyRelationship::TYPE_SPOUSE,
        ]);
    }

    public function parentChild(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship_type' => FamilyRelationship::TYPE_PARENT,
        ]);
    }

    public function sibling(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship_type' => FamilyRelationship::TYPE_SIBLING,
        ]);
    }
}
