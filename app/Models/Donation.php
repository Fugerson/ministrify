<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated Use Transaction model with source_type = 'donation' instead.
 * This model is kept for backward compatibility with existing data.
 * All new donations should use the Transaction model.
 *
 * @see \App\Models\Transaction
 */
class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'person_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'amount',
        'currency',
        'type',
        'purpose',
        'message',
        'ministry_id',
        'campaign_id',
        'status',
        'payment_method',
        'transaction_id',
        'order_id',
        'payment_id',
        'payment_data',
        'notes',
        'is_anonymous',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DonationCampaign::class, 'campaign_id');
    }

    public function getDonorDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Анонімний донор';
        }

        if ($this->person) {
            return $this->person->full_name;
        }

        return $this->donor_name ?? 'Невідомий';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', ' ') . ' ' . $this->currency;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'refunded' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Очікує',
            'completed' => 'Завершено',
            'failed' => 'Невдало',
            'refunded' => 'Повернено',
            default => $this->status,
        };
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }
}
