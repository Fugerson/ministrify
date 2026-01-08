<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'volunteer',
            'onboarding_completed' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Create leader user.
     */
    public function leader(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'leader',
        ]);
    }

    /**
     * Create user with church.
     */
    public function withChurch(): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => Church::factory(),
        ]);
    }
}
