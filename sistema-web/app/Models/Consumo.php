<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumidor_id', 'receta_id', 'insumo_id', 'presentacion_id', 'tipo_comida_id', 'cantidad',
        'precio_unitario', 'total', 'fecha_consumo', 'hora_consumo',
        'estado_pago', 'observaciones', 'usuario_registro_id',
        'menu_dia_id', 'cantidad_menu_descontada',
    ];

    protected $casts = [
        'cantidad' => 'integer', 'precio_unitario' => 'decimal:2',
        'total' => 'decimal:2', 'fecha_consumo' => 'date',
    ];

    public function consumidor()
    {
        return $this->belongsTo(Consumidor::class);
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
    public function insumo(){return $this->belongsTo(Insumo::class);}
    public function presentacion(){return $this->belongsTo(InsumoPresentacion::class,'presentacion_id');}
    public function getProductoNombreAttribute(): string{return $this->receta?->nombre ?? $this->presentacion?->nombre_completo ?? $this->insumo?->nombre ?? 'Producto';}

    public function tipoComida()
    {
        return $this->belongsTo(TipoComida::class);
    }

    public function menuDia()
    {
        return $this->belongsTo(MenuDia::class);
    }

    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'pagos_consumos')
            ->withPivot('monto_aplicado')->withTimestamps();
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }

    public function montoPagado(): float
    {
        if ($this->relationLoaded('pagos')) {
            return (float) $this->pagos->sum(fn ($pago) => (float) $pago->pivot->monto_aplicado);
        }
        return (float) $this->pagos()->sum('pagos_consumos.monto_aplicado');
    }

    public function saldoPendiente(): float
    {
        return max(0, (float) $this->total - $this->montoPagado());
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_pago', 'pendiente');
    }
}
