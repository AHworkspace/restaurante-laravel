<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'costo_total',
        'proveedor',
        'proveedor_id',
        'descripcion',
    ];

    public function proveedorRel()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
