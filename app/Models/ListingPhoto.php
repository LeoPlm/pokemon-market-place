<?php

namespace App\Models;

use Database\Factories\ListingPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entité Photo du MCD (table listing_photos).
 */
class ListingPhoto extends Model
{
    /** @use HasFactory<ListingPhotoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'path',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * Annonce illustrée (« Illustrer »).
     *
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
