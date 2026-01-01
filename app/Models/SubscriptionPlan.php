<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_monthly',
        'price_yearly',
        'max_people',
        'max_ministries',
        'max_events_per_month',
        'max_users',
        'has_telegram_bot',
        'has_finances',
        'has_forms',
        'has_website_builder',
        'has_custom_domain',
        'has_api_access',
        'has_boards',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'integer',
        'price_yearly' => 'integer',
        'max_people' => 'integer',
        'max_ministries' => 'integer',
        'max_events_per_month' => 'integer',
        'max_users' => 'integer',
        'has_telegram_bot' => 'boolean',
        'has_finances' => 'boolean',
        'has_forms' => 'boolean',
        'has_website_builder' => 'boolean',
        'has_custom_domain' => 'boolean',
        'has_api_access' => 'boolean',
        'has_boards' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function churches(): HasMany
    {
        return $this->hasMany(Church::class, 'subscription_plan_id');
    }

    public function isFree(): bool
    {
        return $this->price_monthly === 0;
    }

    public function getPriceMonthlyUahAttribute(): float
    {
        return $this->price_monthly / 100;
    }

    public function getPriceYearlyUahAttribute(): float
    {
        return $this->price_yearly / 100;
    }

    public function getFormattedPriceMonthlyAttribute(): string
    {
        if ($this->isFree()) {
            return 'Безкоштовно';
        }
        return number_format($this->price_monthly_uah, 0, ',', ' ') . ' ₴/міс';
    }

    public function getFormattedPriceYearlyAttribute(): string
    {
        if ($this->isFree()) {
            return 'Безкоштовно';
        }
        return number_format($this->price_yearly_uah, 0, ',', ' ') . ' ₴/рік';
    }

    public function getYearlySavingsPercentAttribute(): int
    {
        if ($this->price_monthly === 0) {
            return 0;
        }
        $yearlyIfMonthly = $this->price_monthly * 12;
        return (int) round((1 - $this->price_yearly / $yearlyIfMonthly) * 100);
    }

    public function hasLimit(string $feature): bool
    {
        $limit = $this->{"max_{$feature}"} ?? 0;
        return $limit > 0;
    }

    public function getLimit(string $feature): int
    {
        return $this->{"max_{$feature}"} ?? 0;
    }

    public function hasFeature(string $feature): bool
    {
        return $this->{"has_{$feature}"} ?? false;
    }

    public static function free(): ?self
    {
        return static::where('slug', 'free')->first();
    }

    public static function getActive()
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }
}
