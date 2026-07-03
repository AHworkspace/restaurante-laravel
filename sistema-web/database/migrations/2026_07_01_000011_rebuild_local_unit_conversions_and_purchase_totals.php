<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up():void
    {
        $unidades=DB::table('unidad_medidas')->get()->keyBy(fn($u)=>mb_strtolower($u->nombre));
        DB::statement('ALTER TABLE conversiones_unidades ALTER COLUMN factor_conversion TYPE NUMERIC(18,8)');
        DB::table('conversiones_unidades')->delete();
        $conversiones=[
            ['Gramo','Kilogramo',0.001],['Miligramo','Kilogramo',0.000001],
            ['Libra','Kilogramo',0.48],['Onza','Kilogramo',0.03],
            ['Cuartilla','Kilogramo',3],['Arroba','Kilogramo',12],
            ['Quintal','Kilogramo',48],['Tonelada','Kilogramo',1000],
            ['Mililitro','Litro',0.001],['Centimetro','Metro',0.01],
            ['Docena','Unidad',12],
        ];
        foreach($conversiones as [$origen,$destino,$factor]){$o=$unidades->get(mb_strtolower($origen));$d=$unidades->get(mb_strtolower($destino));if($o&&$d)DB::table('conversiones_unidades')->insert(['unidad_origen_id'=>$o->id,'unidad_destino_id'=>$d->id,'factor_conversion'=>$factor,'created_at'=>now(),'updated_at'=>now()]);}

        DB::statement("UPDATE compra_lineas SET costo_linea=ROUND((cantidad_pedida*precio_unitario)::numeric,2) WHERE formato_empaque_id IS NOT NULL OR (estructura_empaque IS NOT NULL AND estructura_empaque::text NOT IN ('[]','null'))");
        DB::statement('UPDATE compras c SET costo_total=x.total FROM (SELECT compra_id,ROUND(SUM(costo_linea)::numeric,2) total FROM compra_lineas GROUP BY compra_id) x WHERE x.compra_id=c.id');
        DB::statement('UPDATE insumo_presentaciones p SET costo_estandar=x.costo FROM (SELECT presentacion_id,SUM(costo_linea*(cantidad_recibida_base/NULLIF(cantidad_pedida_base,0)))/NULLIF(SUM(cantidad_recibida_base),0) costo FROM compra_lineas WHERE presentacion_id IS NOT NULL AND cantidad_recibida_base>0 AND cantidad_pedida_base>0 GROUP BY presentacion_id) x WHERE p.id=x.presentacion_id');
    }
    public function down():void{}
};
