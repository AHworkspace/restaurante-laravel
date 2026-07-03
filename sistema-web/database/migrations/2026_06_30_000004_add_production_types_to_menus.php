<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipos_produccion', function (Blueprint $table) {
            $table->id();$table->string('nombre',80)->unique();$table->string('descripcion')->nullable();$table->unsignedInteger('orden')->default(0);$table->boolean('activo')->default(true);$table->timestamps();
        });
        foreach (['Sopas','Segundos','Bebidas','Postres','Extras'] as $orden=>$nombre) DB::table('tipos_produccion')->insert(['nombre'=>$nombre,'orden'=>$orden+1,'activo'=>true,'created_at'=>now(),'updated_at'=>now()]);
        foreach (['menus_dia_recetas','menus_dia_presentaciones'] as $tabla) Schema::table($tabla, fn(Blueprint $table)=>$table->foreignId('tipo_produccion_id')->nullable()->constrained('tipos_produccion')->nullOnDelete());
    }
    public function down(): void
    {
        foreach (['menus_dia_recetas','menus_dia_presentaciones'] as $tabla) Schema::table($tabla,fn(Blueprint $table)=>$table->dropConstrainedForeignId('tipo_produccion_id'));
        Schema::dropIfExists('tipos_produccion');
    }
};
