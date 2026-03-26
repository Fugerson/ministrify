<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case LiqPay = 'liqpay';
    case Monobank = 'monobank';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Готівка',
            self::Card => 'Картка',
            self::LiqPay => 'LiqPay',
            self::Monobank => 'Monobank',
        };
    }

    public static function labels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
