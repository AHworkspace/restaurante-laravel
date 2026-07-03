<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'indicaciones',
        'tiempo_preparacion',
        'imagen',
        'precio',
        'visible',
    ];

    // Scope para recetas visibles
    public function scopeVisibles($query)
    {
        return $query->where('visible', true);
    }

    // Eliminar la relación restaurante
    // public function restaurante()
    // {
    //     return $this->belongsTo(Restaurante::class);
    // }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'recetas_insumos')
            ->withPivot('cantidad', 'desperdicio','presentacion_id') // Incluye el campo extra
            ->withTimestamps();    // Incluye marcas de tiempo
    }

    // calcular cantidad de insumos gastados en una receta
    public function insumosGastados($cantidadRecetas)
    {
        $insumos = $this->insumos;
        $insumosGastados = [];
        foreach ($insumos as $insumo) {
            $insumosGastados[$insumo->id] = $insumo->pivot->cantidad * $cantidadRecetas;
        }

        return $insumosGastados;
    }
}
