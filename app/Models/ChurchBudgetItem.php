<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchBudgetItem extends Model
{
    use Auditable;

    protected $fillable = [
        'category_id',
        'name',
        'is_recurring',
        'amounts',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'amounts' => 'array',
        'is_recurring' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==================
    // Relationships
    // ==================

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function churchBudget(): BelongsTo
    {
        return $this->belongsTo(ChurchBudget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    // ==================
    // Accessors
    // ==================

    public function getPlannedForMonth(int $month): float
    {
        $amounts = $this->amounts ?? [];

        return (float) ($amounts[(string) $month] ?? 0);
    }

    public function getAnnualTotal(): float
    {
        return (float) array_sum($this->amounts ?? []);
    }

    // ==================
    // Methods
    // ==================

    public function getActualSpendingForMonth(int $month, ?int $year = null): float
    {
        if (! $this->category_id) {
            return 0;
        }

        $year = $year ?? $this->churchBudget->year;

        // Match by category regardless of ministry — church budget tracks all expenses in this category
        return (float) (Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('category_id', $this->category_id)
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0);
    }

    public function getMatchedTransactions(int $month, ?int $year = null)
    {
        if (! $this->category_id) {
            return collect();
        }

        $year = $year ?? $this->churchBudget->year;

        // Match by category regardless of ministry — same logic as getActualSpendingForMonth
        return Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('category_id', $this->category_id)
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with(['category', 'attachments'])
            ->orderBy('date', 'desc')
            ->get();
    }
}
