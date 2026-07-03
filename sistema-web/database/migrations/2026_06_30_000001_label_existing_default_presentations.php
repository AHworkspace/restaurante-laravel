<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up():void
    {
        $filas=DB::table('insumo_presentaciones as p')->join('insumos as i','i.id','=','p.insumo_id')->join('unidad_medidas as u','u.id','=','i.unidad_medida_id')->where('p.predeterminada',true)->whereIn('p.nombre',['A granel','Presentación general'])->select('p.id','i.tipo_uso','u.abreviatura')->get();
        foreach($filas as $fila){$nombre=in_array($fila->tipo_uso,['directo','mixto'])?'Unidad estándar':'A granel ('.$fila->abreviatura.')';DB::table('insumo_presentaciones')->where('id',$fila->id)->update(['nombre'=>$nombre,'updated_at'=>now()]);}
    }
    public function down():void{}
};
