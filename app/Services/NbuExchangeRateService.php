<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NbuExchangeRateService
{
    /**
     * NBU currency codes for USD and EUR
     */
    private const CURRENCY_CODES = [
        'USD' => 840,
        'EUR' => 978,
    ];

    /**
     * NBU API endpoint
     */
    private const API_URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';

    /**
     * Sync exchange rates from NBU for a specific date.
     */
    public function syncRates(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $dateString = $date->format('Ymd');

        try {
            $response = Http::timeout(10)
                ->get(self::API_URL, [
                    'date' => $dateString,
                    'json' => '',
                ]);

            if (!$response->successful()) {
                Log::error('NBU API request failed', [
                    'status' => $response->status(),
                    'date' => $dateString,
                ]);
                return ['success' => false, 'error' => 'API request failed'];
            }

            $rates = $response->json();
            if (empty($rates)) {
                Log::warning('NBU API returned empty response', ['date' => $dateString]);
                return ['success' => false, 'error' => 'Empty response from NBU'];
            }

            $synced = [];
            foreach ($rates as $rate) {
                $currencyCode = $this->getCurrencyCodeFromNumeric($rate['r030'] ?? null);

                if (!$currencyCode) {
                    continue;
                }

                $exchangeRate = ExchangeRate::updateOrCreate(
                    [
                        'currency_code' => $currencyCode,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'rate' => $rate['rate'],
                        'source' => 'nbu',
                    ]
                );

                $synced[$currencyCode] = $exchangeRate->rate;
            }

            ExchangeRate::clearCache();

            Log::info('Exchange rates synced from NBU', [
                'date' => $date->format('Y-m-d'),
                'rates' => $synced,
            ]);

            return [
                'success' => true,
                'date' => $date->format('Y-m-d'),
                'rates' => $synced,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to sync exchange rates', [
                'error' => $e->getMessage(),
                'date' => $dateString,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync rates for a date range (useful for backfilling).
     */
    public function syncRatesForRange(Carbon $startDate, Carbon $endDate): array
    {
        $results = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip weekends - NBU doesn't publish rates on weekends
            if (!$current->isWeekend()) {
                $result = $this->syncRates($current);
                $results[$current->format('Y-m-d')] = $result;

                // Small delay to avoid rate limiting
                usleep(100000); // 100ms
            }

            $current->addDay();
        }

        return $results;
    }

    /**
     * Get currency code from NBU numeric code.
     */
    private function getCurrencyCodeFromNumeric(?int $numericCode): ?string
    {
        if (!$numericCode) {
            return null;
        }

        foreach (self::CURRENCY_CODES as $code => $numeric) {
            if ($numeric === $numericCode) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Get the current rates from NBU (for display purposes).
     */
    public function getCurrentRates(): array
    {
        $rates = ExchangeRate::getLatestRates();

        if (count($rates) <= 1) {
            // No rates in DB, try to fetch them
            $this->syncRates();
            $rates = ExchangeRate::getLatestRates();
        }

        return $rates;
    }
}
