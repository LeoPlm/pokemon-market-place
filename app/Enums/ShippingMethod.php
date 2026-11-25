<?php

namespace App\Enums;

/**
 * Mode d'envoi choisi par le vendeur pour une annonce (RG-15).
 * Les tarifs ne sont pas ici : ils sont paramétrables (configuration).
 */
enum ShippingMethod: string
{
    case TrackedLetter = 'tracked_letter';
    case InsuredParcel = 'insured_parcel';

    public function label(): string
    {
        return match ($this) {
            self::TrackedLetter => 'Lettre suivie',
            self::InsuredParcel => 'Colis suivi assuré',
        };
    }
}
