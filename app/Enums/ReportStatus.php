<?php

namespace App\Enums;

/**
 * Statut d'un signalement (US-20 : classer ou sanctionner).
 */
enum ReportStatus: string
{
    case Pending = 'pending';
    case Dismissed = 'dismissed';
    case ActionTaken = 'action_taken';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Dismissed => 'Classé sans suite',
            self::ActionTaken => 'Sanctionné',
        };
    }
}
