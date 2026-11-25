<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Commande du MCD.
 *
 * Association « PASSER » (Utilisateur 0,n — Commande 1,1) : clé étrangère buyer_id.
 * Les montants sont en centimes et figés au moment du paiement (RG-16) : ce sont des copies, jamais recalculées.
 * L'adresse de livraison est recopiée : elle ne change pas si l'acheteur modifie son profil ensuite.
 *
 * Créée avant listings, car une annonce référence la commande qui l'a achetée (« DONNER LIEU »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();                                                             // id_commande
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete(); // PASSER

            // Montants figés, en centimes (RG-14 à RG-17)
            $table->unsignedInteger('subtotal');       // prix (somme des prix des cartes)
            $table->unsignedInteger('shipping_fee');   // frais_port
            $table->unsignedInteger('protection_fee'); // frais_protection
            $table->unsignedInteger('total');          // total

            $table->string('status', 20)->default('paid')->index();             // statut (App\Enums\OrderStatus)
            $table->string('tracking_number', 100)->nullable();                 // numero_suivi
            $table->string('stripe_payment_intent_id')->nullable()->unique();   // id_paiement_stripe

            $table->timestamp('paid_at');                // date_paiement (la commande naît payée)
            $table->timestamp('shipped_at')->nullable(); // date_expedition
            $table->timestamp('received_at')->nullable(); // date_reception

            // Adresse de livraison recopiée au moment de l'achat
            $table->string('shipping_first_name');         // prenom
            $table->string('shipping_last_name');          // nom
            $table->string('shipping_address');            // adresse
            $table->string('shipping_postal_code', 10);    // code_postal
            $table->string('shipping_city');               // ville
            $table->char('shipping_country', 2);           // pays (code ISO 3166-1, ex. FR)

            $table->timestamps();
        });

        // RG-16 : total = prix des cartes + frais de port + frais de protection.
        // Les CHECK ne sont posées que sous MySQL (SQLite, utilisé par les tests locaux, ne sait pas les ajouter après coup).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_total
                CHECK (total = subtotal + shipping_fee + protection_fee)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
