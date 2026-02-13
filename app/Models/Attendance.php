<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendance extends Model
{
    use HasFactory, Auditable;

    // Attendance types
    public const TYPE_SERVICE = 'service';
    public const TYPE_GROUP = 'group';
    public const TYPE_EVENT = 'event';
    public const TYPE_MEETING = 'meeting';

    public const TYPES = [
        self::TYPE_SERVICE => 'Богослужіння',
        self::TYPE_GROUP => 'Мала група',
        self::TYPE_EVENT => 'Подія',
        self::TYPE_MEETING => 'Зустріч',
    ];

    protected $fillable = [
        'church_id',
        'attendable_type',
        'attendable_id',
        'type',
        'event_id', // @deprecated - Use attendable_type/attendable_id polymorphic relation instead
        'date',
        'time',
        'location',
        'total_count',
        'members_present',
        'guests_count',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    // ==================
    // Relationships
    // ==================

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Polymorphic relation to attendable entity (Event, Group, Meeting, etc.)
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @deprecated Use attendable() polymorphic relation instead.
     * Legacy relation to event (for backward compatibility only).
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the group if this is a group attendance
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'attendable_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ==================
    // Scopes
    // ==================

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeThisMonth($query)
    {
        return $query->forMonth(now()->year, now()->month);
    }

    public function scopeServices($query)
    {
        return $query->where('type', self::TYPE_SERVICE);
    }

    public function scopeGroups($query)
    {
        return $query->where('type', self::TYPE_GROUP);
    }

    public function scopeEvents($query)
    {
        return $query->where('type', self::TYPE_EVENT);
    }

    public function scopeMeetings($query)
    {
        return $query->where('type', self::TYPE_MEETING);
    }

    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('attendable_type', $type)
            ->where('attendable_id', $id);
    }

    // ==================
    // Accessors
    // ==================

    public function getPresentCountAttribute(): int
    {
        return $this->records()->where('present', true)->count();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getAttendanceRateAttribute(): float
    {
        if (!$this->attendable || $this->type !== self::TYPE_GROUP) {
            return 0;
        }

        $totalMembers = $this->attendable->members()->count();
        if ($totalMembers === 0) {
            return 0;
        }

        return round(($this->members_present / $totalMembers) * 100, 1);
    }

    public function getEntityNameAttribute(): string
    {
        if ($this->attendable) {
            return match ($this->type) {
                self::TYPE_GROUP => $this->attendable->name ?? 'Група',
                self::TYPE_EVENT => $this->attendable->title ?? 'Подія',
                self::TYPE_MEETING => $this->attendable->title ?? 'Зустріч',
                default => 'Богослужіння',
            };
        }

        return $this->event?->title ?? 'Богослужіння';
    }

    // ==================
    // Methods
    // ==================

    /**
     * Mark a person as present
     */
    public function markPresent(Person $person, ?string $notes = null): AttendanceRecord
    {
        return $this->records()->updateOrCreate(
            ['person_id' => $person->id],
            [
                'present' => true,
                'checked_in_at' => now()->format('H:i'),
                'notes' => $notes,
            ]
        );
    }

    /**
     * Mark a person as absent
     */
    public function markAbsent(Person $person): AttendanceRecord
    {
        return $this->records()->updateOrCreate(
            ['person_id' => $person->id],
            ['present' => false]
        );
    }

    /**
     * Recalculate counts based on records
     */
    public function recalculateCounts(): void
    {
        $this->update([
            'members_present' => $this->records()->where('present', true)->count(),
            'total_count' => $this->records()->where('present', true)->count() + ($this->guests_count ?? 0),
        ]);
    }

    /**
     * Get people who were present
     */
    public function presentPeople()
    {
        return Person::whereIn('id', $this->records()->where('present', true)->pluck('person_id'));
    }

    /**
     * Get people who were absent
     */
    public function absentPeople()
    {
        return Person::whereIn('id', $this->records()->where('present', false)->pluck('person_id'));
    }
}
