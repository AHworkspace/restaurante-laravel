<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up():void{
  DB::statement('UPDATE insumo_presentaciones p SET costo_estandar=x.costo FROM (SELECT presentacion_id, SUM(costo_linea*(cantidad_recibida_base/NULLIF(cantidad_pedida_base,0)))/NULLIF(SUM(cantidad_recibida_base),0) costo FROM compra_lineas WHERE presentacion_id IS NOT NULL AND cantidad_recibida_base>0 AND cantidad_pedida_base>0 GROUP BY presentacion_id) x WHERE p.id=x.presentacion_id');
 }
 public function down():void{}
};
