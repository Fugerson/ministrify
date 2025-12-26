<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class BlockoutDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'church_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'all_day',
        'reason',
        'reason_note',
        'applies_to_all',
        'recurrence',
        'recurrence_config',
        'recurrence_end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'all_day' => 'boolean',
        'applies_to_all' => 'boolean',
        'recurrence_config' => 'array',
        'recurrence_end_date' => 'date',
    ];

    // Reason options with labels
    public const REASONS = [
        'vacation' => 'Відпустка',
        'travel' => 'Відрядження',
        'sick' => 'Хвороба',
        'family' => 'Сімейні обставини',
        'work' => 'Робота',
        'other' => 'Інше',
    ];

    // Recurrence options
    public const RECURRENCE_OPTIONS = [
        'none' => 'Одноразово',
        'weekly' => 'Щотижня',
        'biweekly' => 'Раз на 2 тижні',
        'monthly' => 'Щомісяця',
        'custom' => 'Власний',
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

    public function ministries(): BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'blockout_date_ministry');
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForPerson($query, $personId)
    {
        return $query->where('person_id', $personId);
    }

    public function scopeForChurch($query, $churchId)
    {
        return $query->where('church_id', $churchId);
    }

    /**
     * Get blockouts that overlap with a given date range
     */
    public function scopeOverlapping($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where(function ($inner) use ($startDate, $endDate) {
                // Blockout starts before end and ends after start
                $inner->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            });
        });
    }

    /**
     * Get blockouts for a specific date
     */
    public function scopeForDate($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return $query->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }

    /**
     * Get blockouts for a specific ministry
     */
    public function scopeForMinistry($query, $ministryId)
    {
        return $query->where(function ($q) use ($ministryId) {
            $q->where('applies_to_all', true)
              ->orWhereHas('ministries', fn($m) => $m->where('ministries.id', $ministryId));
        });
    }

    /**
     * Check if date range overlaps with upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now()->format('Y-m-d'));
    }

    // ========== HELPERS ==========

    /**
     * Check if a specific date/time falls within this blockout
     */
    public function coversDateTime($date, $time = null): bool
    {
        $checkDate = Carbon::parse($date);

        // Check if date is within range
        if ($checkDate->lt($this->start_date) || $checkDate->gt($this->end_date)) {
            return false;
        }

        // Handle recurring blockouts
        if ($this->recurrence !== 'none') {
            if (!$this->matchesRecurrencePattern($checkDate)) {
                return false;
            }
        }

        // If all day, no need to check time
        if ($this->all_day || !$time) {
            return true;
        }

        // Check time overlap
        $checkTime = Carbon::parse($time);
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $checkTime->gte($startTime) && $checkTime->lte($endTime);
    }

    /**
     * Check if date matches recurrence pattern
     */
    protected function matchesRecurrencePattern(Carbon $date): bool
    {
        if ($this->recurrence_end_date && $date->gt($this->recurrence_end_date)) {
            return false;
        }

        switch ($this->recurrence) {
            case 'weekly':
                return $date->dayOfWeek === $this->start_date->dayOfWeek;

            case 'biweekly':
                $weeksDiff = $this->start_date->diffInWeeks($date);
                return $weeksDiff % 2 === 0 && $date->dayOfWeek === $this->start_date->dayOfWeek;

            case 'monthly':
                return $date->day === $this->start_date->day;

            case 'custom':
                // Custom patterns stored in recurrence_config
                return $this->matchesCustomPattern($date);

            default:
                return true;
        }
    }

    /**
     * Match custom recurrence patterns
     */
    protected function matchesCustomPattern(Carbon $date): bool
    {
        $config = $this->recurrence_config ?? [];

        // Example: "second Tuesday of each month"
        if (isset($config['week_of_month']) && isset($config['day_of_week'])) {
            $weekOfMonth = $config['week_of_month']; // 1, 2, 3, 4, or 'last'
            $dayOfWeek = $config['day_of_week']; // 0-6

            if ($date->dayOfWeek !== $dayOfWeek) {
                return false;
            }

            if ($weekOfMonth === 'last') {
                $lastOccurrence = $date->copy()->endOfMonth();
                while ($lastOccurrence->dayOfWeek !== $dayOfWeek) {
                    $lastOccurrence->subDay();
                }
                return $date->day === $lastOccurrence->day;
            }

            $weekOfMonthActual = ceil($date->day / 7);
            return $weekOfMonthActual === $weekOfMonth;
        }

        // Example: specific days of week
        if (isset($config['days_of_week'])) {
            return in_array($date->dayOfWeek, $config['days_of_week']);
        }

        return true;
    }

    /**
     * Get reason label
     */
    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute(): string
    {
        if ($this->start_date->eq($this->end_date)) {
            return $this->start_date->format('d.m.Y');
        }
        return $this->start_date->format('d.m') . ' — ' . $this->end_date->format('d.m.Y');
    }

    /**
     * Get time range or "Весь день"
     */
    public function getTimeRangeAttribute(): string
    {
        if ($this->all_day) {
            return 'Весь день';
        }
        return Carbon::parse($this->start_time)->format('H:i') . ' — ' . Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Check if applies to a specific ministry
     */
    public function appliesToMinistry($ministryId): bool
    {
        if ($this->applies_to_all) {
            return true;
        }
        return $this->ministries()->where('ministries.id', $ministryId)->exists();
    }

    /**
     * Get all dates in the blockout range (for calendar display)
     */
    public function getAllDates(): array
    {
        $dates = [];
        $current = $this->start_date->copy();

        while ($current->lte($this->end_date)) {
            if ($this->recurrence === 'none' || $this->matchesRecurrencePattern($current)) {
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Auto-expire old blockouts
     */
    public static function expireOld(): int
    {
        return self::where('status', 'active')
            ->where('end_date', '<', now()->format('Y-m-d'))
            ->where(function ($q) {
                $q->where('recurrence', 'none')
                  ->orWhere(function ($inner) {
                      $inner->whereNotNull('recurrence_end_date')
                            ->where('recurrence_end_date', '<', now()->format('Y-m-d'));
                  });
            })
            ->update(['status' => 'expired']);
    }
}
