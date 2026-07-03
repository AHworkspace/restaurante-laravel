<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            if (! Schema::hasColumn('compras', 'monto_pagado_proveedor')) $table->decimal('monto_pagado_proveedor', 12, 2)->default(0);
            if (! Schema::hasColumn('compras', 'insumo_id')) $table->foreignId('insumo_id')->nullable()->constrained('insumos')->nullOnDelete();
            if (! Schema::hasColumn('compras', 'cantidad_pedida')) $table->decimal('cantidad_pedida', 12, 4)->nullable();
            if (! Schema::hasColumn('compras', 'cantidad_recibida')) $table->decimal('cantidad_recibida', 12, 4)->default(0);
            if (! Schema::hasColumn('compras', 'estado')) $table->string('estado', 30)->default('pendiente');
            if (! Schema::hasColumn('compras', 'numero_documento')) $table->string('numero_documento', 100)->nullable();
            if (! Schema::hasColumn('compras', 'fecha_compra')) $table->date('fecha_compra')->nullable();
        });

        if (! Schema::hasTable('compra_lineas')) Schema::create('compra_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->restrictOnDelete();
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidad_medidas')->nullOnDelete();
            $table->decimal('cantidad_pedida', 12, 4);
            $table->decimal('cantidad_pedida_base', 12, 4);
            $table->decimal('cantidad_recibida', 12, 4)->default(0);
            $table->decimal('costo_linea', 12, 2);
            $table->string('etiqueta_manual', 30)->nullable();
            $table->boolean('linea_pagada')->default(false);
            $table->boolean('suma_al_total')->default(true);
            $table->timestamps();
        });

        if (! Schema::hasTable('compra_proveedor_pagos')) Schema::create('compra_proveedor_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->decimal('monto', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Recovered purchase data is preserved.
    }
};
