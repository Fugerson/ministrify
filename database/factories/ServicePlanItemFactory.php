<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\ServicePlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePlanItemFactory extends Factory
{
    protected $model = ServicePlanItem::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => fake()->sentence(3),
            'type' => ServicePlanItem::TYPE_OTHER,
            'sort_order' => fake()->numberBetween(0, 20),
            'status' => ServicePlanItem::STATUS_PLANNED,
        ];
    }

    public function worship(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ServicePlanItem::TYPE_WORSHIP,
            'title' => 'Прославлення',
        ]);
    }

    public function sermon(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ServicePlanItem::TYPE_SERMON,
            'title' => 'Проповідь',
        ]);
    }

    public function withTimes(string $start = '10:00', string $end = '10:30'): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }

    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
        ]);
    }
}
