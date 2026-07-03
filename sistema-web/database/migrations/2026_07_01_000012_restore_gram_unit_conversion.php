<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up():void
    {
        $gramo=DB::table('unidad_medidas')->whereRaw('LOWER(nombre)=?',['gramo'])->first();
        if(!$gramo){$id=DB::table('unidad_medidas')->insertGetId(['nombre'=>'Gramo','abreviatura'=>'g','descripcion'=>'Unidad métrica de masa','created_at'=>now(),'updated_at'=>now()]);$gramo=DB::table('unidad_medidas')->find($id);}
        $kg=DB::table('unidad_medidas')->whereRaw('LOWER(nombre)=?',['kilogramo'])->first();
        if($kg)DB::table('conversiones_unidades')->updateOrInsert(['unidad_origen_id'=>$gramo->id,'unidad_destino_id'=>$kg->id],['factor_conversion'=>0.001,'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down():void{}
};
