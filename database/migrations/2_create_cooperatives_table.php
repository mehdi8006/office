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
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id('id_cooperative');
            $table->string('matricule', 10)->unique();
            $table->string('nom_cooperative');
            $table->text('adresse');
            $table->string('telephone');
            $table->string('email')->unique();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('responsable_id')->references('id_utilisateur')->on('utilisateurs')->onDelete('set null');
            
            // Add indexes for better performance
            $table->index('matricule');
            $table->index('email');
            $table->index('statut');
            $table->index('responsable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooperatives');
    }
};