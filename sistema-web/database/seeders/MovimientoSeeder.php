<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MovimientoInventario;

class MovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        MovimientoInventario::create([
            'cantidad' => 20, // antes 10
            'tipo' => 'entrada',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 1,
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 10,
            'tipo' => 'salida',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 1,
            'venta_id' => 1
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 1,
            'venta_id' => 1
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 25, // antes 10
            'tipo' => 'entrada',
            'motivo' => 'Reabastecimiento',
            'insumo_id' => 2,
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 10,
            'tipo' => 'salida',
            'motivo' => 'venta de insumos',
            'insumo_id' => 2,
            'venta_id' => 2
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 2,
            'venta_id' => 2
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 20, // antes 10
            'tipo' => 'entrada',
            'motivo' => 'Venta de insumos',
            'insumo_id' => 3,
            'venta_id' => 3
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 3,
            'venta_id' => 3
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 15,
            'tipo' => 'entrada',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 4
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 4,
            'venta_id' => 4
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 20,
            'tipo' => 'entrada',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 5
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 5,
            'venta_id' => 5
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 15,
            'tipo' => 'entrada',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 6
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 6,
            'venta_id' => 6
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 20,
            'tipo' => 'entrada',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 7
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 7,
            'venta_id' => 6
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 20,
            'tipo' => 'entrada',
            'motivo' => 'Compra de insumos',
            'insumo_id' => 8
        ]);
        
        MovimientoInventario::create([
            'cantidad' => 5,
            'tipo' => 'salida',
            'motivo' => 'Venta de plato',
            'insumo_id' => 8,
            'venta_id' => 6
        ]);
    }
}
