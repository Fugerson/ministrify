<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\ChurchRole;
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
            'onboarding_completed' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            if (!$user->church_id) {
                return;
            }
            $role = ChurchRole::firstOrCreate(
                ['church_id' => $user->church_id, 'slug' => 'admin'],
                ['name' => 'Адміністратор', 'is_admin_role' => true, 'sort_order' => 0]
            );
            $user->update(['church_role_id' => $role->id]);
        });
    }

    public function leader(): static
    {
        return $this->afterCreating(function (User $user) {
            if (!$user->church_id) {
                return;
            }
            $role = ChurchRole::firstOrCreate(
                ['church_id' => $user->church_id, 'slug' => 'leader'],
                ['name' => 'Лідер', 'is_admin_role' => false, 'sort_order' => 1]
            );
            $user->update(['church_role_id' => $role->id]);
        });
    }

    public function volunteer(): static
    {
        return $this->afterCreating(function (User $user) {
            if (!$user->church_id) {
                return;
            }
            $role = ChurchRole::firstOrCreate(
                ['church_id' => $user->church_id, 'slug' => 'volunteer'],
                ['name' => 'Волонтер', 'is_admin_role' => false, 'sort_order' => 2]
            );
            $user->update(['church_role_id' => $role->id]);
        });
    }

    public function withChurch(): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => Church::factory(),
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_super_admin' => true,
        ]);
    }
}
