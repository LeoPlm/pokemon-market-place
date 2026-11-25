<?php

namespace App\Models;

use Database\Factories\SetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entité Extension du MCD (table sets).
 */
class Set extends Model
{
    /** @use HasFactory<SetFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'released_at',
        'logo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'released_at' => 'date',
        ];
    }

    /**
     * Cartes de l'extension (« contenir »).
     *
     * @return HasMany<Card, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
