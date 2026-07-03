<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up():void
    {
        Schema::table('compra_lineas',function(Blueprint $t){$t->foreignId('formato_empaque_id')->nullable()->after('presentacion_id')->constrained('formatos_empaque')->nullOnDelete();$t->json('estructura_empaque')->nullable()->after('formato_empaque_id');});
        if(Schema::hasColumn('compra_lineas','presentacion_empaque_id')){
            $lineas=DB::table('compra_lineas as l')->join('presentacion_empaques as e','e.id','=','l.presentacion_empaque_id')->join('insumo_presentaciones as p','p.id','=','l.presentacion_id')->select('l.id','l.cantidad_contenido','l.factor_compra_base','e.formato_empaque_id','e.nombre','e.cantidad_contenida','p.formato_empaque_id as formato_interior_id')->get();
            foreach($lineas as $linea){$estructura=[['cantidad'=>(float)($linea->cantidad_contenido?:$linea->factor_compra_base?:$linea->cantidad_contenida),'formato_empaque_id'=>$linea->formato_interior_id,'descripcion'=>$linea->nombre]];DB::table('compra_lineas')->where('id',$linea->id)->update(['formato_empaque_id'=>$linea->formato_empaque_id,'estructura_empaque'=>json_encode($estructura)]);}
            Schema::table('compra_lineas',fn(Blueprint $t)=>$t->dropConstrainedForeignId('presentacion_empaque_id'));
        }
        Schema::dropIfExists('presentacion_empaques');
    }
    public function down():void
    {
        Schema::create('presentacion_empaques',function(Blueprint $t){$t->id();$t->foreignId('presentacion_id')->constrained('insumo_presentaciones')->cascadeOnDelete();$t->foreignId('formato_empaque_id')->constrained('formatos_empaque')->restrictOnDelete();$t->string('nombre',100);$t->foreignId('contenido_empaque_id')->nullable()->constrained('presentacion_empaques')->nullOnDelete();$t->decimal('cantidad_contenida',14,4);$t->decimal('factor_base',14,4);$t->boolean('activo')->default(true);$t->timestamps();});
        Schema::table('compra_lineas',fn(Blueprint $t)=>$t->foreignId('presentacion_empaque_id')->nullable()->after('presentacion_id')->constrained('presentacion_empaques')->nullOnDelete());
        Schema::table('compra_lineas',function(Blueprint $t){$t->dropConstrainedForeignId('formato_empaque_id');$t->dropColumn('estructura_empaque');});
    }
};
