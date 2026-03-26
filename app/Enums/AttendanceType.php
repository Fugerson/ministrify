<?php

namespace App\Enums;

enum AttendanceType: string
{
    case Service = 'service';
    case Group = 'group';
    case Event = 'event';
    case Meeting = 'meeting';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Богослужіння',
            self::Group => 'Мала група',
            self::Event => 'Подія',
            self::Meeting => 'Зустріч',
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
