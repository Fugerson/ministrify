<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class EventRegistration extends Model
{
    use HasFactory, Auditable;

    protected $hidden = [
        'confirmation_token',
    ];

    protected $fillable = [
        'event_id',
        'church_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'guests',
        'notes',
        'status',
        'confirmation_token',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            $registration->confirmation_token = Str::random(32);
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalGuestsAttribute(): int
    {
        return 1 + $this->guests;
    }

    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function markAttended(): void
    {
        $this->update(['status' => 'attended']);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'cancelled' => 'red',
            'attended' => 'green',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Очікує',
            'confirmed' => 'Підтверджено',
            'cancelled' => 'Скасовано',
            'attended' => 'Відвідав',
            default => $this->status,
        };
    }
}
