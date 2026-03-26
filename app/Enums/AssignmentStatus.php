<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Attended = 'attended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Очікує підтвердження',
            self::Confirmed => 'Підтверджено',
            self::Declined => 'Відхилено',
            self::Attended => 'Був присутній',
        };
    }

    /**
     * Valid transitions from this status.
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Declined],
            self::Confirmed => [self::Attended, self::Declined],
            self::Declined => [self::Pending],
            self::Attended => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->transitions());
    }

    public static function labels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
