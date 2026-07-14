<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UnidadMedida extends Model
{
    //
    use HasFactory;

    protected $table = 'unidad_medidas';
    protected $fillable = [
        'nombre',
        'abreviatura',
        'descripcion',
    ];

    public static function reales(): Collection
    {
        $formatosComoUnidad = [
            'barril',
            'bandeja',
            'bidón',
            'bidon',
            'bolsa',
            'botella',
            'bulto',
            'caja',
            'cartón',
            'carton',
            'envase',
            'frasco',
            'lata',
            'malla',
            'paquete',
            'porción',
            'porcion',
            'sachet',
            'saco',
        ];

        return static::orderBy('nombre')->get()
            ->reject(fn ($unidad) => in_array(mb_strtolower($unidad->nombre), $formatosComoUnidad, true))
            ->values();
    }

    public static function idsReales(): array
    {
        return static::reales()->pluck('id')->all();
    }
}
