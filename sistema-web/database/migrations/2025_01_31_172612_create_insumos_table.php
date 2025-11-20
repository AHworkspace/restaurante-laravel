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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('descripcion', 100)->nullable();
            $table->float('stock_minimo');
            $table->string('imagen', 100)->nullable();
            // $table->foreignId('restaurante_id')->constrained('restaurantes');
            $table->foreignId('unidad_medida_id')->constrained('unidad_medidas');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
