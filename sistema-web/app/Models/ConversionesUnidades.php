<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionesUnidades extends Model
{
    protected $table = 'conversiones_unidades';

    protected $fillable = [
        'unidad_origen_id',
        'unidad_destino_id',
        'factor_conversion',
    ];

    public function unidadOrigen()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_origen_id');
    }

    public function unidadDestino()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_destino_id');
    }

    /**
     * Obtener factor de conversión entre dos unidades
     */
    public static function obtenerFactor($unidadOrigenId, $unidadDestinoId)
    {
        // Si son la misma unidad, retornar 1
        if ($unidadOrigenId == $unidadDestinoId) {
            return 1;
        }

        // Buscar conversión directa
        $conversion = self::where('unidad_origen_id', $unidadOrigenId)
            ->where('unidad_destino_id', $unidadDestinoId)
            ->first();

        if ($conversion) {
            return $conversion->factor_conversion;
        }

        // Buscar conversión inversa
        $conversionInversa = self::where('unidad_origen_id', $unidadDestinoId)
            ->where('unidad_destino_id', $unidadOrigenId)
            ->first();

        if ($conversionInversa) {
            return 1 / $conversionInversa->factor_conversion;
        }

        // Si no hay conversión, retornar null
        return null;
    }

    /**
     * Convertir cantidad de una unidad a otra
     */
    public static function convertir($cantidad, $unidadOrigenId, $unidadDestinoId)
    {
        $factor = self::obtenerFactor($unidadOrigenId, $unidadDestinoId);

        if ($factor === null) {
            // Si no hay conversión, retornar la cantidad original
            return $cantidad;
        }

        return $cantidad * $factor;
    }
}
