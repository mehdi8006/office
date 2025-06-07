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
        Schema::create('paiements_cooperative_eleveurs', function (Blueprint $table) {
            $table->id('id_paiement');
            $table->unsignedBigInteger('id_membre');
            $table->unsignedBigInteger('id_cooperative');
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->decimal('quantite_totale', 10, 2);
            $table->decimal('prix_unitaire', 8, 2);
            $table->decimal('montant_total', 12, 2);
            $table->date('date_paiement')->nullable();
            $table->enum('statut', ['calcule', 'paye'])->default('calcule');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('id_membre')->references('id_membre')->on('membres_eleveurs')->onDelete('cascade');
            $table->foreign('id_cooperative')->references('id_cooperative')->on('cooperatives')->onDelete('cascade');
            
            // Unique constraint: one payment per member per period
            $table->unique(['id_membre', 'periode_debut', 'periode_fin'], 'unique_membre_periode');
            
            // Add indexes for better performance
            $table->index('id_membre');
            $table->index('id_cooperative');
            $table->index('periode_debut');
            $table->index('periode_fin');
            $table->index('date_paiement');
            $table->index('statut');
            $table->index(['id_membre', 'statut']);
            $table->index(['id_cooperative', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements_cooperative_eleveurs');
    }
};