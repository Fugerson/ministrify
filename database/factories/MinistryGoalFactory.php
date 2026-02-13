<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinistryGoalFactory extends Factory
{
    protected $model = MinistryGoal::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'ministry_id' => Ministry::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'period' => fake()->randomElement(['weekly', 'monthly', 'quarterly', 'yearly']),
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => 'active',
            'progress' => fake()->numberBetween(0, 100),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'created_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'due_date' => now()->subWeek(),
        ]);
    }

    public function forMinistry(Ministry $ministry): static
    {
        return $this->state(fn (array $attributes) => [
            'ministry_id' => $ministry->id,
            'church_id' => $ministry->church_id,
        ]);
    }
}
