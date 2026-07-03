<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoComida extends Model
{
    use HasFactory;

    protected $table = 'tipos_comida';

    protected $fillable = ['nombre', 'codigo', 'hora_inicio', 'hora_fin', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function consumos()
    {
        return $this->hasMany(Consumo::class);
    }
}
