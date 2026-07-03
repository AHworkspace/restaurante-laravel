<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->string('tipo_uso', 20)->default('indirecto');
            $table->boolean('vendible_menu')->default(false);
            $table->decimal('precio_venta', 12, 2)->nullable();
            $table->decimal('cantidad_base_por_venta', 12, 4)->default(1);
        });
        Schema::create('menus_dia_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_dia_id')->constrained('menus_dia')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->restrictOnDelete();
            $table->unsignedInteger('cantidad_inicial');
            $table->unsignedInteger('cantidad');
            $table->timestamps();
            $table->unique(['menu_dia_id', 'insumo_id']);
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('insumo_id')->nullable()->after('receta_id')->constrained('insumos')->restrictOnDelete();
        });
        Schema::table('consumos', function (Blueprint $table) {
            $table->foreignId('insumo_id')->nullable()->after('receta_id')->constrained('insumos')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE ventas ALTER COLUMN receta_id DROP NOT NULL');
        DB::statement('ALTER TABLE consumos ALTER COLUMN receta_id DROP NOT NULL');
    }

    public function down(): void
    {
        // La información de ventas directas se conserva.
    }
};
