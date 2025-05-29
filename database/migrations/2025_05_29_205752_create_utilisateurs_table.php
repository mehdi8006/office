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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('nom_complet');
            $table->string('email')->unique();
            $table->string('mot_de_passe');
            $table->string('telephone');
            $table->enum('role', ['éleveur', 'gestionnaire', 'usva', 'direction']);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('email');
            $table->index('role');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};