<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumo_presentaciones',fn(Blueprint $table)=>$table->decimal('stock_minimo',14,4)->default(0)->after('unidad_stock_id'));
        DB::statement('UPDATE insumo_presentaciones p SET stock_minimo = i.stock_minimo FROM insumos i WHERE p.insumo_id = i.id AND p.predeterminada = true');
    }
    public function down(): void{Schema::table('insumo_presentaciones',fn(Blueprint $table)=>$table->dropColumn('stock_minimo'));}
};
