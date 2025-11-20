<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportePersonalizado extends Model
{
    protected $table = 'reportes_personalizados';

    protected $fillable = [
        'nombre',
        'fecha_desde',
        'fecha_hasta',
        'descripcion',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];
}
