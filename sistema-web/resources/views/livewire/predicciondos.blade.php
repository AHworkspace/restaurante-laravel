<?php

use Livewire\Volt\Component;
use App\Models\Insumo;
use Illuminate\Support\Facades\Http;

new class extends Component {

    public $insumos = [];

    public $insumo = null;

    public $mes = null;

    public $resultado = '> Resultado:.....';

    public function mount()
    {
        $this->insumos = Insumo::all();
    }

    public function predecir()
    {
        // Validar que se hayan seleccionado los campos requeridos
        if (!$this->insumo || !$this->mes) {
            $this->resultado = 'Error: Debes seleccionar un insumo y un mes';
            return;
        }

        try {
            // Obtener el nombre del insumo
            $nombreInsumo = Insumo::find($this->insumo)->nombre;

            // Hacer la solicitud al endpoint correcto
            $response = Http::post(env('HOST_MODELO_PREDICCION', 'http://127.0.0.1:5000') . '/predict/month_insumo', [
                'insumo' => $nombreInsumo,
                'mes' => $this->mes,
            ]);

            // Verificar la respuesta
            if ($response->successful()) {
                $data = $response->json();
                $cantidad = $data['cantidad_predicha'];
                $unidad = Insumo::find($this->insumo)->unidad_medida->abreviatura;
                $this->resultado = "Predicción exitosa:\n\nCantidad estimada: {$cantidad} {$unidad}\nInsumo: {$nombreInsumo}\nMes: {$this->mes}";

                // Registrar en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Realizó predicción de insumo mensual',
                    'Insumo: ' . $nombreInsumo . ', Mes: ' . $this->mes . ', Resultado: ' . $cantidad . ' ' . $unidad,
                    'Predicciones'
                );
            } else {
                $errorData = $response->json();
                $this->resultado = 'Error al predecir: ' . ($errorData['error'] ?? 'Error desconocido');

                // Registrar error en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Error en predicción',
                    "Insumo: {$nombreInsumo}, Mes: {$this->mes}, Error: " . ($errorData['error'] ?? 'Error desconocido'),
                    'Predicciones'
                );
            }
        } catch (\Exception $e) {
            $this->resultado = 'Error de conexión: ' . $e->getMessage();

            // Registrar error en historial
            \App\Helpers\HistorialHelper::registrar(
                'Error de conexión en predicción',
                "Insumo: {$nombreInsumo}, Mes: {$this->mes}, Error: " . $e->getMessage(),
                'Predicciones'
            );
        }
    }

        public function reentrenar()
    {
        try {
            $this->resultado = 'Iniciando re-entrenamiento...';

            // Crear archivo de datos
            $filePath = $this->ensamblarData();
            $archivo = public_path($filePath);

            // Hacer solicitud de re-entrenamiento
            $response = Http::attach('data_file', file_get_contents($archivo), 'data.csv')
                           ->post(env('HOST_MODELO_PREDICCION', 'http://127.0.0.1:5000') . '/retrain');

            if ($response->successful()) {
                $this->resultado = 'Re-entrenamiento exitoso: ' . $response->body();

                // Registrar en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Re-entrenamiento del modelo',
                    'Modelo de predicción re-entrenado exitosamente',
                    'Predicciones'
                );
            } else {
                $this->resultado = 'Error al re-entrenar: ' . $response->body();

                // Registrar error en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Error en re-entrenamiento',
                    'Error al re-entrenar el modelo: ' . $response->body(),
                    'Predicciones'
                );
            }
        } catch (\Exception $e) {
            $this->resultado = 'Error durante el re-entrenamiento: ' . $e->getMessage();

            // Registrar error en historial
            \App\Helpers\HistorialHelper::registrar(
                'Error de conexión en re-entrenamiento',
                'Error durante el re-entrenamiento: ' . $e->getMessage(),
                'Predicciones'
            );
        }
    }

    public function ensamblarData()
    {
        // Crear archivo data.csv
        $filePath = 'exports/data.csv';
        $file = fopen($filePath, 'w');

        // Cabecera del CSV
        $cabecera = ['INSUMO', 'CANTIDAD', 'DÍA', 'MES', 'PLATO', 'DESPERDICIO'];
        fputcsv($file, $cabecera);

        // Obtener movimientos de salida que tengan receta asociada
        $movimientos = \App\Models\MovimientoInventario::where('tipo', 'salida')
            ->where(function($query) {
                $query->whereNotNull('receta_id')
                      ->orWhereHas('venta', function($q) {
                          $q->whereNotNull('receta_id');
                      });
            })
            ->with(['insumo', 'receta', 'venta.receta'])
            ->get();

        foreach ($movimientos as $movimiento) {
            if (!$movimiento->insumo) {
                continue; // Saltar si no tiene insumo
            }

            $diaSemana = $movimiento->created_at->format('l');
            $mesNombre = $movimiento->created_at->format('F');

            // Traducir día de la semana
            $diasSemana = [
                'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
            ];
            $diaSemana = $diasSemana[$diaSemana] ?? $diaSemana;

            // Traducir mes
            $meses = [
                'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
                'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
                'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
                'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
            ];
            $mesNombre = $meses[$mesNombre] ?? $mesNombre;

            // Obtener la receta (puede venir de receta_id directo o de venta->receta)
            $receta = null;
            if ($movimiento->receta) {
                $receta = $movimiento->receta;
            } elseif ($movimiento->venta && $movimiento->venta->receta) {
                $receta = $movimiento->venta->receta;
            }

            if ($receta) {
                $nombreReceta = $receta->nombre;

                // Obtener el desperdicio de la relación receta-insumo
                $desperdicio = 0.1; // Valor por defecto
                foreach ($receta->insumos as $insumo) {
                    if ($insumo->id == $movimiento->insumo->id) {
                        $desperdicio = $insumo->pivot->desperdicio ?? 0.1;
                        break;
                    }
                }

                $fila = [
                    $movimiento->insumo->nombre,
                    $movimiento->cantidad,
                    $diaSemana,
                    $mesNombre,
                    $nombreReceta,
                    $desperdicio * $movimiento->cantidad
                ];
                fputcsv($file, $fila);
            }
        }

        fclose($file);
        return $filePath;
    }

}; ?>

<div>
    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Predecir la cantidad de insumo que se necesitará en un mes específico basado en el historial de consumo.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">

                        <div class="select-style-1">
                            <label>¿Qué insumo quieres predecir?</label>
                            <div class="select-position">
                                <select wire:model="insumo">
                                    <option value="">Seleccione un insumo</option>
                                    @foreach ($insumos as $ins)
                                        <option value="{{ $ins->id }}">{{ $ins->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="select-style-1">
                            <label>¿En qué mes quieres predecir el consumo?</label>
                            <div class="select-position">
                                <select wire:model="mes">
                                    <option value="">Seleccione un mes</option>
                                    <option value="Enero">Enero</option>
                                    <option value="Febrero">Febrero</option>
                                    <option value="Marzo">Marzo</option>
                                    <option value="Abril">Abril</option>
                                    <option value="Mayo">Mayo</option>
                                    <option value="Junio">Junio</option>
                                    <option value="Julio">Julio</option>
                                    <option value="Agosto">Agosto</option>
                                    <option value="Septiembre">Septiembre</option>
                                    <option value="Octubre">Octubre</option>
                                    <option value="Noviembre">Noviembre</option>
                                    <option value="Diciembre">Diciembre</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="col-6">
                        <div class="input-style-1">
                            <label>Resultado</label>
                            <textarea wire:model="resultado" rows="5" readonly></textarea>
                        </div>

                        <div class="button-group" style="text-align: center;">
                            <button wire:click="predecir" class="main-btn active-btn btn-hover"><i
                                    class="lni lni-android"></i> Predecir</button>
                            <button wire:click="reentrenar" class="main-btn dark-btn btn-hover"><i
                                    class="lni lni-cog"></i> Re-entrenar</button>
                        </div>

                        <p style="text-align: center;" class="mt-3">Ultimo entreno: 05/02/2025</p>

                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
