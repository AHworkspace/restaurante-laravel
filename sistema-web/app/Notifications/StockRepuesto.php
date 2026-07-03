<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;

class StockRepuesto extends Notification
{
    use Queueable;

    public $insumo;
    public $cantidad;
    public $fecha;
    public $presentacion;

    public function __construct(Insumo $insumo, $cantidad, $fecha, ?InsumoPresentacion $presentacion=null)
    {
        $this->insumo = $insumo;
        $this->cantidad = $cantidad;
        $this->fecha = $fecha;
        $this->presentacion = $presentacion;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $nombreInsumo = $this->insumo->nombre;
        $fecha = $this->fecha->format('d/m/Y H:i');
        return [
            'message' => "✅ El insumo '{$nombreInsumo}' fue repuesto con {$this->cantidad} unidades el día {$fecha}.",
            'insumo_id' => $this->insumo->id,
            'presentacion_id' => $this->presentacion?->id,
            'fecha' => $fecha,
        ];
    }
}
