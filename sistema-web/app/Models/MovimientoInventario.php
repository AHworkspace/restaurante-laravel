<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $fillable = [
        'cantidad',
        'tipo',
        'motivo',
        'insumo_id',
        'unidad_medida_id',
        'cantidad_original',
        'cantidad_convertida',
        'compra_id',
        'receta_id',
    ];

    // Asegurar que restaurante_id no se asigne masivamente
    protected $guarded = ['restaurante_id'];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function unidad_medida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
}
