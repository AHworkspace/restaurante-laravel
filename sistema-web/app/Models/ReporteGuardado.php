<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteGuardado extends Model
{
    protected $table = 'reportes_guardados';

    protected $fillable = [
        'nombre',
        'sector',
        'subtipo',
        'fecha_desde',
        'fecha_hasta',
        'total_registros',
        'total_monto',
        'filtros',
        'datos',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'filtros' => 'array',
        'datos' => 'array',
    ];

    public function getTipoEtiquetaAttribute(): string
    {
        return trim($this->sector . ($this->subtipo ? ' - ' . $this->subtipo : ''));
    }
}
