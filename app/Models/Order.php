<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Entité Commande du MCD (table orders).
 * Pas de $fillable : une commande n'est jamais remplie depuis un formulaire,
 * uniquement par le code métier (webhook Stripe, RG-20), attribut par attribut.
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'protection_fee' => 'integer',
            'total' => 'integer',
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    /**
     * Acheteur (« PASSER »).
     *
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Annonces achetées, toutes du même vendeur (« DONNER LIEU », RG-18).
     *
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Avis laissé par l'acheteur (« NOTER »).
     *
     * @return HasOne<Review, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Litige éventuel (« DONNER »).
     *
     * @return HasOne<Dispute, $this>
     */
    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }
}
