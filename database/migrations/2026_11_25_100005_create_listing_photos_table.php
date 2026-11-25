<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Photo du MCD.
 *
 * Association « Illustrer » (Annonce 2,8 — Photo 1,1) : clé étrangère listing_id.
 * Le minimum de 2 et le maximum de 8 photos par annonce (RG-08) ne s'expriment pas en SQL :
 * ils sont vérifiés par la validation côté serveur à la création de l'annonce.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_photos', function (Blueprint $table) {
            $table->id();                                                       // id_photo
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete(); // Illustrer
            $table->string('path');                                             // chemin (fichier sur le disque de stockage)
            $table->unsignedTinyInteger('position');                            // position (1 = recto, 2 = verso, …)
            $table->timestamps();                                               // created_at = date_ajout

            $table->unique(['listing_id', 'position']); // pas deux photos à la même place
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE listing_photos ADD CONSTRAINT chk_listing_photos_position
                CHECK (position BETWEEN 1 AND 8)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_photos');
    }
};
