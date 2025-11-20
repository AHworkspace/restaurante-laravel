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
        Schema::create('conversiones_unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_origen_id')->constrained('unidad_medidas')->onDelete('cascade');
            $table->foreignId('unidad_destino_id')->constrained('unidad_medidas')->onDelete('cascade');
            $table->decimal('factor_conversion', 10, 4)->comment('Factor para convertir de origen a destino');
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['unidad_origen_id', 'unidad_destino_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversiones_unidades');
    }
};
