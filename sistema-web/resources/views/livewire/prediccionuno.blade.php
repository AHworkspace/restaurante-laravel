<?php

use Livewire\Volt\Component;
use App\Models\Receta;
use App\Models\InsumoPresentacion;
use App\Services\PrediccionEntrenamientoService;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $recetas = [];
    public $receta = null;
    public $presentaciones = [];
    public $presentacion = null;
    public $mes = null;
    public $resultado = '> Resultado:.....';

    public function mount()
    {
        $this->recetas = Receta::orderBy('nombre')->get();
        $this->presentaciones = collect();
    }

    public function obtenerInsumos()
    {
        $receta = Receta::with('insumos.presentaciones')->find($this->receta);
        $this->presentacion = null;
        $this->presentaciones = $receta
            ? $receta->insumos->map(function ($insumo) {
                $presentacionId = $insumo->pivot->presentacion_id ?: $insumo->presentacionPredeterminada()->value('id');
                return InsumoPresentacion::with('insumo')->find($presentacionId);
            })->filter()->values()
            : collect();
    }

    public function predecir()
    {
        if (! $this->receta || ! $this->presentacion || ! $this->mes) {
            $this->resultado = 'Error: Debes seleccionar una receta, una presentacion y un mes.';
            return;
        }

        try {
            $receta = Receta::findOrFail($this->receta);
            $presentacion = InsumoPresentacion::with('insumo')->findOrFail($this->presentacion);
            $response = Http::post(env('HOST_MODELO_PREDICCION', 'http://127.0.0.1:5000').'/predict/month_plato', [
                'insumo' => $presentacion->insumo->nombre,
                'presentacion' => $presentacion->nombre_completo,
                'plato' => $receta->nombre,
                'mes' => $this->mes,
                'desperdicio' => 0.1,
            ]);

            if ($response->successful()) {
                $cantidad = $response->json('cantidad_predicha');
                $unidad = $presentacion->unidadStock()?->abreviatura ?: '';
                $this->resultado = "Prediccion exitosa:\n\nCantidad estimada: {$cantidad} {$unidad}\nReceta: {$receta->nombre}\nPresentacion: {$presentacion->nombre_completo}\nMes: {$this->mes}";
                \App\Helpers\HistorialHelper::registrar('Realizo prediccion de insumo por receta', 'Receta: '.$receta->nombre.', Presentacion: '.$presentacion->nombre_completo.', Mes: '.$this->mes.', Resultado: '.$cantidad.' '.$unidad, 'Predicciones');
                return;
            }

            $this->resultado = 'Error al predecir: '.($response->json('error') ?: $response->body());
        } catch (\Exception $e) {
            $this->resultado = 'Error de conexion: '.$e->getMessage();
        }
    }

    public function reentrenar()
    {
        try {
            $export = app(PrediccionEntrenamientoService::class)->exportar();
            if ($export['registros'] < 2) {
                $this->resultado = 'No hay suficientes salidas de preparacion asociadas a recetas para reentrenar. Registra salidas en Movimientos seleccionando la receta donde se usara el insumo.';
                return;
            }

            $response = Http::attach('data_file', file_get_contents($export['archivo_absoluto']), 'data.csv')
                ->post(env('HOST_MODELO_PREDICCION', 'http://127.0.0.1:5000').'/retrain');

            if ($response->successful()) {
                $this->resultado = 'Re-entrenamiento exitoso. Registros usados: '.$export['registros'].'. Recetas aprendidas: '.count($export['recetas']).'. Presentaciones aprendidas: '.count($export['presentaciones']).'.';
                \App\Helpers\HistorialHelper::registrar('Re-entrenamiento del modelo de insumo por receta', 'Registros: '.$export['registros'], 'Predicciones');
                return;
            }

            $this->resultado = 'Error al re-entrenar: '.($response->json('error') ?: $response->body());
        } catch (\Exception $e) {
            $this->resultado = 'Error durante el re-entrenamiento: '.$e->getMessage();
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
                            Estima cuanto se necesitara de una presentacion para una receta y mes, usando salidas de preparacion.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="select-style-1">
                            <label>Receta</label>
                            <div class="select-position">
                                <select wire:model="receta" wire:change="obtenerInsumos">
                                    <option value="">Seleccione una receta</option>
                                    @foreach ($recetas as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="select-style-1">
                            <label>Presentacion de insumo de esa receta</label>
                            <div class="select-position">
                                <select wire:model="presentacion">
                                    <option value="">Seleccione una presentacion</option>
                                    @foreach ($presentaciones as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre_completo }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="select-style-1">
                            <label>Mes</label>
                            <div class="select-position">
                                <select wire:model="mes">
                                    <option value="">Seleccione un mes</option>
                                    @foreach (['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $nombreMes)
                                        <option value="{{ $nombreMes }}">{{ $nombreMes }}</option>
                                    @endforeach
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
                            <button wire:click="predecir" class="main-btn active-btn btn-hover"><i class="lni lni-android"></i> Predecir</button>
                            <button wire:click="reentrenar" class="main-btn dark-btn btn-hover"><i class="lni lni-cog"></i> Re-entrenar</button>
                        </div>

                        <p style="text-align: center;" class="mt-3">La receta define la referencia; los movimientos de salida alimentan el entrenamiento.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
