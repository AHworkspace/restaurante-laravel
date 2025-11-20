<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ARREGLANDO SECUENCIAS ===\n\n";

$tablas = [
    'usuarios', 'unidad_medidas', 'categorias', 'proveedores', 'insumos',
    'recetas', 'recetas_insumos', 'ventas', 'compras', 'movimiento_inventarios',
    'planes_produccion', 'reportes_personalizados', 'historial',
    'roles', 'permissions', 'model_has_roles', 'model_has_permissions'
];

foreach($tablas as $tabla) {
    try {
        $max = DB::table($tabla)->max('id');

        if ($max) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$tabla', 'id'), $max);");
            echo "$tabla: secuencia actualizada a $max\n";
        } else {
            echo "$tabla: vacía\n";
        }
    } catch(Exception $e) {
        echo "$tabla: sin secuencia o error\n";
    }
}

echo "\n✅ SECUENCIAS ARREGLADAS\n";

