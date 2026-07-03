<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'instituciones';

    protected $fillable = ['nombre', 'codigo', 'fuerza_id', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function fuerza()
    {
        return $this->belongsTo(Fuerza::class);
    }

    public function grados()
    {
        return $this->hasMany(Grado::class)->orderBy('orden');
    }

    public function consumidores()
    {
        return $this->hasMany(Consumidor::class);
    }
}
