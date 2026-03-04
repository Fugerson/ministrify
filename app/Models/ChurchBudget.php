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
        'church_id',
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
        return (float) (Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereNull('ministry_id')
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $this->year)
            ->whereMonth('date', $month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0);
    }

    public function getActualSpendingForYear(): float
    {
        return (float) (Transaction::where('church_id', $this->church_id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereNull('ministry_id')
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->whereYear('date', $this->year)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0);
    }

    public static function getOrCreate(int $churchId, int $year): self
    {
        return static::firstOrCreate(
            ['church_id' => $churchId, 'year' => $year],
            ['name' => __('app.budget') . ' ' . $year, 'status' => 'active']
        );
    }
}
