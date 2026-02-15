<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardCardActivity extends Model
{
    protected $fillable = [
        'card_id',
        'user_id',
        'action',
        'field',
        'old_value',
        'new_value',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(BoardCard::class, 'card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper to create activity
    public static function log(BoardCard $card, string $action, ?string $field = null, $oldValue = null, $newValue = null, ?array $metadata = null): ?self
    {
        // Super admin is invisible in activity logs
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return null;
        }

        return static::create([
            'card_id' => $card->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'field' => $field,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'metadata' => $metadata,
        ]);
    }

    // Human-readable description
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user->name ?? 'Хтось';
        $checklistTitle = $this->metadata['title'] ?? '';

        return match($this->action) {
            'created' => "{$userName} створив завдання",
            'updated' => $this->getUpdateDescription($userName),
            'moved' => "{$userName} перемістив у \"{$this->new_value}\"",
            'completed' => "{$userName} завершив завдання",
            'reopened' => "{$userName} відкрив знову",
            'comment_added' => "{$userName} додав коментар",
            'comment_edited' => "{$userName} редагував коментар",
            'comment_deleted' => "{$userName} видалив коментар",
            'checklist_added' => "{$userName} додав пункт: \"{$this->new_value}\"",
            'checklist_completed' => "{$userName} виконав: \"{$checklistTitle}\"",
            'checklist_uncompleted' => "{$userName} зняв виконання: \"{$checklistTitle}\"",
            'checklist_deleted' => "{$userName} видалив пункт",
            'attachment_added' => "{$userName} додав файл: \"{$this->new_value}\"",
            'attachment_deleted' => "{$userName} видалив файл: \"{$this->old_value}\"",
            'related_added' => "{$userName} пов'язав з: \"{$this->new_value}\"",
            'related_removed' => "{$userName} видалив зв'язок з: \"{$this->old_value}\"",
            'assigned' => $this->new_value ? "{$userName} призначив на {$this->new_value}" : "{$userName} зняв призначення",
            'priority_changed' => "{$userName} змінив пріоритет на {$this->getPriorityLabel($this->new_value)}",
            'due_date_changed' => $this->new_value ? "{$userName} встановив дедлайн: {$this->new_value}" : "{$userName} зняв дедлайн",
            default => "{$userName} змінив завдання",
        };
    }

    private function getUpdateDescription(string $userName): string
    {
        return match($this->field) {
            'title' => "{$userName} змінив назву",
            'description' => "{$userName} змінив опис",
            'priority' => "{$userName} змінив пріоритет на {$this->getPriorityLabel($this->new_value)}",
            'assigned_to' => $this->new_value ? "{$userName} призначив виконавця" : "{$userName} зняв виконавця",
            'due_date' => $this->new_value ? "{$userName} встановив дедлайн" : "{$userName} зняв дедлайн",
            'column_id' => "{$userName} перемістив завдання",
            default => "{$userName} оновив завдання",
        };
    }

    private function getPriorityLabel(?string $priority): string
    {
        return match($priority) {
            'urgent' => 'Терміновий',
            'high' => 'Високий',
            'medium' => 'Середній',
            'low' => 'Низький',
            default => $priority ?? '',
        };
    }
}
