<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Мала група', 'Домашня група', 'Молитовна група']) . ' ' . fake()->lastName();
        return [
            'church_id' => Church::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'status' => Group::STATUS_ACTIVE,
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Group::STATUS_ACTIVE,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Group::STATUS_PAUSED,
        ]);
    }

    public function vacation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Group::STATUS_VACATION,
        ]);
    }

    public function withLeader(Person $leader): static
    {
        return $this->state(fn (array $attributes) => [
            'leader_id' => $leader->id,
        ]);
    }
}
