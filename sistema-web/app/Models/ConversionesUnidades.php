<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionesUnidades extends Model
{
    protected $table = 'conversiones_unidades';

    protected $fillable = ['unidad_origen_id', 'unidad_destino_id', 'factor_conversion'];

    public function unidadOrigen()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_origen_id');
    }

    public function unidadDestino()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_destino_id');
    }

    public static function obtenerFactor($unidadOrigenId, $unidadDestinoId)
    {
        if ((int) $unidadOrigenId === (int) $unidadDestinoId) return 1.0;

        $adyacencias = [];
        foreach (self::all() as $conversion) {
            $factor = (float) $conversion->factor_conversion;
            if ($factor <= 0) continue;
            $adyacencias[$conversion->unidad_origen_id][] = [$conversion->unidad_destino_id, $factor];
            $adyacencias[$conversion->unidad_destino_id][] = [$conversion->unidad_origen_id, 1 / $factor];
        }

        $cola = [[(int) $unidadOrigenId, 1.0]];
        $visitadas = [(int) $unidadOrigenId => true];
        while ($cola !== []) {
            [$actual, $factorAcumulado] = array_shift($cola);
            foreach ($adyacencias[$actual] ?? [] as [$siguiente, $factorPaso]) {
                $siguiente = (int) $siguiente;
                if (isset($visitadas[$siguiente])) continue;
                $nuevoFactor = $factorAcumulado * $factorPaso;
                if ($siguiente === (int) $unidadDestinoId) return $nuevoFactor;
                $visitadas[$siguiente] = true;
                $cola[] = [$siguiente, $nuevoFactor];
            }
        }

        return null;
    }

    public static function convertir($cantidad, $unidadOrigenId, $unidadDestinoId)
    {
        $factor = self::obtenerFactor($unidadOrigenId, $unidadDestinoId);
        return $factor === null ? (float) $cantidad : (float) $cantidad * $factor;
    }
}
