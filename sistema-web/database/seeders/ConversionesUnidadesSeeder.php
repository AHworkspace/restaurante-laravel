<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConversionesUnidades;
use App\Models\UnidadMedida;

class ConversionesUnidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener unidades por nombre
        $kg = UnidadMedida::where('nombre', 'Kilogramo')->first();
        $g = UnidadMedida::where('nombre', 'Gramo')->first();
        $arroba = UnidadMedida::where('nombre', 'Arroba')->first();
        $libra = UnidadMedida::where('nombre', 'Libra')->first();
        $onza = UnidadMedida::where('nombre', 'Onza')->first();
        $quintal = UnidadMedida::where('nombre', 'Quintal')->first();
        $tonelada = UnidadMedida::where('nombre', 'Tonelada')->first();
        $miligramo = UnidadMedida::where('nombre', 'Miligramo')->first();
        $litro = UnidadMedida::where('nombre', 'Litro')->first();
        $mililitro = UnidadMedida::where('nombre', 'Mililitro')->first();
        $barril = UnidadMedida::where('nombre', 'Barril')->first();
        $cuartilla = UnidadMedida::where('nombre', 'Cuartilla')->first();

        // ============================================
        // CONVERSIONES DE MASA/PESO
        // ============================================

        // 1. Kilogramo (kg)
        if ($kg && $g) {
            // 1 kg = 1000 g
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $kg->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 1000.0]
            );
        }

        if ($kg && $libra) {
            // 1 kg = 2.20462 lb
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $kg->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 2.20462]
            );
        }

        if ($kg && $arroba) {
            // 1 arroba = 11.3398 kg (según tabla del usuario)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $arroba->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 11.3398]
            );
        }

        if ($kg && $quintal) {
            // 1 kg = 0.0220462 quintales (1 quintal = 45.3592 kg)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 45.3592]
            );
        }

        if ($kg && $tonelada) {
            // 1 kg = 0.001 toneladas (1 tonelada = 1000 kg)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 1000.0]
            );
        }

        // 2. Gramo (g)
        if ($g && $kg) {
            // 1 g = 0.001 kg
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $g->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 0.001]
            );
        }

        if ($g && $libra) {
            // 1 g = 0.00220462 lb
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $g->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 0.00220462]
            );
        }

        if ($g && $arroba) {
            // 1 g = 0.00004 arrobas (1 arroba = 11,339.8 g)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $arroba->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 11339.8]
            );
        }

        if ($g && $quintal) {
            // 1 g = 0.0000220462 quintales (1 quintal = 45,359.2 g)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 45359.2]
            );
        }

        if ($g && $tonelada) {
            // 1 g = 0.000001 toneladas (1 tonelada = 1,000,000 g)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 1000000.0]
            );
        }

        // 3. Libra (lb)
        if ($libra && $kg) {
            // 1 lb = 0.453592 kg
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $libra->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 0.453592]
            );
        }

        if ($libra && $g) {
            // 1 lb = 453.592 g
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $libra->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 453.592]
            );
        }

        if ($libra && $arroba) {
            // 1 lb = 0.04 arrobas (1 arroba = 25 lb)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $arroba->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 25.0]
            );
        }

        if ($libra && $quintal) {
            // 1 lb = 0.01 quintales (1 quintal = 100 lb)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 100.0]
            );
        }

        if ($libra && $tonelada) {
            // 1 lb = 0.000453592 toneladas (1 tonelada = 2204.62 lb)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 2204.62]
            );
        }

        // 4. Arroba
        if ($arroba && $libra) {
            // 1 arroba = 25 lb
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $arroba->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 25.0]
            );
        }

        if ($arroba && $g) {
            // 1 arroba = 11,339.8 g
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $arroba->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 11339.8]
            );
        }

        if ($arroba && $quintal) {
            // 1 arroba = 0.25 quintales (1 quintal = 4 arrobas)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $arroba->id],
                ['factor_conversion' => 4.0]
            );
        }

        if ($arroba && $tonelada) {
            // 1 arroba = 0.0113398 toneladas (1 tonelada = 88.184 arrobas)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $arroba->id],
                ['factor_conversion' => 88.184]
            );
        }

        // 5. Quintal (qq)
        if ($quintal && $libra) {
            // 1 quintal = 100 lb
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 100.0]
            );
        }

        if ($quintal && $g) {
            // 1 quintal = 45,359.2 g
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 45359.2]
            );
        }

        if ($quintal && $arroba) {
            // 1 quintal = 4 arrobas
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $quintal->id, 'unidad_destino_id' => $arroba->id],
                ['factor_conversion' => 4.0]
            );
        }

        if ($quintal && $tonelada) {
            // 1 quintal = 0.0453592 toneladas (1 tonelada = 22.0462 quintales)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $quintal->id],
                ['factor_conversion' => 22.0462]
            );
        }

        // 6. Tonelada (t)
        if ($tonelada && $kg) {
            // 1 tonelada = 1000 kg
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 1000.0]
            );
        }

        if ($tonelada && $g) {
            // 1 tonelada = 1,000,000 g
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $g->id],
                ['factor_conversion' => 1000000.0]
            );
        }

        if ($tonelada && $libra) {
            // 1 tonelada = 2204.62 lb
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $libra->id],
                ['factor_conversion' => 2204.62]
            );
        }

        if ($tonelada && $arroba) {
            // 1 tonelada = 88.184 arrobas
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $arroba->id],
                ['factor_conversion' => 88.184]
            );
        }

        if ($tonelada && $quintal) {
            // 1 tonelada = 22.0462 quintales
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $tonelada->id, 'unidad_destino_id' => $quintal->id],
                ['factor_conversion' => 22.0462]
            );
        }

        // Miligramo
        if ($miligramo && $kg) {
            // 1 Miligramo = 0.000001 kg
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $miligramo->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 0.000001]
            );
        }

        // Onza
        if ($onza && $kg) {
            // 1 Onza = 0.0283495 kg
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $onza->id, 'unidad_destino_id' => $kg->id],
                ['factor_conversion' => 0.0283495]
            );
        }

        // ============================================
        // CONVERSIONES DE VOLUMEN
        // ============================================

        if ($litro && $mililitro) {
            // 1 Litro = 1000 ml
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $litro->id, 'unidad_destino_id' => $mililitro->id],
                ['factor_conversion' => 1000.0]
            );
        }

        if ($litro && $barril) {
            // 1 Barril = 159 L (aproximado, puede variar según el tipo)
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $barril->id, 'unidad_destino_id' => $litro->id],
                ['factor_conversion' => 159.0]
            );
        }

        // Conversión para Cuartilla (volumen)
        if ($litro && $cuartilla) {
            // 1 Cuartilla = 0.25 litros
            ConversionesUnidades::updateOrCreate(
                ['unidad_origen_id' => $cuartilla->id, 'unidad_destino_id' => $litro->id],
                ['factor_conversion' => 0.25]
            );
        }
    }
}
