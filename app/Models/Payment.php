<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_REVERSED = 'reversed';

    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_ONE_TIME = 'one_time';

    protected $fillable = [
        'church_id',
        'subscription_plan_id',
        'order_id',
        'liqpay_order_id',
        'liqpay_payment_id',
        'amount',
        'currency',
        'description',
        'status',
        'type',
        'period',
        'liqpay_data',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'liqpay_data' => 'array',
        'paid_at' => 'datetime',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this->status === self::STATUS_FAILURE;
    }

    public function getAmountUahAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_uah, 2, ',', ' ') . ' ' . $this->currency;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Очікує',
            self::STATUS_SUCCESS => 'Оплачено',
            self::STATUS_FAILURE => 'Помилка',
            self::STATUS_REVERSED => 'Повернено',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_SUCCESS => 'green',
            self::STATUS_FAILURE => 'red',
            self::STATUS_REVERSED => 'gray',
            default => 'gray',
        };
    }

    public static function generateOrderId(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
