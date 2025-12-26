<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated Use Transaction model with direction = 'in' instead.
 * This model is kept for backward compatibility with existing data.
 * All new income records should use the Transaction model.
 *
 * @see \App\Models\Transaction
 */
class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'category_id',
        'person_id',
        'user_id',
        'amount',
        'date',
        'description',
        'payment_method',
        'is_anonymous',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_anonymous' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeTithes($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('is_tithe', true));
    }

    public function scopeOfferings($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('is_offering', true));
    }

    public function scopeDonations($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('is_donation', true));
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Готівка',
            'card' => 'Картка',
            'transfer' => 'Переказ',
            'online' => 'Онлайн',
            default => $this->payment_method,
        };
    }

    public function getDonorNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Анонімно';
        }
        return $this->person?->full_name ?? 'Не вказано';
    }
}
