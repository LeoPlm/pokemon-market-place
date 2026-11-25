<?php

namespace App\Enums;

/**
 * Langue de la carte (RG-06).
 */
enum Language: string
{
    case French = 'fr';
    case English = 'en';
    case German = 'de';
    case Italian = 'it';
    case Spanish = 'es';
    case Japanese = 'ja';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::French => 'Français',
            self::English => 'Anglais',
            self::German => 'Allemand',
            self::Italian => 'Italien',
            self::Spanish => 'Espagnol',
            self::Japanese => 'Japonais',
            self::Other => 'Autre',
        };
    }
}
