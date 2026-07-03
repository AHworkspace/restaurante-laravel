<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Consumidor extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'consumidores';

    protected $fillable = [
        'nombre_completo', 'ci', 'email', 'password', 'fuerza_id',
        'institucion_id', 'grado_id', 'codigo_unico', 'activo', 'observaciones',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['activo' => 'boolean', 'password' => 'hashed'];

    public function fuerza()
    {
        return $this->belongsTo(Fuerza::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function consumos()
    {
        return $this->hasMany(Consumo::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function saldoPendiente(): float
    {
        $consumido = (float) $this->consumos()->where('estado_pago', '!=', 'cancelado')->sum('total');
        $aplicado = (float) DB::table('pagos_consumos')
            ->join('consumos', 'consumos.id', '=', 'pagos_consumos.consumo_id')
            ->where('consumos.consumidor_id', $this->id)
            ->sum('pagos_consumos.monto_aplicado');

        return max(0, $consumido - $aplicado);
    }

    public function saldoAdelantadoDisponible(): float
    {
        $adelantado = (float) $this->pagos()->where('tipo_pago', 'adelanto')->sum('monto');
        $usado = (float) DB::table('pagos_consumos')
            ->join('pagos', 'pagos.id', '=', 'pagos_consumos.pago_id')
            ->where('pagos.consumidor_id', $this->id)
            ->where('pagos.tipo_pago', 'adelanto')
            ->sum('pagos_consumos.monto_aplicado');

        return max(0, $adelantado - $usado);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
