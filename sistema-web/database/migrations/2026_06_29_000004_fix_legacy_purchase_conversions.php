<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cajaId = DB::table('unidad_medidas')->where('nombre', 'Caja')->value('id');
        $arrobaId = DB::table('unidad_medidas')->where('nombre', 'Arroba')->value('id');

        if ($cajaId) {
            $cocaIds = DB::table('insumos')->whereRaw('LOWER(nombre) = ?', ['coca-cola'])->pluck('id');
            DB::table('compra_lineas')->whereIn('insumo_id', $cocaIds)
                ->where('cantidad_pedida', 10)->where('costo_linea', 11)->where('cantidad_recibida', 0)
                ->update([
                    'unidad_medida_id' => $cajaId, 'unidad_precio_id' => $cajaId,
                    'cantidad_pedida' => 1, 'cantidad_pedida_base' => 6,
                    'precio_unitario' => 60, 'factor_compra_base' => 6,
                    'factor_precio_base' => 6, 'costo_linea' => 60, 'updated_at' => now(),
                ]);
        }

        if ($arrobaId) {
            DB::table('compra_lineas')->where('unidad_medida_id', $arrobaId)
                ->where('factor_compra_base', 1)->where('cantidad_recibida', 0)
                ->update([
                    'cantidad_pedida_base' => DB::raw('cantidad_pedida * 11.3398'),
                    'factor_compra_base' => 11.3398, 'factor_precio_base' => 11.3398,
                    'updated_at' => now(),
                ]);
        }

        foreach (DB::table('compras')->pluck('id') as $compraId) {
            $total = DB::table('compra_lineas')->where('compra_id', $compraId)->where('suma_al_total', true)->sum('costo_linea');
            if ($total > 0) DB::table('compras')->where('id', $compraId)->update(['costo_total' => $total]);
        }
    }

    public function down(): void
    {
        // Los datos corregidos no deben volver a valores incorrectos.
    }
};
