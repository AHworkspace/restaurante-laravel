<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestDbConnection extends Command
{
    protected $signature = 'test:db';
    protected $description = 'Test database connection and ventas table';

    public function handle()
    {
        $this->info('Probando conexión a la base de datos...');

        try {
            DB::connection()->getPdo();
            $this->info('✓ Conexión exitosa a la base de datos');
            
            // Verificar si la tabla existe
            if (Schema::hasTable('ventas')) {
                $this->info('✓ La tabla ventas existe');
                
                // Contar registros
                $count = DB::table('ventas')->count();
                $this->info("✓ Total de registros en ventas: {$count}");
                
                if ($count > 0) {
                    // Mostrar el primer registro
                    $firstRecord = DB::table('ventas')->first();
                    $this->info('Primer registro:');
                    $this->table(
                        ['ID', 'Cantidad', 'Precio', 'Total', 'Receta ID', 'Creado'],
                        [[
                            $firstRecord->id,
                            $firstRecord->cantidad,
                            $firstRecord->precio,
                            $firstRecord->total,
                            $firstRecord->receta_id,
                            $firstRecord->created_at
                        ]]
                    );
                }
            } else {
                $this->error('✗ La tabla ventas no existe');
            }
            
        } catch (\Exception $e) {
            $this->error('Error de conexión: ' . $e->getMessage());
        }
    }
} 