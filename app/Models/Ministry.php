<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ministry extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'church_id',
        'type_id',
        'name',
        'description',
        'icon',
        'color',
        'leader_id',
        'monthly_budget',
        'is_public',
        'slug',
        'public_description',
        'cover_image',
        'allow_registrations',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'is_public' => 'boolean',
        'allow_registrations' => 'boolean',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MinistryType::class, 'type_id');
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

    public function meetings(): HasMany
    {
        return $this->hasMany(MinistryMeeting::class)->orderByDesc('date');
    }

    public function upcomingMeetings(): HasMany
    {
        return $this->hasMany(MinistryMeeting::class)
            ->where('date', '>=', now()->startOfDay())
            ->where('status', '!=', 'cancelled')
            ->orderBy('date');
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

    /**
     * Check if budget is at warning level (80%+)
     */
    public function isBudgetWarning(): bool
    {
        return $this->monthly_budget > 0 && $this->budget_usage_percent >= 80;
    }

    /**
     * Check if budget is exceeded (100%+)
     */
    public function isBudgetExceeded(): bool
    {
        return $this->monthly_budget > 0 && $this->spent_this_month >= $this->monthly_budget;
    }

    /**
     * Check if expense can be added within budget
     */
    public function canAddExpense(float $amount): array
    {
        if (!$this->monthly_budget || $this->monthly_budget <= 0) {
            return ['allowed' => true, 'warning' => null];
        }

        $newTotal = $this->spent_this_month + $amount;
        $newPercentage = ($newTotal / $this->monthly_budget) * 100;

        if ($newTotal > $this->monthly_budget) {
            return [
                'allowed' => false,
                'warning' => 'exceeded',
                'message' => sprintf(
                    'Ця витрата перевищить бюджет на %.2f грн. Залишок: %.2f грн.',
                    $newTotal - $this->monthly_budget,
                    $this->remaining_budget
                ),
            ];
        }

        if ($newPercentage >= 80) {
            return [
                'allowed' => true,
                'warning' => 'high',
                'message' => sprintf(
                    'Увага! Після цієї витрати буде використано %.1f%% бюджету.',
                    $newPercentage
                ),
            ];
        }

        return ['allowed' => true, 'warning' => null];
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(MinistryJoinRequest::class);
    }

    public function pendingJoinRequests(): HasMany
    {
        return $this->joinRequests()->where('status', 'pending');
    }

    public function getPublicUrlAttribute(): string
    {
        return route('public.ministry', [$this->church->slug, $this->slug]);
    }
}
