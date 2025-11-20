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
        //
        Schema::create('recetas_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas');
            $table->foreignId('insumo_id')->constrained('insumos');
            $table->integer('cantidad');
            // agregar desperdicios
            $table->integer('desperdicio')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('recetas_insumos');
    }
};
