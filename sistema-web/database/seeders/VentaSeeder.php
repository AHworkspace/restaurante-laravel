<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Venta;
use Carbon\Carbon;

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar registros existentes
        //Venta::truncate();

        // Crear ventas para la última semana
        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i);
            
            // 2-3 ventas por día
            $ventasPorDia = rand(2, 3);
            for ($j = 0; $j < $ventasPorDia; $j++) {
                $cantidad = rand(1, 10);
                $precio = rand(50, 500);
                
                Venta::create([
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'total' => $cantidad * $precio,
                    'receta_id' => rand(1, 4),
                    'created_at' => $fecha->copy()->addHours(rand(9, 20)) // Entre 9 AM y 8 PM
                ]);
            }
        }

        // Crear algunas ventas para el mes pasado
        for ($i = 29; $i >= 7; $i--) {
            if (rand(0, 1)) { // 50% de probabilidad de crear ventas este día
                $fecha = Carbon::now()->subDays($i);
                
                $ventasPorDia = rand(1, 3);
                for ($j = 0; $j < $ventasPorDia; $j++) {
                    $cantidad = rand(1, 10);
                    $precio = rand(50, 500);
                    
                    Venta::create([
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'total' => $cantidad * $precio,
                        'receta_id' => rand(1, 4),
                        'created_at' => $fecha->copy()->addHours(rand(9, 20))
                    ]);
                }
            }
        }

        // Crear algunas ventas para meses anteriores (últimos 6 meses)
        for ($i = 5; $i >= 1; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            
            // 10-15 ventas por mes
            $ventasPorMes = rand(10, 15);
            for ($j = 0; $j < $ventasPorMes; $j++) {
                $cantidad = rand(1, 10);
                $precio = rand(50, 500);
                
                Venta::create([
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'total' => $cantidad * $precio,
                    'receta_id' => rand(1, 4),
                    'created_at' => $fecha->copy()
                        ->addDays(rand(1, 28))
                        ->addHours(rand(9, 20))
                ]);
            }
        }
    }
}
