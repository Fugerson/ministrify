<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'name',
        'icon',
        'color',
        'is_tithe',
        'is_offering',
        'is_donation',
        'sort_order',
    ];

    protected $casts = [
        'is_tithe' => 'boolean',
        'is_offering' => 'boolean',
        'is_donation' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'category_id');
    }

    public function getTotalForMonthAttribute(): float
    {
        return $this->incomes()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }
}
