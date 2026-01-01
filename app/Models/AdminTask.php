<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminTask extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'priority',
        'status',
        'created_by',
        'assigned_to',
        'support_ticket_id',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'bug' => 'Баг',
            'feature' => 'Фіча',
            'improvement' => 'Покращення',
            'other' => 'Інше',
            default => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'bug' => 'red',
            'feature' => 'green',
            'improvement' => 'blue',
            'other' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Низький',
            'normal' => 'Нормальний',
            'high' => 'Високий',
            'critical' => 'Критичний',
            default => $this->priority,
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'normal' => 'blue',
            'high' => 'yellow',
            'critical' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'backlog' => 'Беклог',
            'todo' => 'До виконання',
            'in_progress' => 'В роботі',
            'testing' => 'Тестування',
            'done' => 'Виконано',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'backlog' => 'gray',
            'todo' => 'blue',
            'in_progress' => 'yellow',
            'testing' => 'purple',
            'done' => 'green',
            default => 'gray',
        };
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['done']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
