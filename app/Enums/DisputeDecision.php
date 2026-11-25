<?php

namespace App\Enums;

/**
 * Décision du modérateur sur un litige (RG-26).
 */
enum DisputeDecision: string
{
    case RefundBuyer = 'refund_buyer';
    case PaySeller = 'pay_seller';

    public function label(): string
    {
        return match ($this) {
            self::RefundBuyer => 'Remboursement de l\'acheteur',
            self::PaySeller => 'Versement au vendeur',
        };
    }
}
