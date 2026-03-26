<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case Guest = 'guest';
    case Newcomer = 'newcomer';
    case Member = 'member';
    case Servant = 'servant';
    case Leader = 'leader';
    case Leadership = 'leadership';

    public function label(): string
    {
        return match ($this) {
            self::Guest => 'Гість',
            self::Newcomer => 'Новачок',
            self::Member => 'Член церкви',
            self::Servant => 'Служитель',
            self::Leader => 'Лідер',
            self::Leadership => 'Керівництво церкви',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Guest => '#9ca3af',
            self::Newcomer => '#f59e0b',
            self::Member => '#3b82f6',
            self::Servant => '#10b981',
            self::Leader => '#8b5cf6',
            self::Leadership => '#dc2626',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Guest => 'user',
            self::Newcomer => 'star',
            self::Member => 'users',
            self::Servant => 'hand-raised',
            self::Leader => 'shield-check',
            self::Leadership => 'crown',
        };
    }

    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = [
                'label' => $case->label(),
                'color' => $case->color(),
                'icon' => $case->icon(),
            ];
        }

        return $result;
    }
}
