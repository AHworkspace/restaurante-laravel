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
        Schema::create('configuraciones_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $todos = collect(config('system_permissions'))->flatMap(
            fn ($subsectores) => collect($subsectores)->flatMap(fn ($permisos) => array_keys($permisos))
        )->values();
        $todos->each(fn ($nombre) => Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']));

        $conPrefijos = fn (array $prefijos) => $todos->filter(fn ($p) => collect($prefijos)->contains(fn ($prefijo) => $p === $prefijo || str_starts_with($p, $prefijo.'.')));
        $asignaciones = [
            'director' => $todos->reject(fn ($p) => $p === 'predicciones.ver' || (str_starts_with($p, 'usuarios.') && $p !== 'usuarios.ver') || (str_starts_with($p, 'recetas.') && $p !== 'recetas.ver')),
            'cajero' => $conPrefijos(['dashboard','consumidores','ventas','consumos','pagos','reportes','historial']),
            'cocinero' => $conPrefijos(['dashboard','insumos','categorias_insumos','unidades','alertas','movimientos','recetas','menus']),
            'ayudante_cocina' => $conPrefijos(['dashboard','alertas','movimientos'])->merge($todos->filter(fn ($p) => in_array($p, ['insumos.ver','categorias_insumos.ver','unidades.ver'], true))),
            'mesero' => collect(['dashboard.ver','recetas.ver']),
        ];
        Role::where('name', 'admin')->first()?->syncPermissions($todos);
        foreach ($asignaciones as $rol => $permisos) Role::where('name', $rol)->first()?->syncPermissions($permisos);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_sistema');
    }
};
