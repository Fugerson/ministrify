<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardCard extends Model
{
    protected $fillable = [
        'column_id',
        'title',
        'description',
        'position',
        'priority',
        'due_date',
        'assigned_to',
        'created_by',
        'labels',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'labels' => 'array',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(BoardColumn::class, 'column_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BoardCardComment::class, 'card_id')->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BoardCardAttachment::class, 'card_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(BoardCardChecklistItem::class, 'card_id')->orderBy('position');
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->is_completed) return false;
        return $this->due_date->isPast();
    }

    public function isDueSoon(): bool
    {
        if (!$this->due_date || $this->is_completed) return false;
        return $this->due_date->isBetween(now(), now()->addDays(2));
    }

    public function getChecklistProgressAttribute(): int
    {
        $total = $this->checklistItems()->count();
        if ($total === 0) return 0;
        $completed = $this->checklistItems()->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}
