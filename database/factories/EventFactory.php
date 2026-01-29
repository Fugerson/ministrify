<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'title' => fake()->sentence(3),
            'date' => fake()->dateTimeBetween('now', '+3 months'),
            'time' => fake()->time('H:i'),
        ];
    }

    public function forChurch(Church $church): static
    {
        return $this->state(fn (array $attributes) => [
            'church_id' => $church->id,
        ]);
    }

    public function forMinistry(Ministry $ministry): static
    {
        return $this->state(fn (array $attributes) => [
            'ministry_id' => $ministry->id,
            'church_id' => $ministry->church_id,
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_service' => true,
            'service_type' => Event::SERVICE_SUNDAY,
        ]);
    }

    public function withRegistration(?int $limit = null): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_registration' => true,
            'registration_limit' => $limit,
            'date' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }

    public function withQrCheckin(): static
    {
        return $this->state(fn (array $attributes) => [
            'qr_checkin_enabled' => true,
        ]);
    }
}
