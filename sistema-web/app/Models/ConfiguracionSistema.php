<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuraciones_sistema';
    protected $fillable = ['clave', 'valor'];

    public static function valor(string $clave, mixed $predeterminado = null): mixed
    {
        return cache()->remember("configuracion_sistema.{$clave}", 300, function () use ($clave, $predeterminado) {
            if (! Schema::hasTable('configuraciones_sistema')) {
                return $predeterminado;
            }

            return static::where('clave', $clave)->value('valor') ?? $predeterminado;
        });
    }

    public static function guardar(string $clave, mixed $valor): void
    {
        if (! Schema::hasTable('configuraciones_sistema')) {
            return;
        }

        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        cache()->forget("configuracion_sistema.{$clave}");
    }
}
