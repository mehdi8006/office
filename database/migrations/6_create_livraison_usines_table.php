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
        Schema::create('livraisons_usine', function (Blueprint $table) {
            $table->id('id_livraison');
            $table->unsignedBigInteger('id_cooperative');
            $table->date('date_livraison');
            $table->decimal('quantite_litres', 10, 2);
            $table->decimal('prix_unitaire', 8, 2);
            $table->decimal('montant_total', 12, 2);
            $table->enum('statut', ['planifiee', 'validee', 'payee'])->default('planifiee');
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('id_cooperative')->references('id_cooperative')->on('cooperatives')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index('id_cooperative');
            $table->index('date_livraison');
            $table->index('statut');
            $table->index(['id_cooperative', 'date_livraison']);
            $table->index(['id_cooperative', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraisons_usine');
    }
};