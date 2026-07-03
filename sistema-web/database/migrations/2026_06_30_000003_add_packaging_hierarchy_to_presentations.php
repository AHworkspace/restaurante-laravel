<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumo_presentaciones', function (Blueprint $table) {
            $table->foreignId('unidad_stock_id')->nullable()->after('unidad_contenido_id')->constrained('unidad_medidas')->nullOnDelete();
            $table->foreignId('unidad_empaque_id')->nullable()->after('unidad_stock_id')->constrained('unidad_medidas')->nullOnDelete();
            $table->decimal('unidades_por_empaque', 14, 6)->nullable()->after('unidad_empaque_id');
        });
        if (! Schema::hasColumn('compra_lineas', 'cantidad_suelta')) {
            Schema::table('compra_lineas', function (Blueprint $table) {
                $table->decimal('cantidad_suelta', 14, 6)->default(0)->after('cantidad_contenido');
            });
        }
    }

    public function down(): void
    {
        Schema::table('insumo_presentaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_empaque_id');
            $table->dropConstrainedForeignId('unidad_stock_id');
            $table->dropColumn('unidades_por_empaque');
        });
    }
};
