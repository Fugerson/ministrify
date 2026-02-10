<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class EventTaskTemplate extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'name',
        'description',
        'tasks',
        'auto_create',
        'days_before',
    ];

    protected $casts = [
        'tasks' => 'array',
        'auto_create' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'task_template_id');
    }

    /**
     * Create tasks for an event based on this template
     */
    public function createTasksForEvent(Event $event): array
    {
        $createdCards = [];
        $board = Board::where('church_id', $event->church_id)
            ->where('name', 'Трекер завдань')
            ->first();

        if (!$board) {
            return $createdCards;
        }

        $column = $board->columns()->where('name', 'До виконання')->first()
            ?? $board->columns()->first();

        if (!$column) {
            return $createdCards;
        }

        foreach ($this->tasks as $taskDef) {
            $dueDate = null;
            if (isset($taskDef['days_before'])) {
                $dueDate = $event->date->copy()->subDays($taskDef['days_before']);
            }

            $card = BoardCard::create([
                'column_id' => $column->id,
                'title' => $this->parseTaskTitle($taskDef['title'], $event),
                'description' => $taskDef['description'] ?? null,
                'priority' => $taskDef['priority'] ?? 'medium',
                'due_date' => $dueDate,
                'event_id' => $event->id,
                'position' => BoardCard::where('column_id', $column->id)->max('position') + 1,
            ]);

            $createdCards[] = $card;
        }

        return $createdCards;
    }

    /**
     * Parse task title with event placeholders
     */
    protected function parseTaskTitle(string $title, Event $event): string
    {
        return str_replace(
            ['{event_title}', '{event_date}', '{ministry_name}'],
            [$event->title, $event->date->format('d.m.Y'), $event->ministry?->name ?? ''],
            $title
        );
    }

    /**
     * Get default task definitions for common event types
     */
    public static function getDefaultTasks(string $eventType): array
    {
        return match ($eventType) {
            'service' => [
                ['title' => 'Підготувати проповідь для {event_title}', 'days_before' => 3, 'priority' => 'high'],
                ['title' => 'Підготувати прославлення для {event_title}', 'days_before' => 2, 'priority' => 'high'],
                ['title' => 'Перевірити обладнання', 'days_before' => 1, 'priority' => 'medium'],
                ['title' => 'Підготувати зал', 'days_before' => 0, 'priority' => 'medium'],
            ],
            'meeting' => [
                ['title' => 'Підготувати порядок денний для {event_title}', 'days_before' => 2, 'priority' => 'medium'],
                ['title' => 'Надіслати нагадування учасникам', 'days_before' => 1, 'priority' => 'medium'],
            ],
            'youth' => [
                ['title' => 'Підготувати тему для молоді', 'days_before' => 3, 'priority' => 'high'],
                ['title' => 'Організувати ігри/активності', 'days_before' => 2, 'priority' => 'medium'],
                ['title' => 'Підготувати перекус', 'days_before' => 1, 'priority' => 'low'],
            ],
            default => [
                ['title' => 'Підготуватися до {event_title}', 'days_before' => 2, 'priority' => 'medium'],
            ],
        };
    }
}
