<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumo_presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->decimal('contenido', 12, 4)->nullable();
            $table->foreignId('unidad_contenido_id')->nullable()->constrained('unidad_medidas')->nullOnDelete();
            $table->string('tipo_envase', 60)->nullable();
            $table->boolean('retornable')->default(false);
            $table->string('codigo_barras', 100)->nullable()->unique();
            $table->boolean('predeterminada')->default(false);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        foreach (DB::table('insumos')->orderBy('id')->get() as $insumo) {
            DB::table('insumo_presentaciones')->insert([
                'insumo_id'=>$insumo->id,
                'nombre'=>in_array($insumo->tipo_uso ?? 'indirecto',['directo','mixto'])?'Presentación general':'A granel',
                'unidad_contenido_id'=>$insumo->unidad_medida_id,
                'predeterminada'=>true,'activa'=>true,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }

        foreach (['compra_lineas','movimiento_inventarios','ventas','consumos'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('presentacion_id')->nullable()->constrained('insumo_presentaciones')->restrictOnDelete();
            });
            DB::statement("UPDATE {$tabla} t SET presentacion_id = p.id FROM insumo_presentaciones p WHERE p.insumo_id = t.insumo_id AND p.predeterminada = true");
        }

        Schema::create('menus_dia_presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_dia_id')->constrained('menus_dia')->cascadeOnDelete();
            $table->foreignId('presentacion_id')->constrained('insumo_presentaciones')->restrictOnDelete();
            $table->decimal('precio_venta',12,2);
            $table->unsignedInteger('cantidad_inicial');
            $table->unsignedInteger('cantidad');
            $table->timestamps();
            $table->unique(['menu_dia_id','presentacion_id']);
        });

        DB::statement('INSERT INTO menus_dia_presentaciones (menu_dia_id,presentacion_id,precio_venta,cantidad_inicial,cantidad,created_at,updated_at)
            SELECT mdi.menu_dia_id,p.id,COALESCE(mdi.precio_venta,0),mdi.cantidad_inicial,mdi.cantidad,mdi.created_at,mdi.updated_at
            FROM menus_dia_insumos mdi JOIN insumo_presentaciones p ON p.insumo_id=mdi.insumo_id AND p.predeterminada=true');
    }

    public function down(): void
    {
        // Se preservan las presentaciones y su historial operativo.
    }
};
