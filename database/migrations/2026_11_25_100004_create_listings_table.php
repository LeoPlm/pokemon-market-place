<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Annonce du MCD : un exemplaire physique mis en vente (RG-04).
 *
 * Associations portées par cette table :
 * - « Publier »     (Utilisateur 0,n — Annonce 1,1) : seller_id
 * - « Vendre »      (Carte 0,n — Annonce 1,1)       : card_id
 * - « DONNER LIEU » (Commande 1,n — Annonce 0,1)    : order_id, nul tant que l'annonce n'est pas vendue
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();                                                                  // id_annonce
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();     // Publier
            $table->foreignId('card_id')->constrained()->restrictOnDelete();              // Vendre
            $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete(); // DONNER LIEU

            $table->text('description')->nullable();            // description
            $table->unsignedInteger('price');                   // prix, en centimes (RG-09)
            $table->string('card_condition', 20)->nullable();   // etat (App\Enums\CardCondition), nul si gradée (RG-07)
            $table->string('edition', 20);                      // edition (App\Enums\Edition)
            $table->string('language', 10);                     // langue (App\Enums\Language)
            $table->boolean('is_graded')->default(false);       // gradee
            $table->string('grading_company', 10)->nullable();  // organisme_grading (App\Enums\GradingCompany)
            $table->decimal('grade', 3, 1)->nullable();         // note_si_gradee (1 à 10, demi-points)
            $table->string('shipping_method', 20);              // mode_envoi (App\Enums\ShippingMethod)
            $table->string('status', 20)->default('available'); // statut (App\Enums\ListingStatus)
            $table->timestamps();                               // created_at = date

            // Index pour la recherche et les filtres (US-02) ; les clés étrangères sont déjà indexées
            $table->index(['status', 'price']);      // annonces disponibles triées par prix
            $table->index(['status', 'created_at']); // dernières annonces disponibles
            $table->index('card_condition');
            $table->index('edition');
            $table->index('language');
            $table->index('is_graded');
        });

        if (DB::getDriverName() === 'mysql') {
            // RG-09 : prix entre 1 € et 10 000 €
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_price
                CHECK (price BETWEEN 100 AND 1000000)');

            // RG-07 : une carte gradée a un organisme et une note, qui remplace l'état ;
            // une carte non gradée a un état, sans organisme ni note
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_grading CHECK (
                (is_graded = 1 AND grading_company IS NOT NULL AND grade IS NOT NULL AND card_condition IS NULL)
                OR (is_graded = 0 AND grading_company IS NULL AND grade IS NULL AND card_condition IS NOT NULL)
            )');

            // RG-07 : note de 1 à 10, demi-points acceptés
            DB::statement('ALTER TABLE listings ADD CONSTRAINT chk_listings_grade CHECK (
                grade IS NULL OR (grade BETWEEN 1 AND 10 AND grade * 2 = FLOOR(grade * 2))
            )');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
