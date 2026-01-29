<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\ChurchRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChurchRoleFactory extends Factory
{
    protected $model = ChurchRole::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Волонтер', 'Музикант', 'Технік', 'Координатор']);
        return [
            'church_id' => Church::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(0, 10),
            'is_admin_role' => false,
            'is_default' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Адміністратор',
            'slug' => 'admin',
            'is_admin_role' => true,
        ]);
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }
}
