<?php

namespace App\Enums;

enum ChurchRole: string
{
    case Member = 'member';
    case Servant = 'servant';
    case Deacon = 'deacon';
    case Presbyter = 'presbyter';
    case Pastor = 'pastor';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Член церкви',
            self::Servant => 'Служитель',
            self::Deacon => 'Диякон',
            self::Presbyter => 'Пресвітер',
            self::Pastor => 'Пастор',
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
