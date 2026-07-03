<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus_dia_recetas', function (Blueprint $table) {
            if (! Schema::hasColumn('menus_dia_recetas', 'cantidad')) $table->unsignedInteger('cantidad')->default(1);
            if (! Schema::hasColumn('menus_dia_recetas', 'cantidad_inicial')) $table->unsignedInteger('cantidad_inicial')->default(1);
        });
        Schema::table('menus_dia', function (Blueprint $table) {
            if (! Schema::hasColumn('menus_dia', 'visible_en_horario')) $table->boolean('visible_en_horario')->default(false);
        });
        Schema::table('consumos', function (Blueprint $table) {
            if (! Schema::hasColumn('consumos', 'menu_dia_id')) $table->foreignId('menu_dia_id')->nullable()->constrained('menus_dia')->nullOnDelete();
            if (! Schema::hasColumn('consumos', 'cantidad_menu_descontada')) $table->unsignedInteger('cantidad_menu_descontada')->default(0);
        });
    }

    public function down(): void
    {
        // Recovered menu tracking is preserved.
    }
};
