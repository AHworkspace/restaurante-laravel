<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarDatosPostgres extends Command
{
    protected $signature = 'db:migrar-mysql-postgres';
    protected $description = 'Migra todos los datos de MySQL a PostgreSQL';

    public function handle()
    {
        $this->info('=== INICIANDO MIGRACION ===');

        // Configurar MySQL source
        config(['database.connections.mysql_source' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'proyecto_final_sw1',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ]]);

        $tablas = [
            'usuarios', 'unidad_medidas', 'categorias', 'proveedores', 'insumos',
            'recetas', 'recetas_insumos', 'ventas', 'compras', 'movimiento_inventarios',
            'planes_produccion', 'reportes_personalizados', 'historial',
            'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'
        ];

        foreach ($tablas as $tabla) {
            $this->info("Migrando: $tabla");

            try {
                // Limpiar tabla PostgreSQL
                DB::table($tabla)->truncate();

                // Leer de MySQL
                $datos = DB::connection('mysql_source')->table($tabla)->get();

                if ($datos->isEmpty()) {
                    $this->warn("  Tabla $tabla vacia");
                    continue;
                }

                // Insertar en PostgreSQL uno por uno
                foreach ($datos as $dato) {
                    DB::table($tabla)->insert((array) $dato);
                }

                $this->info("  ✅ {$datos->count()} registros");

            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info('🎉 MIGRACION COMPLETADA 🎉');

        return 0;
    }
}

