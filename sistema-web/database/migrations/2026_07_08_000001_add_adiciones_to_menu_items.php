<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['menus_dia_recetas', 'menus_dia_presentaciones'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (! Schema::hasColumn($tabla, 'adiciones')) {
                    $table->json('adiciones')->nullable()->after('cantidad_inicial');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['menus_dia_recetas', 'menus_dia_presentaciones'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (Schema::hasColumn($tabla, 'adiciones')) {
                    $table->dropColumn('adiciones');
                }
            });
        }
    }
};
