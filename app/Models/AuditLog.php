<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'church_id',
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'model_name',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the audited model
     */
    public function auditable()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Get action label in Ukrainian
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Створено',
            'updated' => 'Оновлено',
            'deleted' => 'Видалено',
            'restored' => 'Відновлено',
            'login' => 'Вхід',
            'logout' => 'Вихід',
            'exported' => 'Експортовано',
            'imported' => 'Імпортовано',
            'sent' => 'Надіслано',
            'impersonate' => 'Увійшов як',
            'stop_impersonate' => 'Вийшов з',
            'assigned' => 'Призначено',
            'unassigned' => 'Знято',
            'approved' => 'Підтверджено',
            'rejected' => 'Відхилено',
            'completed' => 'Завершено',
            default => $this->action,
        };
    }

    /**
     * Get human-readable description
     */
    public function getDescriptionAttribute(): string
    {
        $modelLabel = $this->model_label;
        $modelName = $this->model_name;
        $actionLabel = $this->action_label;

        // Special cases
        if ($this->action === 'impersonate') {
            return "Увійшов як користувач: {$modelName}";
        }
        if ($this->action === 'stop_impersonate') {
            return "Вийшов з режиму імперсонації: {$modelName}";
        }
        if ($this->action === 'login') {
            return 'Вхід в систему';
        }
        if ($this->action === 'logout') {
            return 'Вихід з системи';
        }

        // Default: "Action ModelType: ModelName"
        if ($modelName) {
            return "{$actionLabel} {$modelLabel}: {$modelName}";
        }

        return "{$actionLabel} {$modelLabel}";
    }

    /**
     * Get model type label
     */
    public function getModelLabelAttribute(): string
    {
        return match($this->model_type) {
            'App\\Models\\Person' => 'Член церкви',
            'App\\Models\\User' => 'Користувач',
            'App\\Models\\Event' => 'Подія',
            'App\\Models\\Ministry' => 'Служіння',
            'App\\Models\\Group' => 'Група',
            'App\\Models\\Expense' => 'Витрата',
            'App\\Models\\Income' => 'Дохід',
            'App\\Models\\Board' => 'Дошка',
            'App\\Models\\BoardCard' => 'Картка',
            'App\\Models\\Assignment' => 'Призначення',
            'App\\Models\\Attendance' => 'Відвідуваність',
            'App\\Models\\Church' => 'Церква',
            default => class_basename($this->model_type ?? ''),
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'M12 4v16m8-8H4',
            'updated' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'deleted' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            'restored' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'login' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
            'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'restored' => 'purple',
            'login' => 'gray',
            'logout' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): array
    {
        if (!$this->old_values && !$this->new_values) {
            return [];
        }

        $changes = [];
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        // Skip technical fields
        $skip = ['id', 'church_id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        foreach ($new as $key => $value) {
            if (in_array($key, $skip)) continue;

            $oldValue = $old[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[] = [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Scope for church
     */
    public function scopeForChurch($query, int $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    /**
     * Scope for model
     */
    public function scopeForModel($query, string $type, int $id)
    {
        return $query->where('model_type', $type)->where('model_id', $id);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
