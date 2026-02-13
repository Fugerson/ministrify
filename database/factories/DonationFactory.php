<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'UAH',
            'type' => 'one_time',
            'status' => 'completed',
            'payment_method' => 'cash',
            'is_anonymous' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
            'person_id' => null,
            'donor_name' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }
}
