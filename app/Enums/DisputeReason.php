<?php

namespace App\Enums;

/**
 * Motif d'ouverture d'un litige (RG-25).
 */
enum DisputeReason: string
{
    case NotReceived = 'not_received';
    case NotAsDescribed = 'not_as_described';

    public function label(): string
    {
        return match ($this) {
            self::NotReceived => 'Carte non reçue',
            self::NotAsDescribed => 'Carte non conforme',
        };
    }
}
