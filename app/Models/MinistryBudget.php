<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinistryBudget extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'monthly_budget',
        'year',
        'month',
        'notes',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
    ];

    // ==================
    // Relationships
    // ==================

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    // ==================
    // Scopes
    // ==================

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeThisMonth($query)
    {
        return $query->forMonth(now()->year, now()->month);
    }

    // ==================
    // Accessors
    // ==================

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Січень', 2 => 'Лютий', 3 => 'Березень',
            4 => 'Квітень', 5 => 'Травень', 6 => 'Червень',
            7 => 'Липень', 8 => 'Серпень', 9 => 'Вересень',
            10 => 'Жовтень', 11 => 'Листопад', 12 => 'Грудень',
        ];

        return $months[$this->month] ?? '';
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }

    // ==================
    // Methods
    // ==================

    /**
     * Get actual spending for this budget period
     */
    public function getActualSpending(): float
    {
        return Transaction::where('church_id', $this->church_id)
            ->where('ministry_id', $this->ministry_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->sum('amount');
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(): float
    {
        return (float) $this->monthly_budget - $this->getActualSpending();
    }

    /**
     * Get spending percentage
     */
    public function getSpendingPercentage(): float
    {
        if ($this->monthly_budget <= 0) {
            return 0;
        }

        return min(100, ($this->getActualSpending() / $this->monthly_budget) * 100);
    }

    /**
     * Check if budget is exceeded
     */
    public function isExceeded(): bool
    {
        return $this->getActualSpending() > $this->monthly_budget;
    }

    /**
     * Get or create budget for a ministry/period
     */
    public static function getOrCreate(int $churchId, int $ministryId, int $year, int $month): self
    {
        return static::firstOrCreate(
            [
                'church_id' => $churchId,
                'ministry_id' => $ministryId,
                'year' => $year,
                'month' => $month,
            ],
            ['monthly_budget' => 0]
        );
    }
}
