<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteMovimiento extends Model
{
    protected $table = 'reporte_movimientos';

    protected $fillable = [
        'nombre',
        'fecha_desde',
        'fecha_hasta',
        'total_movimientos',
        'total_costo',
        'datos',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'datos' => 'array',
    ];
}

