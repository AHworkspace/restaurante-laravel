<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TipoProduccion extends Model
{
    protected $table='tipos_produccion';
    protected $fillable=['nombre','descripcion','orden','activo'];
    protected $casts=['activo'=>'boolean'];
}
