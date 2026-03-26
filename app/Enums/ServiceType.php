<?php

namespace App\Enums;

enum ServiceType: string
{
    case Sunday = 'sunday_service';
    case Youth = 'youth_meeting';
    case Prayer = 'prayer_meeting';
    case Special = 'special_service';

    public function label(): string
    {
        return match ($this) {
            self::Sunday => 'Недільне служіння',
            self::Youth => 'Молодіжна зустріч',
            self::Prayer => 'Молитовна зустріч',
            self::Special => 'Особливе служіння',
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
