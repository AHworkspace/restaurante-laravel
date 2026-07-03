<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{
  Schema::table('insumo_presentaciones',function(Blueprint $t){$t->string('descripcion',255)->nullable()->after('nombre');$t->foreignId('categoria_id')->nullable()->after('descripcion')->constrained('categorias')->nullOnDelete();$t->string('tipo_uso',20)->default('indirecto')->after('categoria_id');});
  DB::statement('UPDATE insumo_presentaciones p SET descripcion=i.descripcion,categoria_id=i.categoria_id,tipo_uso=i.tipo_uso,unidad_stock_id=COALESCE(p.unidad_stock_id,i.unidad_medida_id) FROM insumos i WHERE p.insumo_id=i.id');
 }
 public function down():void{Schema::table('insumo_presentaciones',function(Blueprint $t){$t->dropConstrainedForeignId('categoria_id');$t->dropColumn(['descripcion','tipo_uso']);});}
};
