<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entité Extension du MCD : les 7 extensions de 1re génération (Base Set, Jungle, Fossil…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sets', function (Blueprint $table) {
            $table->id();                             // id_extension
            $table->string('name');                   // nom
            $table->string('slug')->unique();         // slug (URL : /extensions/base-set)
            $table->date('released_at')->nullable();  // date_sortie
            $table->string('logo')->nullable();       // logo (chemin ou URL de l'image)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sets');
    }
};
