<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entité Signalement du MCD (table reports).
 * Vise exactement une annonce OU un commentaire (contrainte XT du MCD).
 */
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'reason',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
        ];
    }

    /**
     * Auteur du signalement (« Rediger »).
     *
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Annonce visée (« Viser »), nulle si le signalement concerne un commentaire.
     *
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Commentaire visé (« Concerner »), nul si le signalement concerne une annonce.
     *
     * @return BelongsTo<Comment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
