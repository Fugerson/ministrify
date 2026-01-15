<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivatbankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'privat_id',
        'amount',
        'card_amount',
        'rest',
        'currency',
        'privat_time',
        'description',
        'terminal',
        'counterpart_name',
        'counterpart_okpo',
        'counterpart_mfo',
        'counterpart_account',
        'transaction_id',
        'person_id',
        'is_income',
        'is_processed',
        'is_ignored',
    ];

    protected $casts = [
        'privat_time' => 'datetime',
        'is_income' => 'boolean',
        'is_processed' => 'boolean',
        'is_ignored' => 'boolean',
        'amount' => 'integer',
        'card_amount' => 'integer',
        'rest' => 'integer',
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
    public function getRestUahAttribute(): ?float
    {
        return $this->rest ? $this->rest / 100 : null;
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
     * Scope for expense only
     */
    public function scopeExpense($query)
    {
        return $query->where('is_income', false);
    }

    /**
     * Create from PrivatBank API response
     */
    public static function createFromPrivatData(int $churchId, array $data): self
    {
        // Amount can come as string like "10.50 UAH" or as cents
        $amount = self::parseAmount($data['cardamount'] ?? $data['amount'] ?? '0');
        $cardAmount = self::parseAmount($data['cardamount'] ?? '0');
        $rest = self::parseAmount($data['rest'] ?? '0');

        return self::updateOrCreate(
            ['privat_id' => $data['tranId'] ?? $data['appcode'] ?? md5(json_encode($data))],
            [
                'church_id' => $churchId,
                'amount' => $amount,
                'card_amount' => $cardAmount,
                'rest' => $rest,
                'currency' => $data['currency'] ?? 'UAH',
                'privat_time' => self::parseDateTime($data['trandate'] ?? '', $data['trantime'] ?? ''),
                'description' => $data['description'] ?? $data['terminal'] ?? null,
                'terminal' => $data['terminal'] ?? null,
                'counterpart_name' => $data['counterparty_name'] ?? $data['description'] ?? null,
                'counterpart_okpo' => $data['counterparty_okpo'] ?? null,
                'counterpart_mfo' => $data['counterparty_mfo'] ?? null,
                'counterpart_account' => $data['counterparty_account'] ?? null,
                'is_income' => $amount > 0,
            ]
        );
    }

    /**
     * Parse amount from PrivatBank format (e.g. "-10.50 UAH" or "10.50")
     */
    protected static function parseAmount(string $value): int
    {
        // Remove currency suffix
        $value = preg_replace('/[A-Z]{3}$/', '', trim($value));
        // Convert to float and then to kopiykas
        $float = (float) str_replace([' ', ','], ['', '.'], $value);
        return (int) round($float * 100);
    }

    /**
     * Parse date and time from PrivatBank format
     */
    protected static function parseDateTime(string $date, string $time): \Carbon\Carbon
    {
        if (empty($date)) {
            return now();
        }

        // PrivatBank uses DD.MM.YYYY format
        $dateTime = trim($date . ' ' . $time);

        try {
            return \Carbon\Carbon::createFromFormat('d.m.Y H:i:s', $dateTime);
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::createFromFormat('d.m.Y', $date)->startOfDay();
            } catch (\Exception $e) {
                return now();
            }
        }
    }
}
