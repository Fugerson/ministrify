<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulingConflict extends Model
{
    protected $fillable = [
        'assignment_id',
        'conflict_type',
        'conflict_details',
        'was_overridden',
        'overridden_by',
    ];

    protected $casts = [
        'was_overridden' => 'boolean',
    ];

    public const CONFLICT_TYPES = [
        'blockout' => [
            'label' => 'Недоступність',
            'icon' => '🚫',
            'color' => 'red',
            'description' => 'Людина має blockout на цю дату',
        ],
        'concurrent' => [
            'label' => 'Паралельне призначення',
            'icon' => '⚠️',
            'color' => 'orange',
            'description' => 'Вже призначена на інше служіння в цей час',
        ],
        'preference_limit' => [
            'label' => 'Перевищення преференцій',
            'icon' => '📊',
            'color' => 'yellow',
            'description' => 'Перевищує бажану кількість разів на місяць',
        ],
        'max_limit' => [
            'label' => 'Максимальний ліміт',
            'icon' => '🔴',
            'color' => 'red',
            'description' => 'Перевищує максимальну кількість разів на місяць',
        ],
        'household' => [
            'label' => 'Сімейна преференція',
            'icon' => '👨‍👩‍👧',
            'color' => 'blue',
            'description' => 'Конфлікт з сімейними преференціями',
        ],
    ];

    // ========== RELATIONSHIPS ==========

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function overriddenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    // ========== HELPERS ==========

    public function getTypeConfigAttribute(): array
    {
        return self::CONFLICT_TYPES[$this->conflict_type] ?? [
            'label' => $this->conflict_type,
            'icon' => '❓',
            'color' => 'gray',
            'description' => '',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->typeConfig['label'];
    }

    public function getTypeIconAttribute(): string
    {
        return $this->typeConfig['icon'];
    }

    public function getTypeColorAttribute(): string
    {
        return $this->typeConfig['color'];
    }

    /**
     * Create a conflict record for an assignment
     */
    public static function record($assignmentId, string $type, string $details = null): self
    {
        return self::create([
            'assignment_id' => $assignmentId,
            'conflict_type' => $type,
            'conflict_details' => $details,
        ]);
    }

    /**
     * Mark conflict as overridden
     */
    public function override($userId): void
    {
        $this->update([
            'was_overridden' => true,
            'overridden_by' => $userId,
        ]);
    }
}
