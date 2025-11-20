<?php

use Livewire\Volt\Component;
use App\Models\Receta;
use App\Models\Insumo;
use Illuminate\Support\Facades\Http;

new class extends Component {
    //

    public $recetas = [];

    public $receta = null;

    public $insumos = [];

    public $insumo = null;

    public $mes = null;

    public $resultado = '> Resultado:.....';

    public function mount()
    {
        $this->recetas = \App\Models\Receta::all();
        $this->insumos = \App\Models\Insumo::all();
    }

    public function obtenerInsumos()
    {
        $receta = Receta::find($this->receta);

        $this->insumos = $receta->insumos;
    }

    public function predecir()
    {
        // Validar que se hayan seleccionado los campos requeridos
        if (!$this->receta || !$this->insumo || !$this->mes) {
            $this->resultado = 'Error: Debes seleccionar una receta, un insumo y un mes';
            return;
        }

        try {
            // hacer una solicitud a la API de predicción
            $nombreInsumo = Insumo::find($this->insumo)->nombre;
            $nombreReceta = Receta::find($this->receta)->nombre;

            $response = Http::post(env('HOST_MODELO_PREDICCION', 'http://127.0.0.1:5000') . '/predict/month_plato', [
                'insumo' => $nombreInsumo,
                'plato' => $nombreReceta,
                'mes' => $this->mes,
                'desperdicio' => 0.1,
            ]);

            // Verificando la respuesta
            if ($response->successful()) {
                $data = $response->json(); // Convertir la respuesta en JSON
                $cantidad = $data['cantidad_predicha'];
                $unidad = Insumo::find($this->insumo)->unidad_medida->abreviatura;
                $resultado = "Predicción exitosa:\n\nCantidad estimada: {$cantidad} {$unidad}\nReceta: {$nombreReceta}\nInsumo: {$nombreInsumo}\nMes: {$this->mes}";
                $this->resultado = $resultado;
                // Registrar en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Realizó predicción de insumo por receta',
                    'Receta: ' . $nombreReceta . ', Insumo: ' . $nombreInsumo . ', Mes: ' . $this->mes . ', Resultado: ' . $cantidad . ' ' . $unidad,
                    'Predicciones'
                );
            } else {
                $errorData = $response->json();
                $this->resultado = 'Error al predecir: ' . ($errorData['error'] ?? 'Error desconocido (Código: ' . $response->status() . ')');

                // Registrar error en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Error en predicción de insumo por receta',
                    "Receta: {$nombreReceta}, Insumo: {$nombreInsumo}, Mes: {$this->mes}, Error: " . ($errorData['error'] ?? 'Error desconocido'),
                    'Predicciones'
                );
            }
        } catch (\Exception $e) {
            $this->resultado = 'Error de conexión: ' . $e->getMessage();

            // Registrar error en historial
            \App\Helpers\HistorialHelper::registrar(
                'Error de conexión en predicción de insumo por receta',
                "Error: " . $e->getMessage(),
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
                    'Re-entrenamiento del modelo de insumo por receta',
                    'Modelo de predicción re-entrenado exitosamente',
                    'Predicciones'
                );
            } else {
                $this->resultado = 'Error al re-entrenar: ' . $response->body();

                // Registrar error en historial
                \App\Helpers\HistorialHelper::registrar(
                    'Error en re-entrenamiento de insumo por receta',
                    'Error al re-entrenar el modelo: ' . $response->body(),
                    'Predicciones'
                );
            }
        } catch (\Exception $e) {
            $this->resultado = 'Error durante el re-entrenamiento: ' . $e->getMessage();

            // Registrar error en historial
            \App\Helpers\HistorialHelper::registrar(
                'Error de conexión en re-entrenamiento de insumo por receta',
                'Error durante el re-entrenamiento: ' . $e->getMessage(),
                'Predicciones'
            );
        }
    }
}; ?>

<div>
    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Predecir la cantidad de insumos que se necesitarán para producir [x] receta en [y] mes.
                        </p>
                    </div>
                </div>

                <div class="row">

                    <div class="col-6">
                        <div class="select-style-1">
                            <label>¿Que receta quieres producir?</label>
                            <div class="select-position">
                                <select wire:model="receta" wire:change="obtenerInsumos">
                                    <option value="">Seleccione una receta</option>
                                    @foreach ($recetas as $recetita)
                                        <option value="{{ $recetita->id }}">{{ $recetita->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

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
                            <label>¿En que mes vas a producir?</label>
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
