<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Person;
use App\Models\SchedulingPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchedulingPreferenceFactory extends Factory
{
    protected $model = SchedulingPreference::class;

    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'church_id' => Church::factory(),
            'max_times_per_month' => fake()->numberBetween(2, 8),
            'preferred_times_per_month' => fake()->numberBetween(1, 4),
            'household_preference' => 'none',
        ];
    }

    public function together(): static
    {
        return $this->state(fn (array $attributes) => [
            'household_preference' => 'together',
            'prefer_with_person_id' => Person::factory(),
        ]);
    }

    public function separate(): static
    {
        return $this->state(fn (array $attributes) => [
            'household_preference' => 'separate',
            'prefer_with_person_id' => Person::factory(),
        ]);
    }

    public function forPerson(Person $person): static
    {
        return $this->state(fn (array $attributes) => [
            'person_id' => $person->id,
            'church_id' => $person->church_id,
        ]);
    }
}
