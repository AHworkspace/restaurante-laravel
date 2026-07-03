<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->decimal('cantidad_contenido', 14, 6)->nullable()->after('factor_compra_base');
            $table->foreignId('unidad_contenido_id')->nullable()->after('cantidad_contenido')->constrained('unidad_medidas')->nullOnDelete();
            $table->foreignId('unidad_inventario_id')->nullable()->after('unidad_contenido_id')->constrained('unidad_medidas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_inventario_id');
            $table->dropConstrainedForeignId('unidad_contenido_id');
            $table->dropColumn('cantidad_contenido');
        });
    }
};
