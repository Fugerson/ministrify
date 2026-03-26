<?php

namespace App\Enums;

enum TransactionSourceType: string
{
    case Tithe = 'tithe';
    case Offering = 'offering';
    case Donation = 'donation';
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';
    case Exchange = 'exchange';
    case Allocation = 'allocation';

    public function label(): string
    {
        return match ($this) {
            self::Tithe => 'Десятина',
            self::Offering => 'Пожертва',
            self::Donation => 'Донат',
            self::Income => 'Надходження',
            self::Expense => 'Витрата',
            self::Transfer => 'Переказ',
            self::Exchange => 'Обмін валюти',
            self::Allocation => 'Виділення бюджету',
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
