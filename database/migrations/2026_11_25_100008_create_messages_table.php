<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Message du MCD.
 *
 * Associations portées par cette table :
 * - « AVOIR »   (Conversation 1,n — Message 1,1) : conversation_id
 * - « Envoyer » (Utilisateur 0,n — Message 1,1)  : sender_id (l'acheteur ou le vendeur)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();                                                              // id_message
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();   // AVOIR
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete(); // Envoyer
            $table->string('content', 2000);          // contenu (2 000 caractères max, RG-31)
            $table->timestamp('read_at')->nullable(); // date_lecture (nul = non lu)
            $table->timestamps();                     // created_at = date_envoi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
