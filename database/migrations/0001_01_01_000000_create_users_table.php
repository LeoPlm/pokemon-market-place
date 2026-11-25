<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Entité Utilisateur du MCD (correspondance des noms : docs/base-de-donnees.md)
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                        // id_utilisateur
            $table->string('username', 20)->unique();            // pseudo (RG-01 : unique, 3 à 20 caractères)
            $table->string('first_name')->nullable();            // prenom
            $table->string('last_name')->nullable();             // nom
            $table->string('email')->unique();                   // adresse_mail
            $table->timestamp('email_verified_at')->nullable();  // date_verif_email
            $table->string('password');                          // mot_de_passe (haché)
            $table->string('address')->nullable();               // adresse
            $table->string('postal_code', 10)->nullable();       // code_postal
            $table->string('city')->nullable();                  // ville
            $table->char('country', 2)->nullable();              // pays (code ISO 3166-1, ex. FR)
            $table->string('role', 20)->default('user')->index(); // role (App\Enums\UserRole)
            $table->timestamp('suspended_at')->nullable();       // suspension (null = compte actif)
            $table->string('stripe_account_id')->nullable()->unique(); // id_stripe (compte Stripe Connect)
            $table->boolean('stripe_account_active')->default(false); // compte_active (RG-02)
            $table->rememberToken();
            $table->timestamps();                                // created_at = date_inscription
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
