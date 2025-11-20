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
        Schema::table('movimiento_inventarios', function (Blueprint $table) {
            $table->foreignId('unidad_medida_id')->nullable()->after('insumo_id')->constrained('unidad_medidas');
            $table->decimal('cantidad_original', 10, 4)->nullable()->after('cantidad')->comment('Cantidad ingresada en la unidad del movimiento');
            $table->decimal('cantidad_convertida', 10, 4)->nullable()->after('cantidad_original')->comment('Cantidad convertida a la unidad base del insumo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento_inventarios', function (Blueprint $table) {
            $table->dropForeign(['unidad_medida_id']);
            $table->dropColumn(['unidad_medida_id', 'cantidad_original', 'cantidad_convertida']);
        });
    }
};
