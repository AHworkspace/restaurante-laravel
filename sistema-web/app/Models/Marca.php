<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $fillable = ['nombre','empresa_fabricante','descripcion','activo'];
    protected $casts = ['activo'=>'boolean'];
    public function proveedores(){return $this->belongsToMany(Proveedor::class,'marca_proveedor');}
    public function lineasCompra(){return $this->hasMany(CompraLinea::class);}
}
