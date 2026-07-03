<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    //
    protected $table = 'ventas';
    protected $fillable = [
        'cantidad', 'precio', 'total', 'receta_id', 'insumo_id', 'presentacion_id', 'consumidor_id',
        'tipo_comida_id', 'fecha_venta', 'hora_venta', 'observaciones',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
    public function consumidor()
    {
        return $this->belongsTo(Consumidor::class);
    }
    public function insumo(){return $this->belongsTo(Insumo::class);}
    public function presentacion(){return $this->belongsTo(InsumoPresentacion::class,'presentacion_id');}
    public function getProductoNombreAttribute(): string{return $this->receta?->nombre ?? $this->presentacion?->nombre_completo ?? $this->insumo?->nombre ?? 'Producto';}
    public function tipoComida()
    {
        return $this->belongsTo(TipoComida::class);
    }
    public function movimientos_inventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
