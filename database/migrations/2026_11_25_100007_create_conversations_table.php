<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Conversation du MCD : messagerie privée entre un acheteur potentiel et le vendeur d'une annonce.
 *
 * Associations portées par cette table :
 * - « OUVRIR »  (Annonce 0,n — Conversation 1,1)     : listing_id (le vendeur est celui de l'annonce)
 * - « Initier » (Utilisateur 0,n — Conversation 1,1) : buyer_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();                                                             // id_conversation
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();       // OUVRIR
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete(); // Initier
            $table->timestamps();                                                     // created_at = date_debut

            // RG-31 : une seule conversation par couple (annonce, acheteur potentiel)
            $table->unique(['listing_id', 'buyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
