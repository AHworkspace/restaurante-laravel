<?php

namespace App\Services;

use App\Models\MovimientoInventario;

class PrediccionEntrenamientoService
{
    private const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo',
    ];

    private const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public function exportar(): array
    {
        $directorio = public_path('exports');
        if (! is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }

        $archivo = $directorio.DIRECTORY_SEPARATOR.'data.csv';
        $handle = fopen($archivo, 'w');
        fputcsv($handle, ['INSUMO', 'PRESENTACION', 'CANTIDAD', 'DIA', 'MES', 'PLATO', 'DESPERDICIO']);

        $movimientos = MovimientoInventario::with([
            'insumo',
            'presentacion.insumo',
            'receta.insumos',
            'venta.receta.insumos',
        ])
            ->where('tipo', 'salida')
            ->where(function ($query) {
                $query->whereNotNull('receta_id')
                    ->orWhereHas('venta', fn ($q) => $q->whereNotNull('receta_id'));
            })
            ->orderBy('created_at')
            ->get();

        $estadisticas = [
            'registros' => 0,
            'meses' => [],
            'recetas' => [],
            'insumos' => [],
            'presentaciones' => [],
        ];

        foreach ($movimientos as $movimiento) {
            $receta = $movimiento->receta ?: $movimiento->venta?->receta;
            $presentacion = $movimiento->presentacion;
            $insumo = $movimiento->insumo ?: $presentacion?->insumo;

            if (! $receta || ! $insumo) {
                continue;
            }

            $cantidad = (float) ($movimiento->cantidad_convertida ?? $movimiento->cantidad ?? 0);
            if ($cantidad <= 0) {
                continue;
            }

            $dia = self::DIAS[(int) $movimiento->created_at->format('N')] ?? 'Sin dia';
            $mes = self::MESES[(int) $movimiento->created_at->format('n')] ?? 'Sin mes';
            $presentacionNombre = $presentacion
                ? trim($insumo->nombre.' - '.$presentacion->nombre)
                : trim($insumo->nombre.' - General');
            $desperdicio = $this->desperdicioPara($receta, $insumo->id, $presentacion?->id, $cantidad);

            fputcsv($handle, [
                $insumo->nombre,
                $presentacionNombre,
                $cantidad,
                $dia,
                $mes,
                $receta->nombre,
                $desperdicio,
            ]);

            $estadisticas['registros']++;
            $estadisticas['meses'][$mes] = true;
            $estadisticas['recetas'][$receta->nombre] = true;
            $estadisticas['insumos'][$insumo->nombre] = true;
            $estadisticas['presentaciones'][$presentacionNombre] = true;
        }

        fclose($handle);

        return [
            'archivo_absoluto' => $archivo,
            'archivo_relativo' => 'exports/data.csv',
            'registros' => $estadisticas['registros'],
            'meses' => array_keys($estadisticas['meses']),
            'recetas' => array_keys($estadisticas['recetas']),
            'insumos' => array_keys($estadisticas['insumos']),
            'presentaciones' => array_keys($estadisticas['presentaciones']),
        ];
    }

    private function desperdicioPara($receta, int $insumoId, ?int $presentacionId, float $cantidad): float
    {
        $detalle = $receta->insumos->first(function ($insumo) use ($insumoId, $presentacionId) {
            if ($presentacionId && (int) ($insumo->pivot->presentacion_id ?? 0) === $presentacionId) {
                return true;
            }

            return (int) $insumo->id === $insumoId;
        });

        return round(((float) ($detalle?->pivot?->desperdicio ?? 0.1)) * $cantidad, 4);
    }
}
