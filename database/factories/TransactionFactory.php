<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'direction' => Transaction::DIRECTION_OUT,
            'source_type' => Transaction::SOURCE_EXPENSE,
            'amount' => fake()->randomFloat(2, 100, 5000),
            'currency' => 'UAH',
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => Transaction::STATUS_COMPLETED,
            'description' => fake()->sentence(),
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => Transaction::DIRECTION_OUT,
            'source_type' => Transaction::SOURCE_EXPENSE,
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_INCOME,
        ]);
    }

    public function tithe(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_TITHE,
        ]);
    }

    public function forMinistry(Ministry $ministry): static
    {
        return $this->state(fn (array $attributes) => [
            'ministry_id' => $ministry->id,
            'church_id' => $ministry->church_id,
        ]);
    }

    public function withCategory(TransactionCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function recordedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_by' => $user->id,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_COMPLETED,
        ]);
    }

    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }
}
