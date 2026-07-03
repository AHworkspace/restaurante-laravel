<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumo_presentaciones',fn(Blueprint $table)=>$table->decimal('costo_estandar',14,4)->nullable()->after('stock_minimo'));
        DB::statement('UPDATE insumo_presentaciones p SET costo_estandar = i.costo_estandar FROM insumos i WHERE p.insumo_id = i.id AND p.predeterminada = true');
    }
    public function down(): void{Schema::table('insumo_presentaciones',fn(Blueprint $table)=>$table->dropColumn('costo_estandar'));}
};
