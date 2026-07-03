<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('recetas_insumos',fn(Blueprint $t)=>$t->foreignId('presentacion_id')->nullable()->after('insumo_id')->constrained('insumo_presentaciones')->nullOnDelete());DB::statement('UPDATE recetas_insumos ri SET presentacion_id=p.id FROM insumo_presentaciones p WHERE p.insumo_id=ri.insumo_id AND p.predeterminada=true');}
 public function down():void{Schema::table('recetas_insumos',fn(Blueprint $t)=>$t->dropConstrainedForeignId('presentacion_id'));}
};
