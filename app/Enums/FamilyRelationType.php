<?php

namespace App\Enums;

enum FamilyRelationType: string
{
    case Spouse = 'spouse';
    case Child = 'child';
    case Parent = 'parent';
    case Sibling = 'sibling';

    public function label(): string
    {
        return match ($this) {
            self::Spouse => 'Чоловік/Дружина',
            self::Child => 'Дитина',
            self::Parent => 'Батько/Мати',
            self::Sibling => 'Брат/Сестра',
        };
    }

    public function inverse(): self
    {
        return match ($this) {
            self::Spouse => self::Spouse,
            self::Child => self::Parent,
            self::Parent => self::Child,
            self::Sibling => self::Sibling,
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
