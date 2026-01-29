<?php

namespace Database\Factories;

use App\Models\Ministry;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'ministry_id' => Ministry::factory(),
            'name' => fake()->randomElement(['Вокал', 'Гітара', 'Клавіші', 'Барабани', 'Звукорежисер', 'Проектор']),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function forMinistry(Ministry $ministry): static
    {
        return $this->state(fn (array $attributes) => [
            'ministry_id' => $ministry->id,
        ]);
    }
}
