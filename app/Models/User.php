<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Entité Utilisateur du MCD : un même utilisateur peut être acheteur et vendeur.
 */
class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Champs modifiables par l'utilisateur lui-même.
     * role, suspended_at et les champs Stripe en sont exclus volontairement :
     * ils ne doivent jamais pouvoir être remplis depuis un formulaire (élévation de privilèges).
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'postal_code',
        'city',
        'country',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'suspended_at' => 'datetime',
            'stripe_account_active' => 'boolean',
        ];
    }

    /**
     * Initiales affichées dans l'avatar (deux premières lettres du pseudo).
     */
    public function initials(): string
    {
        return Str::upper(Str::substr($this->username, 0, 2));
    }

    /**
     * Annonces publiées (« Publier »).
     *
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'seller_id');
    }

    /**
     * Commandes passées en tant qu'acheteur (« PASSER »).
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Commentaires écrits (« Ecrire »).
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Conversations ouvertes en tant qu'acheteur potentiel (« Initier »).
     *
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    /**
     * Messages envoyés (« Envoyer »).
     *
     * @return HasMany<Message, $this>
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Signalements rédigés (« Rediger »).
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Litiges tranchés en tant que modérateur (« Trancher »).
     *
     * @return HasMany<Dispute, $this>
     */
    public function resolvedDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'moderator_id');
    }
}
