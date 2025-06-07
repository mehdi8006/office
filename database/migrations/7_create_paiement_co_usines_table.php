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
        Schema::create('paiements_cooperative_usine', function (Blueprint $table) {
            $table->id('id_paiement');
            $table->unsignedBigInteger('id_cooperative');
            $table->unsignedBigInteger('id_livraison');
            $table->date('date_paiement');
            $table->decimal('montant', 12, 2);
            $table->enum('statut', ['en_attente', 'paye'])->default('en_attente');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('id_cooperative')->references('id_cooperative')->on('cooperatives')->onDelete('cascade');
            $table->foreign('id_livraison')->references('id_livraison')->on('livraisons_usine')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index('id_cooperative');
            $table->index('id_livraison');
            $table->index('date_paiement');
            $table->index('statut');
            $table->index(['id_cooperative', 'statut']);
            $table->index(['id_cooperative', 'date_paiement']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements_cooperative_usine');
    }
};