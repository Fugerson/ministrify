<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinistryGoal extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'title',
        'description',
        'period',
        'due_date',
        'status',
        'progress',
        'priority',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'progress' => 'integer',
    ];

    public const STATUSES = [
        'active' => 'Активна',
        'completed' => 'Виконана',
        'on_hold' => 'На паузі',
        'cancelled' => 'Скасована',
    ];

    public const PRIORITIES = [
        'low' => 'Низький',
        'medium' => 'Середній',
        'high' => 'Високий',
    ];

    // Relationships
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MinistryTask::class, 'goal_id');
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->whereIn('status', ['todo', 'in_progress']);
    }

    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('status', 'done');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status === 'active';
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->due_date || $this->status !== 'active') {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false);
    }

    public function getCalculatedProgressAttribute(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return $this->progress;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        return (int) round(($completedTasks / $totalTasks) * 100);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'blue',
            'completed' => 'green',
            'on_hold' => 'yellow',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    // Methods
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }

    public function updateProgressFromTasks(): void
    {
        $this->update(['progress' => $this->calculated_progress]);
    }

    // Scopes
    public function scopeForChurch($query, int $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }
}
