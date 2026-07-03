<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up():void
    {
        foreach(['ver','crear','editar','eliminar'] as $accion){$permiso=Permission::firstOrCreate(['name'=>'formatos_empaque.'.$accion,'guard_name'=>'web']);Role::with('permissions')->get()->filter(fn($rol)=>$rol->name==='admin'||$rol->hasPermissionTo('insumos.'.$accion))->each(fn($rol)=>$rol->givePermissionTo($permiso));}
    }
    public function down():void{}
};
