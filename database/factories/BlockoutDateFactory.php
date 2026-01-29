<?php

namespace Database\Factories;

use App\Models\BlockoutDate;
use App\Models\Church;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlockoutDateFactory extends Factory
{
    protected $model = BlockoutDate::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+2 months');
        return [
            'person_id' => Person::factory(),
            'church_id' => Church::factory(),
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+' . fake()->numberBetween(1, 7) . ' days'),
            'all_day' => true,
            'reason' => fake()->randomElement(array_keys(BlockoutDate::REASONS)),
            'applies_to_all' => true,
            'recurrence' => 'none',
            'status' => 'active',
        ];
    }

    public function forPerson(Person $person): static
    {
        return $this->state(fn (array $attributes) => [
            'person_id' => $person->id,
            'church_id' => $person->church_id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'start_date' => now()->subMonth(),
            'end_date' => now()->subWeek(),
        ]);
    }
}
