<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class OnlineDonation extends Model
{
    use Auditable, HasFactory;

    protected $hidden = [
        'provider_response',
        'error_message',
        'donor_email',
        'donor_phone',
        'provider_payment_id',
        'provider_order_id',
    ];

    protected $fillable = [
        'church_id',
        'person_id',
        'transaction_id',
        'provider',
        'provider_order_id',
        'provider_payment_id',
        'amount',
        'currency',
        'status',
        'donor_name',
        'donor_email',
        'donor_phone',
        'is_anonymous',
        'is_recurring',
        'recurring_id',
        'description',
        'provider_response',
        'error_message',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'provider_response' => 'array',
        'paid_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_SUCCESS = 'success';

    const STATUS_FAILED = 'failed';

    const STATUS_REFUNDED = 'refunded';

    const PROVIDER_LIQPAY = 'liqpay';

    const PROVIDER_MONOBANK = 'monobank';

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Очікує',
            self::STATUS_PROCESSING => 'Обробка',
            self::STATUS_SUCCESS => 'Успішно',
            self::STATUS_FAILED => 'Помилка',
            self::STATUS_REFUNDED => 'Повернено',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_SUCCESS => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_REFUNDED => 'gray',
            default => 'gray',
        };
    }

    public function getProviderLabelAttribute(): string
    {
        return match ($this->provider) {
            self::PROVIDER_LIQPAY => 'LiqPay',
            self::PROVIDER_MONOBANK => 'Monobank',
            default => $this->provider,
        };
    }

    public function getDonorDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Анонімно';
        }

        return $this->donor_name ?? $this->person?->full_name ?? 'Невідомо';
    }

    public function markAsSuccess(array $providerResponse = []): void
    {
        DB::transaction(function () use ($providerResponse) {
            $donation = self::lockForUpdate()->find($this->id);
            if ($donation->transaction_id) {
                return; // already processed
            }

            $donation->update([
                'status' => self::STATUS_SUCCESS,
                'paid_at' => now(),
                'provider_response' => array_merge($donation->provider_response ?? [], $providerResponse),
            ]);

            $donation->createTransaction();

            // Refresh current instance
            $this->refresh();
        });
    }

    public function markAsFailed(string $errorMessage, array $providerResponse = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'provider_response' => array_merge($this->provider_response ?? [], $providerResponse),
        ]);
    }

    protected function createTransaction(): void
    {
        if ($this->transaction_id) {
            return;
        }

        $church = $this->church;

        // Find or create online donation category
        $category = TransactionCategory::firstOrCreate(
            ['church_id' => $church->id, 'name' => 'Онлайн пожертви'],
            [
                'type' => 'income',
                'icon' => '💳',
                'color' => '#22c55e',
                'is_donation' => true,
            ]
        );

        $transaction = Transaction::create([
            'church_id' => $church->id,
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_DONATION,
            'amount' => $this->amount,
            'date' => $this->paid_at ?? now(),
            'category_id' => $category->id,
            'person_id' => $this->is_anonymous ? null : $this->person_id,
            'is_anonymous' => $this->is_anonymous,
            'currency' => $this->currency ?? 'UAH',
            'payment_method' => $this->provider,
            'description' => 'Онлайн пожертва через '.$this->provider_label,
            'notes' => $this->description,
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        $this->update(['transaction_id' => $transaction->id]);
    }
}
