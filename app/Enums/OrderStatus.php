<?php

namespace App\Enums;

/**
 * Statut d'une commande. La commande naît au statut « payée » (webhook Stripe, RG-20).
 * Les fonds ne sortent de la plateforme que vers le vendeur (Completed) ou vers l'acheteur (Refunded).
 */
enum OrderStatus: string
{
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Disputed = 'disputed';
    case Completed = 'completed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Payée',
            self::Shipped => 'Expédiée',
            self::Disputed => 'En litige',
            self::Completed => 'Terminée',
            self::Refunded => 'Remboursée',
        };
    }
}
