<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Event;
use App\Models\Person;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'position_id' => Position::factory(),
            'person_id' => Person::factory(),
            'status' => Assignment::STATUS_PENDING,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Assignment::STATUS_PENDING,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Assignment::STATUS_CONFIRMED,
            'responded_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Assignment::STATUS_DECLINED,
            'responded_at' => now(),
        ]);
    }

    public function attended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Assignment::STATUS_ATTENDED,
        ]);
    }
}
