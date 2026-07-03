<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;

class StockBajo extends Notification
{
    use Queueable;

    public $insumo;
    public $cantidadSalida;
    public $fecha;
    public $presentacion;

    /**
     * Modifico el constructor para aceptar cantidad de salida y fecha.
     */
    public function __construct(Insumo $insumo, $cantidadSalida, $fecha, ?InsumoPresentacion $presentacion=null)
    {
        $this->insumo = $insumo;
        $this->cantidadSalida = $cantidadSalida;
        $this->fecha = $fecha;
        $this->presentacion = $presentacion;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toDatabase(object $notifiable): array
    {
        $nombreInsumo = $this->insumo->nombre;
        $categoria = $this->insumo->categoria ? $this->insumo->categoria->nombre : '-';
        $stockMinimo = $this->presentacion?->stock_minimo ?? $this->insumo->stock_minimo;
        $stockActual = $this->presentacion?->stockDisponible() ?? $this->insumo->getCantidadTotal();
        $cantidadSalida = $this->cantidadSalida;
        $fecha = $this->fecha->format('d/m/Y');
        $hora = $this->fecha->format('H:i');
        return [
            'message' => "⚠️ El insumo '{$nombreInsumo}' está por debajo del stock mínimo.",
            'insumo_id' => $this->insumo->id,
            'presentacion_id' => $this->presentacion?->id,
            'presentacion' => $this->presentacion?->nombre,
            'nombre_insumo' => $nombreInsumo,
            'categoria' => $categoria,
            'stock_minimo' => $stockMinimo,
            'stock_actual' => $stockActual,
            'cantidad_salida' => $cantidadSalida,
            'fecha' => $fecha,
            'hora' => $hora,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
