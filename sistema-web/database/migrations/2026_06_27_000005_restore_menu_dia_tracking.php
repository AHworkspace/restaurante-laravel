<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus_dia')) {
            Schema::create('menus_dia', function (Blueprint $table) {
                $table->id();
                $table->string('titulo')->default('Menu del Dia');
                $table->foreignId('tipo_comida_id')->nullable()->constrained('tipos_comida')->nullOnDelete();
                $table->date('fecha');
                $table->time('hora_inicio')->nullable();
                $table->time('hora_fin')->nullable();
                $table->boolean('visible_para_clientes')->default(false);
                $table->boolean('visible_en_horario')->default(false);
                $table->boolean('activo')->default(true);
                $table->text('descripcion')->nullable();
                $table->foreignId('usuario_creador_id')->nullable()->constrained('usuarios')->nullOnDelete();
                $table->json('historial')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('menus_dia_recetas')) {
            Schema::create('menus_dia_recetas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_dia_id')->constrained('menus_dia')->cascadeOnDelete();
                $table->foreignId('receta_id')->constrained('recetas')->cascadeOnDelete();
                $table->unsignedInteger('cantidad')->default(1);
                $table->unsignedInteger('cantidad_inicial')->default(1);
                $table->timestamps();
                $table->unique(['menu_dia_id', 'receta_id']);
            });
        }

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
