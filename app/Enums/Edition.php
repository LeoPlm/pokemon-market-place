<?php

namespace App\Enums;

/**
 * Édition de la carte (RG-05).
 */
enum Edition: string
{
    case FirstEdition = 'first_edition';
    case Unlimited = 'unlimited';

    public function label(): string
    {
        return match ($this) {
            self::FirstEdition => '1ʳᵉ édition',
            self::Unlimited => 'Unlimited',
        };
    }
}
