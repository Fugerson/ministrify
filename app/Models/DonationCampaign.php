<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'name',
        'slug',
        'description',
        'goal_amount',
        'collected_amount',
        'start_date',
        'end_date',
        'is_active',
        'cover_image',
    ];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }

    public function getRaisedAmountAttribute(): float
    {
        // Use cached collected_amount if available, otherwise calculate
        if ($this->collected_amount > 0) {
            return (float) $this->collected_amount;
        }

        return $this->donations()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Update collected amount from donations
     */
    public function updateCollectedAmount(): void
    {
        $total = $this->donations()
            ->where('status', 'completed')
            ->sum('amount');

        $this->update(['collected_amount' => $total]);
    }

    public function getProgressPercentAttribute(): int
    {
        if (!$this->goal_amount || $this->goal_amount == 0) {
            return 0;
        }

        return min(100, (int) round(($this->raised_amount / $this->goal_amount) * 100));
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }
}
