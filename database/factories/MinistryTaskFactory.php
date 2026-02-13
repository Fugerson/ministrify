<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\MinistryGoal;
use App\Models\MinistryTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinistryTaskFactory extends Factory
{
    protected $model = MinistryTask::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'ministry_id' => Ministry::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => 'todo',
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'sort_order' => fake()->numberBetween(0, 100),
            'created_by' => null,
        ];
    }

    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'todo',
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completed_at' => null,
            'completed_by' => null,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'completed_at' => now(),
            'completed_by' => null,
        ]);
    }

    public function forGoal(MinistryGoal $goal): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_id' => $goal->id,
            'ministry_id' => $goal->ministry_id,
            'church_id' => $goal->church_id,
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
