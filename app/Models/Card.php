<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entité Carte du MCD : carte de référence du catalogue (table cards).
 */
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'set_id',
        'name',
        'number',
        'rarity',
        'image',
        'external_id',
    ];

    /**
     * Extension de la carte (« contenir »).
     *
     * @return BelongsTo<Set, $this>
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    /**
     * Annonces de cet exemplaire de référence (« Vendre »).
     *
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
