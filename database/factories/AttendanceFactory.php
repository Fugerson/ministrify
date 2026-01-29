<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Church;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'type' => Attendance::TYPE_SERVICE,
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'total_count' => fake()->numberBetween(10, 100),
            'members_present' => fake()->numberBetween(5, 80),
            'guests_count' => fake()->numberBetween(0, 20),
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Attendance::TYPE_SERVICE,
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Attendance::TYPE_GROUP,
        ]);
    }
}
