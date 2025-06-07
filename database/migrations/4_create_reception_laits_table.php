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
        Schema::create('receptions_lait', function (Blueprint $table) {
            $table->id('id_reception');
            $table->unsignedBigInteger('id_cooperative');
            $table->unsignedBigInteger('id_membre');
            $table->string('matricule_reception', 15)->unique();
            $table->date('date_reception');
            $table->decimal('quantite_litres', 8, 2); // Max 999,999.99 litres
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('id_cooperative')->references('id_cooperative')->on('cooperatives')->onDelete('cascade');
            $table->foreign('id_membre')->references('id_membre')->on('membres_eleveurs')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index('id_cooperative');
            $table->index('id_membre');
            $table->index('date_reception');
            $table->index('matricule_reception');
            $table->index(['id_cooperative', 'date_reception']);
            $table->index(['id_membre', 'date_reception']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receptions_lait');
    }
};