<?php

namespace App\Enums;

enum ExpenseType: string
{
    case Recurring = 'recurring';
    case OneTime = 'one_time';

    public function label(): string
    {
        return match ($this) {
            self::Recurring => 'Регулярна',
            self::OneTime => 'Одноразова',
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
