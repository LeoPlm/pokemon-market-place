<?php

namespace App\Models;

use App\Enums\CardCondition;
use App\Enums\Edition;
use App\Enums\GradingCompany;
use App\Enums\Language;
use App\Enums\ListingStatus;
use App\Enums\ShippingMethod;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entité Annonce du MCD : un exemplaire physique mis en vente (table listings).
 */
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory;

    /**
     * seller_id, order_id et status sont exclus : ils sont fixés par le code métier
     * (vendeur connecté, achat, machine à états), jamais par un formulaire.
     *
     * @var list<string>
     */
    protected $fillable = [
        'card_id',
        'description',
        'price',
        'card_condition',
        'edition',
        'language',
        'is_graded',
        'grading_company',
        'grade',
        'shipping_method',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'card_condition' => CardCondition::class,
            'edition' => Edition::class,
            'language' => Language::class,
            'is_graded' => 'boolean',
            'grading_company' => GradingCompany::class,
            'grade' => 'decimal:1',
            'shipping_method' => ShippingMethod::class,
            'status' => ListingStatus::class,
        ];
    }

    /**
     * Vendeur (« Publier »).
     *
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Carte de référence du catalogue (« Vendre »).
     *
     * @return BelongsTo<Card, $this>
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Commande qui a acheté l'annonce, nulle tant qu'elle n'est pas vendue (« DONNER LIEU »).
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Photos, de 2 à 8, triées par position (« Illustrer »).
     *
     * @return HasMany<ListingPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ListingPhoto::class)->orderBy('position');
    }

    /**
     * Commentaires publics (« Recevoir »).
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Conversations privées ouvertes sur l'annonce (« OUVRIR »).
     *
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Signalements visant l'annonce (« Viser »).
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
