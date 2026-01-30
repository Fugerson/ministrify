<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class ExpenseCategory extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'church_id',
        'name',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function getTotalThisMonthAttribute(): float
    {
        return $this->expenses()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }
}
