<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanProduccion extends Model
{
    protected $table = 'planes_produccion';

    protected $fillable = [
        'nombre',
        'receta_id',
        'cantidad',
    ];

    /**
     * Relación con Receta
     */
    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    /**
     * Calcular insumos necesarios para este plan
     */
    public function calcularInsumos()
    {
        $receta = $this->receta;

        return $receta->insumos->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'cantidad' => $insumo->pivot->cantidad * $this->cantidad,
                'unidad_medida' => $insumo->unidad_medida->abreviatura,
                'stock_actual' => $insumo->getCantidadTotal(),
                'stock_suficiente' => $insumo->getCantidadTotal() >= ($insumo->pivot->cantidad * $this->cantidad),
            ];
        });
    }
}

