<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'position_id',
        'person_id',
        'status',
        'notified_at',
        'responded_at',
        'notes',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'responded_at' => now(),
        ]);
    }

    public function decline(): void
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);
    }

    public function markAsNotified(): void
    {
        $this->update(['notified_at' => now()]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeNotNotified($query)
    {
        return $query->whereNull('notified_at');
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'pending' => '&#9203;', // hourglass
            'confirmed' => '&#9989;', // check
            'declined' => '&#10060;', // cross
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Очікує підтвердження',
            'confirmed' => 'Підтверджено',
            'declined' => 'Відхилено',
        };
    }
}
