<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonobankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'mono_id',
        'amount',
        'balance',
        'cashback_amount',
        'commission_rate',
        'currency_code',
        'mono_time',
        'description',
        'comment',
        'mcc',
        'counterpart_iban',
        'counterpart_name',
        'transaction_id',
        'person_id',
        'is_income',
        'is_processed',
        'is_ignored',
    ];

    protected $casts = [
        'mono_time' => 'datetime',
        'is_income' => 'boolean',
        'is_processed' => 'boolean',
        'is_ignored' => 'boolean',
        'amount' => 'integer',
        'balance' => 'integer',
    ];

    /**
     * Get amount in UAH (from kopiykas)
     */
    public function getAmountUahAttribute(): float
    {
        return abs($this->amount) / 100;
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        $amount = number_format($this->amount_uah, 2, ',', ' ');
        $sign = $this->is_income ? '+' : '-';
        return "{$sign}{$amount} ₴";
    }

    /**
     * Get balance in UAH
     */
    public function getBalanceUahAttribute(): ?float
    {
        return $this->balance ? $this->balance / 100 : null;
    }

    /**
     * Get sender/recipient name
     */
    public function getCounterpartDisplayAttribute(): string
    {
        if ($this->counterpart_name) {
            return $this->counterpart_name;
        }
        if ($this->description) {
            return $this->description;
        }
        return 'Невідомий відправник';
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Scope for unprocessed income transactions
     */
    public function scopeUnprocessedIncome($query)
    {
        return $query->where('is_income', true)
            ->where('is_processed', false)
            ->where('is_ignored', false);
    }

    /**
     * Scope for income only
     */
    public function scopeIncome($query)
    {
        return $query->where('is_income', true);
    }

    /**
     * Create from Monobank API response
     */
    public static function createFromMonoData(int $churchId, array $data): self
    {
        return self::updateOrCreate(
            ['mono_id' => $data['id']],
            [
                'church_id' => $churchId,
                'amount' => $data['amount'],
                'balance' => $data['balance'] ?? null,
                'cashback_amount' => $data['cashbackAmount'] ?? 0,
                'commission_rate' => $data['commissionRate'] ?? 0,
                'currency_code' => $data['currencyCode'] ?? '980',
                'mono_time' => \Carbon\Carbon::createFromTimestamp($data['time']),
                'description' => $data['description'] ?? null,
                'comment' => $data['comment'] ?? null,
                'mcc' => $data['mcc'] ?? null,
                'counterpart_iban' => $data['counterIban'] ?? null,
                'counterpart_name' => $data['counterName'] ?? null,
                'is_income' => $data['amount'] > 0,
            ]
        );
    }
}
