<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fuerza extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function instituciones()
    {
        return $this->hasMany(Institucion::class);
    }

    public function consumidores()
    {
        return $this->hasMany(Consumidor::class);
    }
}
