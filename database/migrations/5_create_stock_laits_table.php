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
        Schema::create('stock_lait', function (Blueprint $table) {
            $table->id('id_stock');
            $table->unsignedBigInteger('id_cooperative');
            $table->date('date_stock');
            $table->decimal('quantite_totale', 10, 2)->default(0); // Total reçu dans la journée
            $table->decimal('quantite_disponible', 10, 2)->default(0); // Disponible pour livraison
            $table->decimal('quantite_livree', 10, 2)->default(0); // Déjà livré
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('id_cooperative')->references('id_cooperative')->on('cooperatives')->onDelete('cascade');
            
            // Unique constraint: une seule ligne par coopérative par jour
            $table->unique(['id_cooperative', 'date_stock'], 'unique_cooperative_date_stock');
            
            // Add indexes for better performance
            $table->index('id_cooperative');
            $table->index('date_stock');
            $table->index(['id_cooperative', 'date_stock']);
            $table->index('created_at');
            
            // Index for queries on available quantity
            $table->index('quantite_disponible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_lait');
    }
};