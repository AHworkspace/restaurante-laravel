<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus_dia_recetas', function (Blueprint $table) {
            if (! Schema::hasColumn('menus_dia_recetas', 'precio_venta')) {
                $table->decimal('precio_venta', 12, 2)->nullable()->after('receta_id');
            }
        });

        DB::statement('UPDATE menus_dia_recetas mdr SET precio_venta = r.precio FROM recetas r WHERE r.id = mdr.receta_id AND mdr.precio_venta IS NULL');
    }

    public function down(): void
    {
        Schema::table('menus_dia_recetas', function (Blueprint $table) {
            if (Schema::hasColumn('menus_dia_recetas', 'precio_venta')) {
                $table->dropColumn('precio_venta');
            }
        });
    }
};
