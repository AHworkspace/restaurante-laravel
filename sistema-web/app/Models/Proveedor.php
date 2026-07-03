<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    //
    use HasFactory;
    protected $table = 'proveedores';
    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'descripcion',
    ];
    public function marcas(){return $this->belongsToMany(Marca::class,'marca_proveedor');}
}
