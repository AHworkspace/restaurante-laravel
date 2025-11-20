<?php

/**
 * Sincroniza todos los datos desde la BD PostgreSQL remota (db_grupo12sc)
 * hacia la BD local configurada en .env (restaurante).
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Sincronización Postgres remota -> local ===\n";

$sourceConfig = [
    'host' => 'mail.tecnoweb.org.bo',
    'port' => '5432',
    'database' => 'db_grupo12sc',
    'username' => 'grupo12sc',
    'password' => 'grup012grup012*',
];

$targetConfig = [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'restaurante'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', ''),
];

function createPdo(array $config): PDO
{
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $config['host'],
        $config['port'],
        $config['database']
    );

    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

try {
    $sourcePdo = createPdo($sourceConfig);
    $targetPdo = createPdo($targetConfig);
    echo "✅ Conexiones establecidas.\n\n";
} catch (Exception $e) {
    echo "❌ No se pudo conectar: {$e->getMessage()}\n";
    exit(1);
}

$tablesStmt = $sourcePdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
$targetTablesStmt = $targetPdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
$targetTables = $targetTablesStmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    echo "⚠️ No se encontraron tablas para copiar.\n";
    exit(0);
}

// Desactivar temporalmente restricciones para evitar problemas de FK
$targetPdo->exec("SET session_replication_role = 'replica'");

foreach ($tables as $table) {
    // Algunas tablas del sistema conviene saltarlas
    if (in_array($table, ['spatial_ref_sys'], true)) {
        continue;
    }

    if (!in_array($table, $targetTables, true)) {
        echo "📦 Tabla {$table}: omitida (no existe en destino)\n";
        continue;
    }

    echo "📦 Tabla {$table}: ";

    $targetPdo->exec("TRUNCATE TABLE \"{$table}\" RESTART IDENTITY CASCADE");

    $data = $sourcePdo->query("SELECT * FROM \"{$table}\"")->fetchAll();

    if (empty($data)) {
        echo "vacía\n";
        continue;
    }

    $columns = array_keys($data[0]);
    $columnList = '"' . implode('","', $columns) . '"';
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $insertSql = "INSERT INTO \"{$table}\" ({$columnList}) VALUES ({$placeholders})";
    $insertStmt = $targetPdo->prepare($insertSql);

    foreach ($data as $row) {
        $insertStmt->execute(array_values($row));
    }

    echo count($data) . " registros copiados\n";

    // Ajustar secuencias si existe alguna asociada a columnas serial
    foreach ($columns as $column) {
        $sequenceStmt = $targetPdo->query("SELECT pg_get_serial_sequence('\"{$table}\"', '{$column}')");
        $sequence = $sequenceStmt->fetchColumn();

        if ($sequence) {
            $maxValueStmt = $targetPdo->query("SELECT MAX(\"{$column}\") FROM \"{$table}\"");
            $maxValue = $maxValueStmt->fetchColumn();

            if ($maxValue !== null) {
                $targetPdo->exec("SELECT setval('{$sequence}', {$maxValue})");
            }
        }
    }
}

$targetPdo->exec("SET session_replication_role = 'origin'");

echo "\n🎉 Sincronización completada exitosamente.\n";

