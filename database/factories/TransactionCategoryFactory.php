<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\TransactionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionCategoryFactory extends Factory
{
    protected $model = TransactionCategory::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'name' => fake()->randomElement(['Rent', 'Utilities', 'Equipment', 'Supplies', 'Travel']),
            'type' => 'expense',
            'icon' => 'currency-dollar',
            'color' => fake()->hexColor(),
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'name' => fake()->randomElement(['Tithe', 'Offering', 'Donation', 'Other Income']),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'name' => fake()->randomElement(['Rent', 'Utilities', 'Equipment', 'Ministry Expense']),
        ]);
    }

    public function both(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'both',
        ]);
    }
}
