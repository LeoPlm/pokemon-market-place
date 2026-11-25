<?php

namespace App\Enums;

/**
 * Organisme de gradation d'une carte gradée (RG-07).
 */
enum GradingCompany: string
{
    case Psa = 'psa';
    case Pca = 'pca';
    case Cgc = 'cgc';
    case Bgs = 'bgs';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Psa => 'PSA',
            self::Pca => 'PCA',
            self::Cgc => 'CGC',
            self::Bgs => 'BGS',
            self::Other => 'Autre',
        };
    }
}
