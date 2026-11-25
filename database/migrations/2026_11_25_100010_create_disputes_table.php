<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Litige du MCD (RG-25, RG-26).
 *
 * Associations portées par cette table :
 * - « DONNER »   (Commande 0,1 — Litige 1,1)    : order_id, unique (un litige au plus par commande)
 * - « Trancher » (Utilisateur 0,n — Litige 0,1) : moderator_id, nul tant que le litige n'est pas tranché
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();                                                                            // id_litige
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();              // DONNER
            $table->foreignId('moderator_id')->nullable()->constrained('users')->restrictOnDelete(); // Trancher
            $table->string('reason', 30);                           // motif (App\Enums\DisputeReason)
            $table->text('description');                            // description
            $table->string('status', 20)->default('open')->index(); // statut (App\Enums\DisputeStatus)
            $table->string('decision', 20)->nullable();             // decision (App\Enums\DisputeDecision)
            $table->text('justification')->nullable();              // justification de la décision
            $table->timestamp('resolved_at')->nullable();           // date_resolution
            $table->timestamps();                                   // created_at = date_ouverture
        });

        if (DB::getDriverName() === 'mysql') {
            // RG-26 : un litige tranché a forcément une décision, un modérateur et une date (décision tracée)
            DB::statement("ALTER TABLE disputes ADD CONSTRAINT chk_disputes_resolution CHECK (
                (status = 'open' AND decision IS NULL AND resolved_at IS NULL)
                OR (status = 'resolved' AND decision IS NOT NULL AND moderator_id IS NOT NULL AND resolved_at IS NOT NULL)
            )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
