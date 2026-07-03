<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->decimal('cantidad_suelta', 14, 6)->default(0)->after('cantidad_contenido');
            $table->decimal('cantidad_recibida_base', 14, 6)->default(0)->after('cantidad_recibida');
        });
        Schema::table('movimiento_inventarios', function (Blueprint $table) {
            $table->decimal('cantidad_suelta', 14, 6)->default(0)->after('cantidad_original');
            $table->foreignId('unidad_inventario_id')->nullable()->after('unidad_medida_id')->constrained('unidad_medidas')->nullOnDelete();
        });
        DB::table('compra_lineas')->update([
            'cantidad_recibida_base' => DB::raw('cantidad_recibida * COALESCE(factor_compra_base, 1)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('movimiento_inventarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_inventario_id');
            $table->dropColumn('cantidad_suelta');
        });
        Schema::table('compra_lineas', fn (Blueprint $table) => $table->dropColumn(['cantidad_suelta', 'cantidad_recibida_base']));
    }
};
