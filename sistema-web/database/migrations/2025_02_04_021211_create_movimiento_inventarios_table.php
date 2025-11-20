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
        Schema::create('movimiento_inventarios', function (Blueprint $table) {
            $table->id();
            $table->float('cantidad');
            $table->string('tipo'); // entrada o salida
            $table->string('motivo'); // compra, venta, ajuste, etc
            $table->foreignId('insumo_id')->constrained('insumos');
            // relacion con venta
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            // relacion con compra
            $table->foreignId('compra_id')->nullable()->constrained('compras');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventarios');
    }
};
