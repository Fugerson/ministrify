<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ministry extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'name',
        'description',
        'icon',
        'color',
        'leader_id',
        'monthly_budget',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'leader_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('sort_order');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'ministry_person')
            ->withPivot('position_ids')
            ->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getSpentThisMonthAttribute(): float
    {
        return $this->expenses()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }

    public function getBudgetUsagePercentAttribute(): float
    {
        if (!$this->monthly_budget || $this->monthly_budget == 0) {
            return 0;
        }

        return min(100, round(($this->spent_this_month / $this->monthly_budget) * 100, 1));
    }

    public function getRemainingBudgetAttribute(): float
    {
        return max(0, ($this->monthly_budget ?? 0) - $this->spent_this_month);
    }
}
