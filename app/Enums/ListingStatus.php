<?php

namespace App\Enums;

/**
 * Statut d'une annonce (cycle de vie du cahier des charges fonctionnel).
 * Les transitions autorisées seront contrôlées par une machine à états (tâche « Gérer mes annonces »).
 */
enum ListingStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Withdrawn = 'withdrawn';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Reserved => 'Réservée',
            self::Sold => 'Vendue',
            self::Withdrawn => 'Retirée',
            self::Hidden => 'Masquée',
        };
    }
}
