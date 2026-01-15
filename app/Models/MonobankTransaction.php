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
     * Get MCC category name
     */
    public function getMccCategoryAttribute(): ?string
    {
        return self::getMccCategoryName($this->mcc);
    }

    /**
     * Get MCC category key for filtering
     */
    public function getMccCategoryKeyAttribute(): ?string
    {
        return self::getMccCategoryKey($this->mcc);
    }

    /**
     * Get MCC category key by code
     */
    public static function getMccCategoryKey(?int $mcc): ?string
    {
        if (!$mcc) return null;

        return match (true) {
            // Utilities - комунальні
            $mcc === 4900 => 'utilities',

            // Groceries - продукти
            in_array($mcc, [5411, 5412, 5422, 5441, 5451, 5462, 5499]) => 'groceries',

            // Restaurants/Food - ресторани/їжа
            in_array($mcc, [5812, 5813, 5814]) => 'restaurants',

            // Fuel - паливо
            in_array($mcc, [5541, 5542, 5983]) => 'fuel',

            // Transport - транспорт
            in_array($mcc, [4111, 4112, 4121, 4131, 4784]) => 'transport',

            // Healthcare - медицина
            in_array($mcc, [5912, 8011, 8021, 8031, 8041, 8042, 8043, 8049, 8050, 8062, 8071, 8099]) => 'healthcare',

            // Education - освіта
            in_array($mcc, [8211, 8220, 8241, 8244, 8249, 8299]) => 'education',

            // Entertainment - розваги
            in_array($mcc, [7832, 7841, 7911, 7922, 7929, 7932, 7933, 7941, 7991, 7992, 7993, 7994, 7996, 7997, 7998, 7999]) => 'entertainment',

            // Shopping - покупки
            in_array($mcc, [5200, 5211, 5231, 5251, 5261, 5271, 5300, 5310, 5311, 5331, 5399, 5611, 5621, 5631, 5641, 5651, 5661, 5681, 5691, 5699, 5732, 5733, 5734, 5735]) => 'shopping',

            // Transfers - перекази
            in_array($mcc, [4829, 6010, 6011, 6012, 6050, 6051]) => 'transfers',

            default => 'other',
        };
    }

    /**
     * Get MCC category name by code
     */
    public static function getMccCategoryName(?int $mcc): ?string
    {
        if (!$mcc) return null;

        $key = self::getMccCategoryKey($mcc);
        return self::getMccCategories()[$key] ?? 'Інше';
    }

    /**
     * Get all MCC categories with labels
     */
    public static function getMccCategories(): array
    {
        return [
            'utilities' => 'Комунальні',
            'groceries' => 'Продукти',
            'restaurants' => 'Ресторани/Їжа',
            'fuel' => 'Паливо',
            'transport' => 'Транспорт',
            'healthcare' => 'Медицина',
            'education' => 'Освіта',
            'entertainment' => 'Розваги',
            'shopping' => 'Покупки',
            'transfers' => 'Перекази',
            'other' => 'Інше',
        ];
    }

    /**
     * Scope for MCC category
     */
    public function scopeMccCategory($query, string $category)
    {
        $mccCodes = self::getMccCodesForCategory($category);
        if (empty($mccCodes)) {
            return $query->whereNotIn('mcc', self::getAllKnownMccCodes());
        }
        return $query->whereIn('mcc', $mccCodes);
    }

    /**
     * Get MCC codes for category
     */
    public static function getMccCodesForCategory(string $category): array
    {
        return match ($category) {
            'utilities' => [4900],
            'groceries' => [5411, 5412, 5422, 5441, 5451, 5462, 5499],
            'restaurants' => [5812, 5813, 5814],
            'fuel' => [5541, 5542, 5983],
            'transport' => [4111, 4112, 4121, 4131, 4784],
            'healthcare' => [5912, 8011, 8021, 8031, 8041, 8042, 8043, 8049, 8050, 8062, 8071, 8099],
            'education' => [8211, 8220, 8241, 8244, 8249, 8299],
            'entertainment' => [7832, 7841, 7911, 7922, 7929, 7932, 7933, 7941, 7991, 7992, 7993, 7994, 7996, 7997, 7998, 7999],
            'shopping' => [5200, 5211, 5231, 5251, 5261, 5271, 5300, 5310, 5311, 5331, 5399, 5611, 5621, 5631, 5641, 5651, 5661, 5681, 5691, 5699, 5732, 5733, 5734, 5735],
            'transfers' => [4829, 6010, 6011, 6012, 6050, 6051],
            default => [],
        };
    }

    /**
     * Get all known MCC codes
     */
    public static function getAllKnownMccCodes(): array
    {
        $all = [];
        foreach (['utilities', 'groceries', 'restaurants', 'fuel', 'transport', 'healthcare', 'education', 'entertainment', 'shopping', 'transfers'] as $cat) {
            $all = array_merge($all, self::getMccCodesForCategory($cat));
        }
        return $all;
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
