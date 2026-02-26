<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetItem extends Model
{
    protected $fillable = [
        'church_id',
        'ministry_budget_id',
        'category_id',
        'name',
        'planned_amount',
        'planned_date',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'planned_date' => 'date',
        'sort_order' => 'integer',
    ];

    // ==================
    // Relationships
    // ==================

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function ministryBudget(): BelongsTo
    {
        return $this->belongsTo(MinistryBudget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    public function responsiblePeople(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'budget_item_person')->withTimestamps();
    }

    public function directTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'budget_item_id');
    }

    // ==================
    // Methods
    // ==================

    /**
     * Get actual spending: direct transactions + auto-matched by category
     */
    public function getActualSpending(?float $autoMatchedAmount = null): float
    {
        // Direct transactions (explicitly linked to this budget item)
        $direct = (float) Transaction::where('budget_item_id', $this->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->completed()
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0;

        // Auto-matched by category (if category_id set)
        $autoMatched = 0;
        if ($this->category_id && $autoMatchedAmount === null) {
            $budget = $this->ministryBudget;
            $autoMatched = (float) Transaction::where('ministry_id', $budget->ministry_id)
                ->where('church_id', $this->church_id)
                ->where('category_id', $this->category_id)
                ->whereNull('budget_item_id')
                ->where('direction', Transaction::DIRECTION_OUT)
                ->completed()
                ->whereYear('date', $budget->year)
                ->whereMonth('date', $budget->month)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0;
        } elseif ($autoMatchedAmount !== null) {
            $autoMatched = $autoMatchedAmount;
        }

        return $direct + $autoMatched;
    }

    /**
     * Get all matched transactions (direct + auto-matched)
     */
    public function getMatchedTransactions()
    {
        $budget = $this->ministryBudget;

        $query = Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->completed()
            ->whereYear('date', $budget->year)
            ->whereMonth('date', $budget->month)
            ->where(function ($q) use ($budget) {
                // Direct: explicitly linked
                $q->where('budget_item_id', $this->id);

                // Auto-matched: same ministry + category, not linked to any budget item
                if ($this->category_id) {
                    $q->orWhere(function ($q2) use ($budget) {
                        $q2->where('ministry_id', $budget->ministry_id)
                            ->where('category_id', $this->category_id)
                            ->whereNull('budget_item_id');
                    });
                }
            })
            ->with('attachments')
            ->orderBy('date')
            ->get();

        return $query;
    }
}
