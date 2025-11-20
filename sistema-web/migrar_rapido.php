<?php
// Migración rápida de MySQL a PostgreSQL
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MIGRACION RAPIDA MySQL -> PostgreSQL ===\n";

// Temporalmente cambiar a MySQL para leer datos
$mysqlConfig = [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'proyecto_final_sw1',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

$pgsqlConfig = [
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'port' => '5432',
    'database' => 'restaurante',
    'username' => 'postgres',
    'password' => '',
    'charset' => 'utf8',
];

try {
    // Conectar a MySQL
    $mysqlPdo = new PDO(
        "mysql:host={$mysqlConfig['host']};dbname={$mysqlConfig['database']};charset=utf8mb4",
        $mysqlConfig['username'],
        $mysqlConfig['password']
    );

    // Conectar a PostgreSQL
    $pgPdo = new PDO(
        "pgsql:host={$pgsqlConfig['host']};port={$pgsqlConfig['port']};dbname={$pgsqlConfig['database']}",
        $pgsqlConfig['username'],
        $pgsqlConfig['password']
    );

    echo "✅ Conexiones establecidas\n\n";

    // Tablas a migrar
    $tablas = [
        'usuarios', 'unidad_medidas', 'categorias', 'proveedores', 'insumos',
        'recetas', 'recetas_insumos', 'ventas', 'compras', 'movimiento_inventarios',
        'planes_produccion', 'reportes_personalizados', 'historial',
        'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'
    ];

    foreach ($tablas as $tabla) {
        echo "📦 $tabla: ";

        // Limpiar tabla en PostgreSQL
        $pgPdo->exec("TRUNCATE TABLE $tabla RESTART IDENTITY CASCADE");

        // Leer de MySQL
        $stmt = $mysqlPdo->query("SELECT * FROM $tabla");
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($datos)) {
            echo "vacía\n";
            continue;
        }

        // Insertar en PostgreSQL
        foreach ($datos as $fila) {
            $columnas = array_keys($fila);
            $valores = array_values($fila);

            $cols = '"' . implode('", "', $columnas) . '"';
            $placeholders = implode(', ', array_fill(0, count($valores), '?'));

            $sql = "INSERT INTO $tabla ($cols) VALUES ($placeholders)";
            $stmtInsert = $pgPdo->prepare($sql);
            $stmtInsert->execute($valores);
        }

        echo count($datos) . " registros migrados ✅\n";
    }

    echo "\n🎉 MIGRACION COMPLETADA! 🎉\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

