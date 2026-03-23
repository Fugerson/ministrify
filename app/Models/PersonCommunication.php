<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonCommunication extends Model
{
    use Auditable;

    protected $fillable = [
        'person_id',
        'user_id',
        'type',
        'direction',
        'content',
        'communicated_at',
    ];

    protected $casts = [
        'communicated_at' => 'datetime',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'telegram' => '📱',
            'phone' => '📞',
            'meeting' => '🤝',
            'note' => '📝',
            default => '💬',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'telegram' => 'Telegram',
            'phone' => 'Дзвінок',
            'meeting' => 'Зустріч',
            'note' => 'Нотатка',
            default => 'Інше',
        };
    }
}
