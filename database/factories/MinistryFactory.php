<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MinistryFactory extends Factory
{
    protected $model = Ministry::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Worship', 'Youth', 'Children', 'Media', 'Hospitality']) . ' Ministry';

        return [
            'church_id' => Church::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'monthly_budget' => fake()->randomFloat(2, 1000, 10000),
            'visibility' => 'public',
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function withLeader(Person $leader): static
    {
        return $this->state(fn (array $attributes) => [
            'leader_id' => $leader->id,
        ]);
    }

    public function withBudget(float $budget): static
    {
        return $this->state(fn (array $attributes) => [
            'monthly_budget' => $budget,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'members',
            'is_private' => true,
        ]);
    }
}
