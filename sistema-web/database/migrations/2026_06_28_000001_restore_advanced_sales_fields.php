<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (! Schema::hasColumn('ventas', 'consumidor_id')) $table->foreignId('consumidor_id')->nullable()->constrained('consumidores')->nullOnDelete();
            if (! Schema::hasColumn('ventas', 'tipo_comida_id')) $table->foreignId('tipo_comida_id')->nullable()->constrained('tipos_comida')->nullOnDelete();
            if (! Schema::hasColumn('ventas', 'fecha_venta')) $table->date('fecha_venta')->nullable();
            if (! Schema::hasColumn('ventas', 'hora_venta')) $table->time('hora_venta')->nullable();
            if (! Schema::hasColumn('ventas', 'observaciones')) $table->text('observaciones')->nullable();
        });
    }

    public function down(): void
    {
        // Recovered sales data is intentionally preserved.
    }
};
