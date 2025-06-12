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
        Schema::create('prix_unitaires', function (Blueprint $table) {
            $table->id();
            $table->decimal('prix', 8, 2); // Prix unitaire avec 2 décimales
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('prix');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prix_unitaires');
    }
};