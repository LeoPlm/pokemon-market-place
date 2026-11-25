<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Signalement du MCD (RG-13).
 *
 * Associations portées par cette table :
 * - « Rediger »   (Utilisateur 0,n — Signalement 1,1) : reporter_id
 * - « Viser »     (Annonce 0,n — Signalement 0,1)     : listing_id
 * - « Concerner » (Commentaire 0,n — Signalement 0,1) : comment_id
 * Contrainte XT du MCD (exclusion + totalité) : un signalement vise exactement une annonce OU un commentaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();                                                                    // id_signalement
            $table->foreignId('reporter_id')->constrained('users')->restrictOnDelete();     // Rediger
            $table->foreignId('listing_id')->nullable()->constrained()->restrictOnDelete(); // Viser
            $table->foreignId('comment_id')->nullable()->constrained()->restrictOnDelete(); // Concerner
            $table->string('reason');                                  // motif (obligatoire, US-12)
            $table->text('description')->nullable();                   // description
            $table->string('status', 20)->default('pending')->index(); // statut (App\Enums\ReportStatus)
            $table->timestamps();                                      // created_at = date_debut

            // RG-13 : un utilisateur ne signale qu'une fois le même élément.
            // Un index unique accepte plusieurs NULL : les deux contraintes cohabitent sans se gêner.
            $table->unique(['reporter_id', 'listing_id']);
            $table->unique(['reporter_id', 'comment_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            // Contrainte XT du MCD
            DB::statement('ALTER TABLE reports ADD CONSTRAINT chk_reports_target CHECK (
                (listing_id IS NOT NULL AND comment_id IS NULL)
                OR (listing_id IS NULL AND comment_id IS NOT NULL)
            )');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
