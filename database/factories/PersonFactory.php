<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_date' => fake()->dateTimeBetween('-70 years', '-18 years'),
            'membership_status' => 'member',
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function shepherd(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shepherd' => true,
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'guest',
        ]);
    }

    public function servant(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'servant',
        ]);
    }

    public function leader(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'leader',
        ]);
    }

    public function leadership(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'leadership',
        ]);
    }
}
