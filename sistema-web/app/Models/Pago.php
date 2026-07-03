<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumidor_id', 'monto', 'tipo_pago', 'metodo_pago', 'periodo_pagado',
        'fecha_pago', 'hora_pago', 'referencia', 'observaciones', 'usuario_registro_id',
    ];

    protected $casts = ['monto' => 'decimal:2', 'fecha_pago' => 'date'];

    public function consumidor()
    {
        return $this->belongsTo(Consumidor::class);
    }

    public function consumos()
    {
        return $this->belongsToMany(Consumo::class, 'pagos_consumos')
            ->withPivot('monto_aplicado')->withTimestamps();
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }
}
