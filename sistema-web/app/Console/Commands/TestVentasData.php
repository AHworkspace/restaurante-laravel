<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class TestVentasData extends Command
{
    protected $signature = 'test:ventas-data';
    protected $description = 'Test ventas data for reports';

    public function handle()
    {
        $this->info('Testing Ventas Data...');

        // Test 1: Count total ventas
        $totalVentas = Venta::count();
        $this->info("Total ventas: {$totalVentas}");

        // Test 2: Get all ventas with dates
        $ventas = Venta::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('SUM(total) as total_ventas'),
            DB::raw('COUNT(*) as cantidad_ventas')
        )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        $this->info("\nVentas por fecha:");
        foreach ($ventas as $venta) {
            $this->line("Fecha: {$venta->fecha}, Total: {$venta->total_ventas}, Cantidad: {$venta->cantidad_ventas}");
        }

        // Test 3: Get raw ventas data
        $this->info("\nPrimeras 5 ventas (raw data):");
        $rawVentas = Venta::take(5)->get();
        foreach ($rawVentas as $venta) {
            $this->line("ID: {$venta->id}, Created: {$venta->created_at}, Total: {$venta->total}");
        }

        // Test 4: Check movimientos
        $totalMovimientos = MovimientoInventario::count();
        $this->info("\nTotal movimientos: {$totalMovimientos}");

        $movimientos = MovimientoInventario::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as total_ingresos')
        )
            ->where('tipo', 'entrada')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        $this->info("\nMovimientos por fecha:");
        foreach ($movimientos as $movimiento) {
            $this->line("Fecha: {$movimiento->fecha}, Total: {$movimiento->total_ingresos}");
        }
    }
} 