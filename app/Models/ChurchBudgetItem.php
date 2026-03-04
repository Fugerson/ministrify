<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchBudgetItem extends Model
{
    use Auditable;

    protected $fillable = [
        'church_id',
        'church_budget_id',
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

    public function getActualSpendingForMonth(int $month): float
    {
        if (!$this->category_id) {
            return 0;
        }

        return (float) (Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('category_id', $this->category_id)
            ->whereNull('ministry_id')
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $this->churchBudget->year)
            ->whereMonth('date', $month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0);
    }

    public function getMatchedTransactions(int $month)
    {
        if (!$this->category_id) {
            return collect();
        }

        return Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->where('category_id', $this->category_id)
            ->whereNull('ministry_id')
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $this->churchBudget->year)
            ->whereMonth('date', $month)
            ->with(['category', 'attachments'])
            ->orderBy('date', 'desc')
            ->get();
    }
}
