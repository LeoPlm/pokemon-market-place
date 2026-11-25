<?php

namespace App\Models;

use App\Enums\DisputeDecision;
use App\Enums\DisputeReason;
use App\Enums\DisputeStatus;
use Database\Factories\DisputeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entité Litige du MCD (table disputes).
 */
class Dispute extends Model
{
    /** @use HasFactory<DisputeFactory> */
    use HasFactory;

    /**
     * Seuls les champs saisis par l'acheteur à l'ouverture : la décision est posée par le code métier.
     *
     * @var list<string>
     */
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
            'reason' => DisputeReason::class,
            'status' => DisputeStatus::class,
            'decision' => DisputeDecision::class,
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Commande contestée (« DONNER »).
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Modérateur qui a tranché (« Trancher »), nul tant que le litige est ouvert.
     *
     * @return BelongsTo<User, $this>
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
