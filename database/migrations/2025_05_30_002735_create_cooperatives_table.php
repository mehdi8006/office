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
            $table->string('nom_cooperative');
            $table->text('adresse');
            $table->string('telephone');
            $table->string('email')->unique();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('nom_cooperative');
            $table->index('email');
            $table->index('statut');
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