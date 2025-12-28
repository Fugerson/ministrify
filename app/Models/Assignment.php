<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DECLINED = 'declined';
    const STATUS_ATTENDED = 'attended';

    const STATUSES = [
        self::STATUS_PENDING => 'Очікує підтвердження',
        self::STATUS_CONFIRMED => 'Підтверджено',
        self::STATUS_DECLINED => 'Відхилено',
        self::STATUS_ATTENDED => 'Був присутній',
    ];

    // Valid status transitions
    const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_DECLINED],
        self::STATUS_CONFIRMED => [self::STATUS_ATTENDED, self::STATUS_DECLINED],
        self::STATUS_DECLINED => [self::STATUS_PENDING], // Can re-assign
        self::STATUS_ATTENDED => [], // Final state
    ];

    protected $fillable = [
        'event_id',
        'position_id',
        'person_id',
        'status',
        'notified_at',
        'responded_at',
        'notes',
        'email_sent_at',
        'email_opened_at',
        'blockout_override',
        'preference_override',
        'conflict_override',
        'decline_reason',
        'assignment_notes',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'responded_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'email_opened_at' => 'datetime',
        'blockout_override' => 'boolean',
        'preference_override' => 'boolean',
        'conflict_override' => 'boolean',
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

    public function schedulingConflicts(): HasMany
    {
        return $this->hasMany(SchedulingConflict::class);
    }

    /**
     * Check if this assignment has any overridden conflicts
     */
    public function hasOverriddenConflicts(): bool
    {
        return $this->blockout_override || $this->preference_override || $this->conflict_override;
    }

    /**
     * Get conflict warning message if any
     */
    public function getConflictWarningAttribute(): ?string
    {
        if ($this->blockout_override) {
            $conflict = $this->schedulingConflicts()->where('conflict_type', 'blockout')->first();
            return $conflict ? "Blockout: {$conflict->conflict_details}" : "Призначено під час blockout";
        }
        return null;
    }

    // Status checks
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

    public function isAttended(): bool
    {
        return $this->status === self::STATUS_ATTENDED;
    }

    /**
     * Check if transition to new status is allowed
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = self::TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    /**
     * Transition to new status with validation
     */
    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        // Additional validation based on event date
        if ($newStatus === self::STATUS_CONFIRMED && $this->event->date->isPast()) {
            return false; // Can't confirm past event
        }

        $this->update([
            'status' => $newStatus,
            'responded_at' => in_array($newStatus, [self::STATUS_CONFIRMED, self::STATUS_DECLINED]) ? now() : $this->responded_at,
        ]);

        return true;
    }

    public function confirm(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_CONFIRMED)) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'responded_at' => now(),
        ]);

        return true;
    }

    public function decline(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_DECLINED)) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        return true;
    }

    public function markAsAttended(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_ATTENDED)) {
            return false;
        }

        $this->update(['status' => self::STATUS_ATTENDED]);
        return true;
    }

    public function markAsNotified(): void
    {
        $this->update(['notified_at' => now()]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }

    public function scopeNotNotified($query)
    {
        return $query->whereNull('notified_at');
    }

    public function scopeForUpcomingEvents($query)
    {
        return $query->whereHas('event', fn($q) => $q->where('date', '>=', now()->startOfDay()));
    }

    // Attributes
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '⏳',
            self::STATUS_CONFIRMED => '✅',
            self::STATUS_DECLINED => '❌',
            self::STATUS_ATTENDED => '🎯',
            default => '❓',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_DECLINED => 'red',
            self::STATUS_ATTENDED => 'blue',
            default => 'gray',
        };
    }
}
