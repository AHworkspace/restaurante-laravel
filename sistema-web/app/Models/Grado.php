<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo', 'institucion_id', 'orden', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean', 'orden' => 'integer'];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function consumidores()
    {
        return $this->hasMany(Consumidor::class);
    }
}
