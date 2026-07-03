<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected static function booted():void
    {
        static::creating(function(MovimientoInventario $movimiento){if($movimiento->insumo_id&&!$movimiento->presentacion_id)$movimiento->presentacion_id=Insumo::find($movimiento->insumo_id)?->presentacionPredeterminada()->value('id');});
    }
    protected $fillable = [
        'cantidad',
        'tipo',
        'motivo',
        'insumo_id',
        'unidad_medida_id',
        'cantidad_original',
        'cantidad_suelta',
        'cantidad_convertida',
        'unidad_inventario_id',
        'compra_id',
        'compra_linea_id',
        'presentacion_id',
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
    public function unidadInventario(){return $this->belongsTo(UnidadMedida::class,'unidad_inventario_id');}

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function compraLinea()
    {
        return $this->belongsTo(CompraLinea::class, 'compra_linea_id');
    }
    public function presentacion(){return $this->belongsTo(InsumoPresentacion::class,'presentacion_id');}

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
}
