<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinistryTask extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'goal_id',
        'title',
        'description',
        'assigned_to',
        'due_date',
        'status',
        'priority',
        'sort_order',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public const STATUSES = [
        'todo' => 'До виконання',
        'in_progress' => 'В процесі',
        'done' => 'Виконано',
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

    public function goal(): BelongsTo
    {
        return $this->belongsTo(MinistryGoal::class, 'goal_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
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

    public function getIsDoneAttribute(): bool
    {
        return $this->status === 'done';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    // Methods
    public function markAsDone(): void
    {
        $this->update([
            'status' => 'done',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        // Update goal progress
        if ($this->goal) {
            $this->goal->updateProgressFromTasks();
        }
    }

    public function markAsTodo(): void
    {
        $this->update([
            'status' => 'todo',
            'completed_at' => null,
            'completed_by' => null,
        ]);

        // Update goal progress
        if ($this->goal) {
            $this->goal->updateProgressFromTasks();
        }
    }

    public function toggle(): void
    {
        if ($this->status === 'done') {
            $this->markAsTodo();
        } else {
            $this->markAsDone();
        }
    }

    // Scopes
    public function scopeForChurch($query, int $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    public function scopeTodo($query)
    {
        return $query->where('status', 'todo');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeNotDone($query)
    {
        return $query->where('status', '!=', 'done');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }
}
