<?php

use Livewire\Volt\Component;
use App\Models\InsumoPresentacion;
use App\Services\PrediccionEntrenamientoService;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $presentaciones = [];
    public $presentacion = null;
    public $mes = null;
    public $resultado = '> Resultado:.....';

    public function mount()
    {
        $this->presentaciones = InsumoPresentacion::with(['insumo', 'unidadStockRelacion'])
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    public function predecir()
    {
        if (! $this->presentacion || ! $this->mes) {
            $this->resultado = 'Error: Debes seleccionar una presentacion y un mes.';
            return;
        }

        try {
            $presentacion = InsumoPresentacion::with(['insumo', 'unidadStockRelacion'])->findOrFail($this->presentacion);
            $response = Http::post(env('HOST_MODELO_PREDICCION', 'http://127.0.0.1:5000').'/predict/month_insumo', [
                'insumo' => $presentacion->insumo->nombre,
                'presentacion' => $presentacion->nombre_completo,
                'mes' => $this->mes,
            ]);

            if ($response->successful()) {
                $cantidad = $response->json('cantidad_predicha');
                $unidad = $presentacion->unidadStock()?->abreviatura ?: '';
                $this->resultado = "Prediccion exitosa:\n\nCantidad estimada: {$cantidad} {$unidad}\nPresentacion: {$presentacion->nombre_completo}\nMes: {$this->mes}";
                \App\Helpers\HistorialHelper::registrar('Realizo prediccion de insumo mensual', 'Presentacion: '.$presentacion->nombre_completo.', Mes: '.$this->mes.', Resultado: '.$cantidad.' '.$unidad, 'Predicciones');
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
                $this->resultado = 'Re-entrenamiento exitoso. Registros usados: '.$export['registros'].'. Meses con datos: '.implode(', ', $export['meses']).'.';
                \App\Helpers\HistorialHelper::registrar('Re-entrenamiento del modelo', 'Registros: '.$export['registros'], 'Predicciones');
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
                            Estima cuanto se necesitara de una presentacion de insumo en un mes, usando salidas de preparacion asociadas a recetas.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="select-style-1">
                            <label>Presentacion de insumo</label>
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

                        <p style="text-align: center;" class="mt-3">Entrena con movimientos de salida vinculados a recetas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
