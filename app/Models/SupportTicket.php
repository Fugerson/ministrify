<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'church_id',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to',
        'last_reply_at',
        'resolved_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(SupportMessage::class, 'ticket_id')->latestOfMany();
    }

    public function unreadMessagesForUser(): int
    {
        return $this->messages()
            ->where('is_from_admin', true)
            ->where('is_internal', false)
            ->whereNull('read_at')
            ->count();
    }

    public function unreadMessagesForAdmin(): int
    {
        return $this->messages()
            ->where('is_from_admin', false)
            ->whereNull('read_at')
            ->count();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'bug' => 'Помилка',
            'question' => 'Питання',
            'feature' => 'Пропозиція',
            'other' => 'Інше',
            default => $this->category,
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'bug' => 'red',
            'question' => 'blue',
            'feature' => 'purple',
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
            'urgent' => 'Терміновий',
            default => $this->priority,
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'normal' => 'blue',
            'high' => 'yellow',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Відкритий',
            'in_progress' => 'В роботі',
            'waiting' => 'Очікує відповіді',
            'resolved' => 'Вирішено',
            'closed' => 'Закритий',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'waiting' => 'purple',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }
}
