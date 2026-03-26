<?php

namespace App\Enums;

enum MaritalStatus: string
{
    case Single = 'single';
    case Married = 'married';
    case Widowed = 'widowed';
    case Divorced = 'divorced';

    public function label(): string
    {
        return match ($this) {
            self::Single => __('app.marital_single'),
            self::Married => __('app.marital_married'),
            self::Widowed => __('app.marital_widowed'),
            self::Divorced => __('app.marital_divorced'),
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
