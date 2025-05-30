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
        Schema::create('membres_eleveurs', function (Blueprint $table) {
            $table->id('id_membre');
            $table->unsignedBigInteger('id_cooperative');
            $table->string('nom_complet');
            $table->text('adresse');
            $table->string('telephone');
            $table->string('email')->unique();
            $table->string('numero_carte_nationale', 20)->unique();
            $table->enum('statut', ['actif', 'inactif', 'suppression'])->default('actif');
            $table->text('raison_suppression')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('id_cooperative')->references('id_cooperative')->on('cooperatives')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index('id_cooperative');
            $table->index('email');
            $table->index('numero_carte_nationale');
            $table->index('statut');
            $table->index('created_at');
            $table->index(['id_cooperative', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membres_eleveurs');
    }
};