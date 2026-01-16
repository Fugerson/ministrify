<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_code',
        'rate',
        'date',
        'source',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'date' => 'date',
    ];

    /**
     * Get the exchange rate for a currency on a specific date.
     * Falls back to the nearest available rate if exact date not found.
     */
    public static function getRateForDate(string $currency, $date = null): ?float
    {
        if ($currency === 'UAH') {
            return 1.0;
        }

        $date = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $cacheKey = "exchange_rate_{$currency}_{$date}";

        return Cache::remember($cacheKey, 3600, function () use ($currency, $date) {
            // Try exact date first
            $rate = static::where('currency_code', $currency)
                ->where('date', $date)
                ->value('rate');

            if ($rate) {
                return (float) $rate;
            }

            // Fall back to the most recent rate before the date
            $rate = static::where('currency_code', $currency)
                ->where('date', '<=', $date)
                ->orderByDesc('date')
                ->value('rate');

            if ($rate) {
                return (float) $rate;
            }

            // If no historical rate, get the oldest available rate
            return (float) static::where('currency_code', $currency)
                ->orderBy('date')
                ->value('rate');
        });
    }

    /**
     * Convert an amount from one currency to another.
     */
    public static function convert(float $amount, string $from, string $to, $date = null): float
    {
        if ($from === $to) {
            return $amount;
        }

        // Convert to UAH first
        if ($from === 'UAH') {
            $amountInUah = $amount;
        } else {
            $fromRate = static::getRateForDate($from, $date);
            if (!$fromRate) {
                return $amount; // Can't convert without rate
            }
            $amountInUah = $amount * $fromRate;
        }

        // Convert from UAH to target currency
        if ($to === 'UAH') {
            return $amountInUah;
        }

        $toRate = static::getRateForDate($to, $date);
        if (!$toRate) {
            return $amountInUah; // Return UAH if can't convert
        }

        return $amountInUah / $toRate;
    }

    /**
     * Convert an amount to UAH.
     */
    public static function toUah(float $amount, string $currency, $date = null): float
    {
        return static::convert($amount, $currency, 'UAH', $date);
    }

    /**
     * Get latest rates for all currencies.
     */
    public static function getLatestRates(): array
    {
        return Cache::remember('latest_exchange_rates', 3600, function () {
            $rates = ['UAH' => 1.0];

            $latestRates = static::whereIn('currency_code', ['USD', 'EUR'])
                ->orderByDesc('date')
                ->get()
                ->unique('currency_code');

            foreach ($latestRates as $rate) {
                $rates[$rate->currency_code] = (float) $rate->rate;
            }

            return $rates;
        });
    }

    /**
     * Clear the exchange rate cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('latest_exchange_rates');
        // Note: Individual date caches will expire naturally
    }
}
