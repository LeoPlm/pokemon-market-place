<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Carte du MCD : la carte de référence du catalogue standardisé
 * (à ne pas confondre avec l'annonce, qui est un exemplaire physique).
 *
 * Association « contenir » (Extension 1,n — Carte 1,1) : clé étrangère set_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();                                                    // id_carte
            $table->foreignId('set_id')->constrained()->restrictOnDelete(); // contenir
            $table->string('name');                                          // nom
            $table->string('number', 10);                                    // numero (ex. « 4 » pour Dracaufeu Base Set)
            $table->string('rarity', 50)->nullable();                        // rarete
            $table->string('image')->nullable();                             // image (URL de l'API TCG)
            $table->string('external_id')->unique();                         // id_externe (id de l'API Pokémon TCG, ex. base1-4)
            $table->timestamps();

            $table->unique(['set_id', 'number']); // un numéro n'existe qu'une fois par extension
            $table->index('name');                // recherche par nom
            $table->index('rarity');              // filtre par rareté (US-02)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
