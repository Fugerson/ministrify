<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'title',
        'date',
        'time',
        'notes',
        'recurrence_rule',
        'parent_event_id',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    public function childEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }

    public function checklist(): HasOne
    {
        return $this->hasOne(EventChecklist::class);
    }

    public function getFilledPositionsCountAttribute(): int
    {
        return $this->assignments()->count();
    }

    public function getTotalPositionsCountAttribute(): int
    {
        return $this->ministry->positions()->count();
    }

    public function getUnfilledPositionsAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        $filledPositionIds = $this->assignments()->pluck('position_id')->toArray();

        return $this->ministry->positions()
            ->whereNotIn('id', $filledPositionIds)
            ->get();
    }

    public function getConfirmedAssignmentsCountAttribute(): int
    {
        return $this->assignments()->where('status', 'confirmed')->count();
    }

    public function getPendingAssignmentsCountAttribute(): int
    {
        return $this->assignments()->where('status', 'pending')->count();
    }

    public function isFullyStaffed(): bool
    {
        return $this->unfilled_positions->isEmpty();
    }

    public function getDateTimeAttribute(): \DateTime
    {
        return \DateTime::createFromFormat(
            'Y-m-d H:i',
            $this->date->format('Y-m-d') . ' ' . $this->time->format('H:i')
        );
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->orderBy('time');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeForWeek($query, $startOfWeek)
    {
        return $query->whereBetween('date', [
            $startOfWeek,
            $startOfWeek->copy()->endOfWeek()
        ]);
    }
}
