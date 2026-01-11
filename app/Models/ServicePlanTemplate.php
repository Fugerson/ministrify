<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'name',
        'description',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Apply this template to an event
     * Creates ServicePlanItem records for each template item
     */
    public function applyToEvent(Event $event, Carbon $startTime): void
    {
        $currentTime = $startTime->copy();
        $sortOrder = 0;

        // Delete existing plan items for this event
        $event->servicePlanItems()->delete();

        foreach ($this->items as $item) {
            $duration = $item['duration_minutes'] ?? ServicePlanItem::getDefaultDuration($item['type'] ?? 'other');
            $endTime = $currentTime->copy()->addMinutes($duration);

            ServicePlanItem::create([
                'event_id' => $event->id,
                'title' => $item['title'],
                'type' => $item['type'] ?? null,
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'responsible_names' => $item['responsible_names'] ?? null,
                'notes' => $item['notes'] ?? null,
                'sort_order' => $sortOrder++,
                'status' => ServicePlanItem::STATUS_PLANNED,
            ]);

            $currentTime = $endTime;
        }
    }

    /**
     * Create template from event's service plan
     */
    public static function createFromEvent(Event $event, string $name, bool $includeResponsible = false, ?string $description = null): self
    {
        $items = $event->servicePlanItems()->ordered()->get()->map(function ($item) use ($includeResponsible) {
            $templateItem = [
                'title' => $item->title,
                'type' => $item->type,
                'duration_minutes' => $item->duration_minutes ?? ServicePlanItem::getDefaultDuration($item->type ?? 'other'),
                'notes' => $item->notes,
            ];

            if ($includeResponsible && $item->responsible_names) {
                $templateItem['responsible_names'] = $item->responsible_names;
            }

            return $templateItem;
        })->toArray();

        return self::create([
            'church_id' => $event->church_id,
            'name' => $name,
            'description' => $description,
            'items' => $items,
        ]);
    }
}
