<?php

namespace Database\Factories;

use App\Models\ChurchRole;
use App\Models\ChurchRolePermission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChurchRolePermissionFactory extends Factory
{
    protected $model = ChurchRolePermission::class;

    public function definition(): array
    {
        return [
            'church_role_id' => ChurchRole::factory(),
            'module' => fake()->randomElement(array_keys(ChurchRolePermission::MODULES)),
            'actions' => ['view'],
        ];
    }

    public function fullAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'actions' => ['view', 'create', 'edit', 'delete'],
        ]);
    }

    public function readOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'actions' => ['view'],
        ]);
    }

    public function forModule(string $module): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => $module,
        ]);
    }
}
