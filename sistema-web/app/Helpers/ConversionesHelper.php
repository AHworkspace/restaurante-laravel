<?php

namespace App\Helpers;

use App\Models\ConversionesUnidades;

class ConversionesHelper
{
    /**
     * Convertir cantidad de una unidad a otra
     */
    public static function convertir($cantidad, $unidadOrigenId, $unidadDestinoId)
    {
        return ConversionesUnidades::convertir($cantidad, $unidadOrigenId, $unidadDestinoId);
    }

    /**
     * Obtener factor de conversión entre dos unidades
     */
    public static function obtenerFactor($unidadOrigenId, $unidadDestinoId)
    {
        return ConversionesUnidades::obtenerFactor($unidadOrigenId, $unidadDestinoId);
    }

    /**
     * Verificar si dos unidades son la misma
     */
    public static function esMismaUnidad($unidad1Id, $unidad2Id)
    {
        return $unidad1Id == $unidad2Id;
    }

    /**
     * Formatear cantidad con unidad
     */
    public static function formatearCantidad($cantidad, $unidadAbreviatura)
    {
        return number_format($cantidad, 2) . ' ' . $unidadAbreviatura;
    }
}

