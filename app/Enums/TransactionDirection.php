<?php

namespace App\Enums;

enum TransactionDirection: string
{
    case In = 'in';
    case Out = 'out';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Надходження',
            self::Out => 'Витрата',
        };
    }
}
