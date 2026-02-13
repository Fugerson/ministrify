<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\MonobankTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonobankTransactionFactory extends Factory
{
    protected $model = MonobankTransaction::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'mono_id' => fake()->unique()->uuid(),
            'amount' => fake()->numberBetween(-100000, 100000),
            'balance' => fake()->numberBetween(0, 10000000),
            'currency_code' => '980',
            'mono_time' => now(),
            'description' => fake()->sentence(),
            'mcc' => fake()->randomElement([4900, 5411, 5812, 5541, null]),
            'is_income' => false,
            'is_processed' => false,
            'is_ignored' => false,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => abs(fake()->numberBetween(10000, 500000)),
            'is_income' => true,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => -abs(fake()->numberBetween(10000, 500000)),
            'is_income' => false,
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_processed' => true,
        ]);
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }
}
