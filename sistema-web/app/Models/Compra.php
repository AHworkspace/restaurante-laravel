<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'costo_total', 'monto_pagado_proveedor',
        'proveedor',
        'proveedor_id',
        'descripcion', 'insumo_id', 'cantidad_pedida', 'cantidad_recibida',
        'estado', 'numero_documento', 'fecha_compra',
    ];

    public function proveedorRel()
    {
        return $this->belongsTo(Proveedor::class);
    }

    protected $casts = ['fecha_compra'=>'date','cantidad_pedida'=>'decimal:4','cantidad_recibida'=>'decimal:4','monto_pagado_proveedor'=>'decimal:2'];
    public function lineas(){return $this->hasMany(CompraLinea::class)->orderBy('id');}
    public function pagosProveedor(){return $this->hasMany(CompraProveedorPago::class);}
    public function movimientos(){return $this->hasMany(MovimientoInventario::class);}
    public function getSaldoPagoProveedorAttribute(): float{return max(0,round((float)$this->costo_total-(float)$this->monto_pagado_proveedor,2));}
}
