<?php

namespace Database\Factories;

use App\Models\Church;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChurchFactory extends Factory
{
    protected $model = Church::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Church',
            'slug' => Str::slug(fake()->unique()->company()),
            'city' => fake()->city(),
            'address' => fake()->address(),
        ];
    }
}
