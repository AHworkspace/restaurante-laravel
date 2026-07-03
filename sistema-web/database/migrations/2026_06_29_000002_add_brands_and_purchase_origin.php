<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('empresa_fabricante')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        Schema::create('marca_proveedor', function (Blueprint $table) {
            $table->foreignId('marca_id')->constrained('marcas')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->primary(['marca_id', 'proveedor_id']);
        });
        Schema::table('compra_lineas', function (Blueprint $table) {
            $table->foreignId('marca_id')->nullable()->after('insumo_id')->constrained('marcas')->nullOnDelete();
        });

        $permisos = collect(['marcas.ver','marcas.crear','marcas.editar','marcas.eliminar'])
            ->mapWithKeys(fn ($nombre) => [$nombre => Permission::firstOrCreate(['name'=>$nombre,'guard_name'=>'web'])]);
        foreach (['admin','director','cocinero'] as $rol) Role::where('name',$rol)->first()?->givePermissionTo($permisos->values());
        Role::where('name','ayudante_cocina')->first()?->givePermissionTo($permisos['marcas.ver']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('compra_lineas', fn (Blueprint $table) => $table->dropConstrainedForeignId('marca_id'));
        Schema::dropIfExists('marca_proveedor');
        Schema::dropIfExists('marcas');
    }
};
