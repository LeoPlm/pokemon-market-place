<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Avis du MCD : la note laissée par l'acheteur au vendeur (RG-28, RG-29).
 *
 * Association « NOTER » (Commande 0,1 — Avis 1,1) : order_id, unique (un avis par commande).
 * L'auteur (l'acheteur) et le vendeur noté se retrouvent par la commande : pas de colonne redondante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();                                                               // id_avis
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete(); // NOTER
            $table->unsignedTinyInteger('rating');              // Note (1 à 5)
            $table->string('comment', 500)->nullable();         // commentaire (optionnel, 500 caractères max)
            $table->string('seller_reply', 500)->nullable();    // reponse_vendeur (une seule, RG-29)
            $table->timestamp('seller_replied_at')->nullable(); // date_reponse_vendeur
            $table->timestamps();                               // created_at = date_avis
        });

        if (DB::getDriverName() === 'mysql') {
            // RG-28 : note entière de 1 à 5
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating
                CHECK (rating BETWEEN 1 AND 5)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
