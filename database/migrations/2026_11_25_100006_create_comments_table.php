<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Commentaire du MCD : commentaires publics sous une annonce (RG-30).
 *
 * Associations portées par cette table :
 * - « Recevoir » (Annonce 0,n — Commentaire 1,1)                 : listing_id
 * - « Ecrire »   (Utilisateur 0,n — Commentaire 1,1)             : user_id
 * - « Répondre » (réflexive, Commentaire 0,n — Commentaire 0,1)  : parent_id, nul pour un commentaire racine
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();                                                                           // id_commentaire
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();                     // Recevoir
            $table->foreignId('user_id')->constrained()->restrictOnDelete();                       // Ecrire
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete(); // Répondre
            $table->string('content', 500);               // contenu (500 caractères max, RG-30)
            $table->boolean('is_hidden')->default(false); // masque (RG-12)
            $table->string('hidden_reason')->nullable();  // motif_masquage
            $table->timestamps();                         // created_at = date
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
