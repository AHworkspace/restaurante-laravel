<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    //
    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }
}
