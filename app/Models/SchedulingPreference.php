<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchedulingPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'church_id',
        'max_times_per_month',
        'preferred_times_per_month',
        'prefer_with_person_id',
        'household_preference',
        'last_blockout_request_sent_at',
        'last_blockout_response_at',
        'scheduling_notes',
    ];

    protected $casts = [
        'last_blockout_request_sent_at' => 'datetime',
        'last_blockout_response_at' => 'datetime',
    ];

    public const HOUSEHOLD_PREFERENCES = [
        'none' => 'Без преференцій',
        'together' => 'Хочу служити разом',
        'separate' => 'Не хочу служити одночасно',
    ];

    // ========== RELATIONSHIPS ==========

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function preferWithPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'prefer_with_person_id');
    }

    public function ministryPreferences(): HasMany
    {
        return $this->hasMany(MinistryPreference::class);
    }

    public function positionPreferences(): HasMany
    {
        return $this->hasMany(PositionPreference::class);
    }

    // ========== HELPERS ==========

    /**
     * Get max times per month for a specific ministry
     */
    public function getMaxForMinistry($ministryId): ?int
    {
        $pref = $this->ministryPreferences()->where('ministry_id', $ministryId)->first();
        return $pref?->max_times_per_month ?? $this->max_times_per_month;
    }

    /**
     * Get preferred times per month for a specific ministry
     */
    public function getPreferredForMinistry($ministryId): ?int
    {
        $pref = $this->ministryPreferences()->where('ministry_id', $ministryId)->first();
        return $pref?->preferred_times_per_month ?? $this->preferred_times_per_month;
    }

    /**
     * Get max times per month for a specific position
     */
    public function getMaxForPosition($positionId): ?int
    {
        $pref = $this->positionPreferences()->where('position_id', $positionId)->first();
        return $pref?->max_times_per_month ?? $this->max_times_per_month;
    }

    /**
     * Check if household preference conflicts
     */
    public function hasHouseholdConflict($eventId, $assignedPersonIds): bool
    {
        if ($this->household_preference === 'none' || !$this->prefer_with_person_id) {
            return false;
        }

        $partnerAssigned = in_array($this->prefer_with_person_id, $assignedPersonIds);

        if ($this->household_preference === 'together') {
            // Wants to serve together but partner not assigned
            return !$partnerAssigned;
        }

        if ($this->household_preference === 'separate') {
            // Doesn't want to serve at same time but partner is assigned
            return $partnerAssigned;
        }

        return false;
    }

    /**
     * Get or create preference for person
     */
    public static function getOrCreate($personId, $churchId): self
    {
        return self::firstOrCreate(
            ['person_id' => $personId, 'church_id' => $churchId],
            ['max_times_per_month' => null, 'preferred_times_per_month' => null]
        );
    }

    /**
     * Get household preference label
     */
    public function getHouseholdPreferenceLabelAttribute(): string
    {
        return self::HOUSEHOLD_PREFERENCES[$this->household_preference] ?? '';
    }
}
