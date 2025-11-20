<?php

if (!function_exists('ensamblarDatos')) {
    function ensamblarDatos()
    {
        // necesito crear un archivo data.csv
        $file = fopen('data.csv', 'w');

        // la cabecera es INSUMO,CANTIDAD,DÍA,MES,PLATO,DESPERDICIO
        $cabecera = ['INSUMO', 'CANTIDAD', 'DÍA', 'MES', 'PLATO', 'DESPERDICIO'];

        // Obtener movimientos de los usuarios
        $movimientos = auth()->user()->movimientos;
        // Filtrar movimientos de tipo "salida"
        $movimientosSalida = $movimientos->where('tipo', 'salida');

        // Escribir la cabecera en el archivo CSV
        fputcsv($file, $cabecera);

        // Recorrer los movimientos de salida y escribir cada uno en el archivo CSV
        foreach ($movimientosSalida as $movimiento) {
            // obtener dia y mes
            $dia = $movimiento->created_at->format('d');
            $mes = $movimiento->created_at->format('m');

            $fila = [
                $movimiento->insumo->nombre,
                $movimiento->cantidad,
                $dia,
                $mes,
                $movimiento->insumo->receta->nombre,
                $movimiento->insumo->desperdicio,
            ];
            fputcsv($file, $fila);
        }

        // Cerrar el archivo CSV
        fclose($file);
    }
}
