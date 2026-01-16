<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Currency symbols mapping.
     */
    public const SYMBOLS = [
        'UAH' => '₴',
        'USD' => '$',
        'EUR' => '€',
    ];

    /**
     * Currency names in Ukrainian.
     */
    public const NAMES = [
        'UAH' => 'Гривня',
        'USD' => 'Долар США',
        'EUR' => 'Євро',
    ];

    /**
     * Available currencies.
     */
    public const CURRENCIES = ['UAH', 'USD', 'EUR'];

    /**
     * Get the symbol for a currency.
     */
    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[strtoupper($currency)] ?? $currency;
    }

    /**
     * Get the name of a currency in Ukrainian.
     */
    public static function name(string $currency): string
    {
        return self::NAMES[strtoupper($currency)] ?? $currency;
    }

    /**
     * Format an amount with the appropriate currency symbol.
     */
    public static function format(float $amount, string $currency = 'UAH', bool $showSymbol = true): string
    {
        $currency = strtoupper($currency);

        // Format based on currency
        switch ($currency) {
            case 'USD':
            case 'EUR':
                // Western format: $1,234.56
                $formatted = number_format(abs($amount), 2, '.', ',');
                $symbol = $showSymbol ? self::symbol($currency) : '';
                $result = $symbol . $formatted;
                break;

            case 'UAH':
            default:
                // Ukrainian format: 1 234,56 ₴
                $formatted = number_format(abs($amount), 0, ',', ' ');
                $symbol = $showSymbol ? ' ' . self::symbol($currency) : '';
                $result = $formatted . $symbol;
                break;
        }

        // Add negative sign if needed
        return $amount < 0 ? '-' . $result : $result;
    }

    /**
     * Format an amount with a sign prefix (+/-).
     */
    public static function formatWithSign(float $amount, string $currency = 'UAH', string $direction = null): string
    {
        $sign = '';
        if ($direction === 'in' || $amount > 0) {
            $sign = '+';
        } elseif ($direction === 'out' || $amount < 0) {
            $sign = '-';
        }

        return $sign . self::format(abs($amount), $currency);
    }

    /**
     * Format a compact amount (for mobile/small displays).
     */
    public static function formatCompact(float $amount, string $currency = 'UAH'): string
    {
        $currency = strtoupper($currency);

        if ($amount >= 1000000) {
            $formatted = number_format($amount / 1000000, 1, '.', '') . 'M';
        } elseif ($amount >= 1000) {
            $formatted = number_format($amount / 1000, 1, '.', '') . 'K';
        } else {
            $formatted = number_format($amount, 0, ',', ' ');
        }

        return self::symbol($currency) . $formatted;
    }

    /**
     * Get all available currencies as options for a dropdown.
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::CURRENCIES as $code) {
            $options[$code] = self::symbol($code) . ' ' . self::name($code);
        }
        return $options;
    }

    /**
     * Get currencies that are enabled for a church.
     */
    public static function getEnabledCurrencies(?array $enabledCurrencies): array
    {
        if (empty($enabledCurrencies)) {
            return ['UAH'];
        }

        // Filter to only valid currencies
        return array_intersect($enabledCurrencies, self::CURRENCIES);
    }
}
