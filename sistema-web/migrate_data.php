<?php

use Illuminate\Support\Facades\DB;
// Script para migrar datos de MySQL a PostgreSQL
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MIGRACION DE DATOS MySQL -> PostgreSQL ===\n\n";

// Configurar conexión MySQL
config(['database.connections.mysql_source' => [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'proyecto_final_sw1',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]]);

// Configurar conexión PostgreSQL
config(['database.connections.pgsql_target' => [
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'port' => '5432',
    'database' => 'restaurante',
    'username' => 'postgres',
    'password' => '',
    'charset' => 'utf8',
]]);

try {
    // Tablas a migrar (mismo nombre en ambas BD)
    $tables = [
        'usuarios',
        'unidad_medidas',
        'categorias',
        'proveedores',
        'insumos',
        'recetas',
        'recetas_insumos',
        'ventas',
        'compras',
        'movimiento_inventarios',
        'planes_produccion',
        'reportes_personalizados',
        'historial',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
    ];

    foreach ($tables as $tableName) {
        echo "Migrando tabla: $tableName...\n";

        // LIMPIAR la tabla en PostgreSQL primero
        DB::connection('pgsql_target')->table($tableName)->truncate();
        echo "  🧹 Tabla limpiada\n";

        // Leer datos de MySQL
        $data = DB::connection('mysql_source')->table($tableName)->get();

        if ($data->isEmpty()) {
            echo "  ⚠️  Tabla $tableName está vacía en MySQL\n";
            continue;
        }

        echo "  📊 Encontrados " . count($data) . " registros\n";

        // Insertar en PostgreSQL
        foreach ($data as $row) {
            DB::connection('pgsql_target')->table($tableName)->insert((array)$row);
        }

        echo "  ✅ Migrados " . count($data) . " registros\n\n";
    }

    echo "\n🎉 MIGRACION COMPLETADA! 🎉\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
