<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinistryBudget extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'church_id',
        'ministry_id',
        'monthly_budget',
        'allocated_budget',
        'year',
        'month',
        'notes',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'allocated_budget' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class)->orderBy('sort_order');
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
            1 => __('app.month_january'), 2 => __('app.month_february'), 3 => __('app.month_march'),
            4 => __('app.month_april'), 5 => __('app.month_may'), 6 => __('app.month_june'),
            7 => __('app.month_july'), 8 => __('app.month_august'), 9 => __('app.month_september'),
            10 => __('app.month_october'), 11 => __('app.month_november'), 12 => __('app.month_december'),
        ];

        return $months[$this->month] ?? '';
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->month_name.' '.$this->year;
    }

    // ==================
    // Methods
    // ==================

    /**
     * Get effective budget: SUM of items if any, otherwise monthly_budget
     */
    public function getEffectiveBudget(): float
    {
        if ($this->items->isNotEmpty()) {
            return (float) $this->items->sum(fn ($item) => $item->getMonthlyPlanned());
        }

        return (float) $this->monthly_budget;
    }

    /**
     * Get actual spending for this budget period
     */
    public function getActualSpending(): float
    {
        return (float) (Transaction::where('church_id', $this->church_id)
            ->where('ministry_id', $this->ministry_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('source_type', '!=', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0);
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(): float
    {
        return (float) $this->getEffectiveBudget() - $this->getActualSpending();
    }

    /**
     * Get spending percentage
     */
    public function getSpendingPercentage(): float
    {
        $budget = $this->getEffectiveBudget();
        if ($budget <= 0) {
            return 0;
        }

        return min(100, ($this->getActualSpending() / $budget) * 100);
    }

    /**
     * Check if budget is exceeded
     */
    public function isExceeded(): bool
    {
        return $this->getActualSpending() > $this->getEffectiveBudget();
    }

    /**
     * Get total allocated from real allocation transactions (IN direction)
     */
    public function getTotalAllocated(): float
    {
        return (float) (Transaction::where('church_id', $this->church_id)
            ->where('ministry_id', $this->ministry_id)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('source_type', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0);
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
