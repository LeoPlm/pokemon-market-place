<?php

namespace App\Enums;

/**
 * État d'une carte non gradée (RG-05).
 */
enum CardCondition: string
{
    case LikeNew = 'like_new';
    case VeryGood = 'very_good';
    case Good = 'good';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::LikeNew => 'Comme neuf',
            self::VeryGood => 'Très bon état',
            self::Good => 'Bon état',
            self::Damaged => 'Endommagé',
        };
    }
}
