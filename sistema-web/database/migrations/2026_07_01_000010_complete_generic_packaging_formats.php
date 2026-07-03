<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up():void
    {
        foreach(['Barril','Bulto','Bidón','Malla','Sachet','Cartón','Envase','Porción'] as $nombre){$unidad=DB::table('unidad_medidas')->whereRaw('LOWER(nombre)=?',[mb_strtolower($nombre)])->first();DB::table('formatos_empaque')->updateOrInsert(['nombre'=>$nombre],['descripcion'=>null,'unidad_medida_id'=>$unidad?->id,'es_granel'=>false,'activo'=>true,'updated_at'=>now(),'created_at'=>now()]);}
    }
    public function down():void{}
};
