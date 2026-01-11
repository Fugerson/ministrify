<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventResponsibility extends Model
{
    const STATUS_OPEN = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'event_id',
        'person_id',
        'name',
        'notes',
        'status',
        'notified_at',
        'responded_at',
        'reminded_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'responded_at' => 'datetime',
        'reminded_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function confirm(): bool
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'responded_at' => now(),
        ]);
        return true;
    }

    public function decline(): bool
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'responded_at' => now(),
        ]);
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'Відкрито',
            self::STATUS_PENDING => 'Очікує',
            self::STATUS_CONFIRMED => 'Підтверджено',
            self::STATUS_DECLINED => 'Відхилено',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'gray',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_DECLINED => 'red',
            default => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => '⚪',
            self::STATUS_PENDING => '⏳',
            self::STATUS_CONFIRMED => '✅',
            self::STATUS_DECLINED => '❌',
            default => '❓',
        };
    }
}
