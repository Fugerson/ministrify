<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChurchBudget extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'year',
        'status',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    // ==================
    // Relationships
    // ==================

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChurchBudgetItem::class)->orderBy('sort_order');
    }

    // ==================
    // Scopes
    // ==================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    // ==================
    // Methods
    // ==================

    public function getTotalPlannedForMonth(int $month): float
    {
        return $this->items->sum(fn ($item) => $item->getPlannedForMonth($month));
    }

    public function getTotalPlannedForYear(): float
    {
        return $this->items->sum(fn ($item) => $item->getAnnualTotal());
    }

    public function getActualSpendingForMonth(int $month): float
    {
        // Sum actual spending for all budget items that have categories
        $itemsSpending = $this->items->sum(fn ($item) => $item->getActualSpendingForMonth($month, $this->year));

        // Also count uncategorized church-level expenses (no ministry, no category matching any budget item)
        $itemCategoryIds = $this->items->pluck('category_id')->filter()->toArray();
        $uncategorized = (float) (Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereNull('ministry_id')
            ->when(! empty($itemCategoryIds), fn ($q) => $q->whereNotIn('category_id', $itemCategoryIds))
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $this->year)
            ->whereMonth('date', $month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0);

        return $itemsSpending + $uncategorized;
    }

    public function getActualSpendingForYear(): float
    {
        $total = 0;
        for ($m = 1; $m <= 12; $m++) {
            $total += $this->getActualSpendingForMonth($m);
        }

        return $total;
    }

    public static function getOrCreate(int $churchId, int $year): self
    {
        $existing = static::where('church_id', $churchId)->where('year', $year)->first();
        if ($existing) {
            return $existing;
        }

        $budget = new static([
            'name' => __('app.budget').' '.$year,
            'status' => 'active',
            'year' => $year,
        ]);
        $budget->church_id = $churchId;
        $budget->save();

        return $budget;
    }
}
