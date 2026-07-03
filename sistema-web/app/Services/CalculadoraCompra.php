<?php

namespace App\Services;

use App\Helpers\ConversionesHelper;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use Illuminate\Validation\ValidationException;

class CalculadoraCompra
{
    public static function calcular(Insumo $insumo, array $linea): array
    {
        $presentacion = !empty($linea['presentacion_id']) ? InsumoPresentacion::find($linea['presentacion_id']) : null;
        $unidadBaseId = (int) ($presentacion?->unidad_stock_id ?: $insumo->unidad_medida_id);
        $unidadCompraId = (int) ($linea['unidad_medida_id'] ?? $unidadBaseId);
        $factorEstructura = collect($linea['estructura_empaque'] ?? [])->reduce(fn($factor,$nivel)=>$factor*(float)($nivel['cantidad']??1),1);
        $tieneEstructura = !empty($linea['estructura_empaque']);
        $unidadPrecioId = (int) ($linea['unidad_precio_id'] ?? $unidadCompraId);
        $cantidad = (float) $linea['cantidad_pedida'];
        $precioUnitario = (float) $linea['precio_unitario'];

        $cantidadContenido = isset($linea['cantidad_contenido']) && $linea['cantidad_contenido'] !== ''
            ? (float) $linea['cantidad_contenido'] : null;
        $cantidadSuelta = isset($linea['cantidad_suelta']) && $linea['cantidad_suelta'] !== ''
            ? (float) $linea['cantidad_suelta'] : 0;
        $unidadContenidoId = ! empty($linea['unidad_contenido_id']) ? (int) $linea['unidad_contenido_id'] : null;

        $factorCompra = $tieneEstructura ? $factorEstructura : self::factorHaciaBase(
            $unidadCompraId,
            $unidadBaseId,
            $linea['factor_compra_base'] ?? null,
            'contenido de la presentacion',
            true
        );
        $unidadInventarioId = $unidadBaseId;
        if ($cantidadContenido !== null && $unidadContenidoId) {
            $conversionContenido = ConversionesHelper::obtenerFactor($unidadContenidoId, $unidadBaseId);
            $factorCompra = $cantidadContenido * ($conversionContenido ?? 1);
            $unidadInventarioId = $conversionContenido === null ? $unidadContenidoId : $unidadBaseId;
        } elseif (!$tieneEstructura && $unidadCompraId !== $unidadBaseId
            && ConversionesHelper::obtenerFactor($unidadCompraId, $unidadBaseId) === null) {
            $unidadInventarioId = $unidadCompraId;
        }
        if($tieneEstructura){$cantidadContenido=$factorCompra;$unidadContenidoId=$unidadBaseId;$unidadInventarioId=$unidadBaseId;}

        $factorPrecioManual = $linea['factor_precio_base'] ?? null;
        if ($unidadPrecioId === $unidadCompraId && ! is_numeric($factorPrecioManual)) {
            $factorPrecioManual = $factorCompra;
        }
        $factorPrecio = self::factorHaciaBase(
            $unidadPrecioId,
            $unidadBaseId,
            $factorPrecioManual,
            'equivalencia de la unidad del precio'
        );

        $cantidadBase = ($cantidad * $factorCompra) + $cantidadSuelta;
        $precioExterior=!empty($linea['formato_empaque_id'])||$tieneEstructura||$unidadPrecioId===$unidadCompraId;
        $total=$precioExterior
            ? round(($cantidad*$precioUnitario)+($cantidadSuelta>0?$cantidadSuelta*($precioUnitario/max(0.000001,$factorCompra)):0),2)
            : round(($cantidadBase/$factorPrecio)*$precioUnitario,2);

        return [
            'unidad_medida_id' => $unidadCompraId,
            'unidad_precio_id' => $unidadPrecioId,
            'cantidad_pedida_base' => round($cantidadBase, 4),
            'precio_unitario' => round($precioUnitario, 4),
            'factor_compra_base' => round($factorCompra, 6),
            'cantidad_contenido' => $cantidadContenido,
            'cantidad_suelta' => $cantidadSuelta,
            'unidad_contenido_id' => $unidadContenidoId,
            'unidad_inventario_id' => $unidadInventarioId,
            'factor_precio_base' => round($factorPrecio, 6),
            'costo_linea' => $total,
        ];
    }

    private static function factorHaciaBase(int $origenId, int $baseId, mixed $manual, string $campo, bool $permitirControlPorEmpaque = false): float
    {
        if ($origenId === $baseId) {
            return 1;
        }

        $factor = ConversionesHelper::obtenerFactor($origenId, $baseId);
        if ($factor !== null) {
            return (float) $factor;
        }
        if (is_numeric($manual) && (float) $manual > 0) {
            return (float) $manual;
        }

        if ($permitirControlPorEmpaque) {
            return 1;
        }

        throw ValidationException::withMessages([
            'lineas' => 'Debes indicar la '.$campo.' porque no existe una conversión automática para ese empaque.',
        ]);
    }
}
