<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormatoEmpaque extends Model
{
    protected $table = 'formatos_empaque';
    protected $fillable = ['nombre', 'descripcion', 'unidad_medida_id', 'es_granel', 'activo'];
    protected $casts = ['es_granel' => 'boolean', 'activo' => 'boolean'];

    public function unidadMedida(){return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');}
    public function presentaciones(){return $this->hasMany(InsumoPresentacion::class, 'formato_empaque_id');}
    public function lineasCompra(){return $this->hasMany(CompraLinea::class, 'formato_empaque_id');}
}
