<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up():void
    {
        Schema::create('formatos_empaque',function(Blueprint $t){$t->id();$t->string('nombre',80)->unique();$t->string('descripcion')->nullable();$t->foreignId('unidad_medida_id')->nullable()->constrained('unidad_medidas')->nullOnDelete();$t->boolean('es_granel')->default(false);$t->boolean('activo')->default(true);$t->timestamps();});
        foreach(['A granel','Bolsa','Botella','Lata','Caja','Paquete','Saco','Frasco','Bandeja','Unidad'] as $nombre){$unidad=DB::table('unidad_medidas')->whereRaw('LOWER(nombre)=?', [mb_strtolower($nombre)])->first();DB::table('formatos_empaque')->insert(['nombre'=>$nombre,'unidad_medida_id'=>$unidad?->id,'es_granel'=>$nombre==='A granel','activo'=>true,'created_at'=>now(),'updated_at'=>now()]);}
        Schema::table('insumo_presentaciones',fn(Blueprint $t)=>$t->foreignId('formato_empaque_id')->nullable()->after('tipo_envase')->constrained('formatos_empaque')->nullOnDelete());
        DB::statement("UPDATE insumo_presentaciones p SET formato_empaque_id=f.id FROM formatos_empaque f WHERE LOWER(f.nombre)=LOWER(p.tipo_envase)");
        DB::statement("UPDATE insumo_presentaciones p SET formato_empaque_id=f.id FROM formatos_empaque f WHERE f.es_granel=true AND p.formato_empaque_id IS NULL AND LOWER(COALESCE(p.tipo_envase,'')) LIKE '%granel%'");
        Schema::create('presentacion_empaques',function(Blueprint $t){$t->id();$t->foreignId('presentacion_id')->constrained('insumo_presentaciones')->cascadeOnDelete();$t->foreignId('formato_empaque_id')->constrained('formatos_empaque')->restrictOnDelete();$t->string('nombre',100);$t->foreignId('contenido_empaque_id')->nullable()->constrained('presentacion_empaques')->nullOnDelete();$t->decimal('cantidad_contenida',14,4);$t->decimal('factor_base',14,4);$t->boolean('activo')->default(true);$t->timestamps();});
        $anteriores=DB::table('insumo_presentaciones')->whereNotNull('unidad_empaque_id')->whereNotNull('unidades_por_empaque')->get();
        foreach($anteriores as $p){$unidad=DB::table('unidad_medidas')->find($p->unidad_empaque_id);if(!$unidad)continue;$formato=DB::table('formatos_empaque')->whereRaw('LOWER(nombre)=?',[mb_strtolower($unidad->nombre)])->first();if(!$formato){$id=DB::table('formatos_empaque')->insertGetId(['nombre'=>$unidad->nombre,'unidad_medida_id'=>$unidad->id,'activo'=>true,'es_granel'=>false,'created_at'=>now(),'updated_at'=>now()]);}else{$id=$formato->id;}DB::table('presentacion_empaques')->insert(['presentacion_id'=>$p->id,'formato_empaque_id'=>$id,'nombre'=>$unidad->nombre.' de '.rtrim(rtrim(number_format((float)$p->unidades_por_empaque,4,'.',''),'0'),'.'),'cantidad_contenida'=>$p->unidades_por_empaque,'factor_base'=>$p->unidades_por_empaque,'activo'=>true,'created_at'=>now(),'updated_at'=>now()]);}
        Schema::table('compra_lineas',fn(Blueprint $t)=>$t->foreignId('presentacion_empaque_id')->nullable()->after('presentacion_id')->constrained('presentacion_empaques')->nullOnDelete());
        foreach(['formatos_empaque.ver','formatos_empaque.crear','formatos_empaque.editar','formatos_empaque.eliminar'] as $nombre)Permission::firstOrCreate(['name'=>$nombre,'guard_name'=>'web']);
        $admin=Role::where('name','admin')->first();if($admin)$admin->givePermissionTo(['formatos_empaque.ver','formatos_empaque.crear','formatos_empaque.editar','formatos_empaque.eliminar']);
    }
    public function down():void{Schema::table('compra_lineas',fn(Blueprint $t)=>$t->dropConstrainedForeignId('presentacion_empaque_id'));Schema::dropIfExists('presentacion_empaques');Schema::table('insumo_presentaciones',fn(Blueprint $t)=>$t->dropConstrainedForeignId('formato_empaque_id'));Schema::dropIfExists('formatos_empaque');}
};
