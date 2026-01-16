<?php

namespace App\Models;

use App\Helpers\CurrencyHelper;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    // Direction types
    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    // Source types
    public const SOURCE_TITHE = 'tithe';
    public const SOURCE_OFFERING = 'offering';
    public const SOURCE_DONATION = 'donation';
    public const SOURCE_INCOME = 'income';
    public const SOURCE_EXPENSE = 'expense';
    public const SOURCE_TRANSFER = 'transfer';

    public const SOURCE_TYPES = [
        self::SOURCE_TITHE => 'Десятина',
        self::SOURCE_OFFERING => 'Пожертва',
        self::SOURCE_DONATION => 'Донат',
        self::SOURCE_INCOME => 'Надходження',
        self::SOURCE_EXPENSE => 'Витрата',
        self::SOURCE_TRANSFER => 'Переказ',
    ];

    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Очікує',
        self::STATUS_COMPLETED => 'Завершено',
        self::STATUS_FAILED => 'Невдало',
        self::STATUS_REFUNDED => 'Повернено',
        self::STATUS_CANCELLED => 'Скасовано',
    ];

    // Payment methods
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_CARD = 'card';
    public const PAYMENT_TRANSFER = 'transfer';
    public const PAYMENT_LIQPAY = 'liqpay';
    public const PAYMENT_MONOBANK = 'monobank';

    public const PAYMENT_METHODS = [
        self::PAYMENT_CASH => 'Готівка',
        self::PAYMENT_CARD => 'Картка',
        self::PAYMENT_TRANSFER => 'Переказ',
        self::PAYMENT_LIQPAY => 'LiqPay',
        self::PAYMENT_MONOBANK => 'Monobank',
    ];

    protected $fillable = [
        'church_id',
        'direction',
        'source_type',
        'amount',
        'currency',
        'amount_uah',
        'date',
        'category_id',
        'person_id',
        'ministry_id',
        'campaign_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'is_anonymous',
        'payment_method',
        'transaction_id',
        'order_id',
        'payment_data',
        'status',
        'description',
        'notes',
        'purpose',
        'recorded_by',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_uah' => 'decimal:2',
        'date' => 'date',
        'is_anonymous' => 'boolean',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Calculate amount_uah when saving
        static::saving(function (Transaction $transaction) {
            $transaction->calculateAmountUah();
        });
    }

    /**
     * Calculate and set the amount in UAH based on currency and date.
     */
    public function calculateAmountUah(): void
    {
        $currency = $this->currency ?? 'UAH';

        if ($currency === 'UAH') {
            $this->amount_uah = $this->amount;
        } else {
            $this->amount_uah = ExchangeRate::toUah(
                (float) $this->amount,
                $currency,
                $this->date
            );
        }
    }

    // ==================
    // Relationships
    // ==================

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TransactionAttachment::class);
    }

    // ==================
    // Scopes
    // ==================

    public function scopeIncoming($query)
    {
        return $query->where('direction', self::DIRECTION_IN);
    }

    public function scopeOutgoing($query)
    {
        return $query->where('direction', self::DIRECTION_OUT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeThisMonth($query)
    {
        return $query->forMonth(now()->year, now()->month);
    }

    public function scopeThisYear($query)
    {
        return $query->forYear(now()->year);
    }

    public function scopeTithes($query)
    {
        return $query->where('source_type', self::SOURCE_TITHE);
    }

    public function scopeOfferings($query)
    {
        return $query->where('source_type', self::SOURCE_OFFERING);
    }

    public function scopeDonations($query)
    {
        return $query->where('source_type', self::SOURCE_DONATION);
    }

    public function scopeExpenses($query)
    {
        return $query->where('direction', self::DIRECTION_OUT);
    }

    // ==================
    // Accessors
    // ==================

    public function getFormattedAmountAttribute(): string
    {
        return CurrencyHelper::formatWithSign(
            (float) $this->amount,
            $this->currency ?? 'UAH',
            $this->direction
        );
    }

    public function getFormattedAmountUahAttribute(): string
    {
        if ($this->currency === 'UAH') {
            return $this->formatted_amount;
        }

        return CurrencyHelper::formatWithSign(
            (float) ($this->amount_uah ?? $this->amount),
            'UAH',
            $this->direction
        );
    }

    public function getCurrencySymbolAttribute(): string
    {
        return CurrencyHelper::symbol($this->currency ?? 'UAH');
    }

    public function getSourceTypeLabelAttribute(): string
    {
        return self::SOURCE_TYPES[$this->source_type] ?? $this->source_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_REFUNDED, self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method ?? 'Не вказано';
    }

    public function getDonorDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Анонімно';
        }

        if ($this->person) {
            return $this->person->full_name;
        }

        return $this->donor_name ?? 'Не вказано';
    }

    public function getIsIncomeAttribute(): bool
    {
        return $this->direction === self::DIRECTION_IN;
    }

    public function getIsExpenseAttribute(): bool
    {
        return $this->direction === self::DIRECTION_OUT;
    }

    // ==================
    // Methods
    // ==================

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    public function refund(): void
    {
        $this->update(['status' => self::STATUS_REFUNDED]);
    }
}
