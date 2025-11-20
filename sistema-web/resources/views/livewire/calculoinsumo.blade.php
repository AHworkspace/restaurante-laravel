<?php

use Livewire\Volt\Component;
use App\Models\Receta;
use App\Models\PlanProduccion;

new class extends Component {
    //
    public $count = 0;

    public $recetas = [];

    public $receta = null;

    public $cantidad = 0;

    public $nombrePlan = '';

    public $insumos = [];

    public $resultado = '> Resultado:.....';

    public $planesProduccion = [];

    public $mostrarPlanes = false;

    public $planCalculado = false;

    // Para ver detalle
    public $planDetalle = null;
    public $mostrarDetalle = false;

    // Para editar
    public $planEditar = null;
    public $editarCantidad = 0;

    public function mount()
    {
        $this->recetas = \App\Models\Receta::all();
        $this->cargarPlanes();
    }

    public function increment()
    {
        $this->count++;
    }

    public function calcular()
    {
        $receta = Receta::find($this->receta);

        $this->insumos = $receta->insumos->map(function ($insumo) {
            return [
                'nombre' => $insumo->nombre,
                'cantidad' => $insumo->pivot->cantidad * $this->cantidad,
                'unidad_medida' => $insumo->unidad_medida->abreviatura,
                'imagen' => $insumo->imagen,
                'stock_actual' => $insumo->getCantidadTotal(),
                'stock_suficiente' => $insumo->getCantidadTotal() >= ($insumo->pivot->cantidad * $this->cantidad),
            ];
        });

        $this->planCalculado = true;

        // Registrar en historial
        $nombreReceta = $receta->nombre;
        \App\Helpers\HistorialHelper::registrar(
            'Calculó insumo necesario para receta',
            'Receta: ' . $nombreReceta . ', Cantidad a producir: ' . $this->cantidad . ', Insumos calculados: ' . $this->insumos->count(),
            'Predicciones'
        );
    }

    public function guardarPlan()
    {
        if (!$this->receta || !$this->cantidad) {
            session()->flash('error', 'Debe calcular primero antes de guardar');
            return;
        }

        if (empty($this->nombrePlan)) {
            session()->flash('error', 'Debe ingresar un nombre para el plan');
            return;
        }

        PlanProduccion::create([
            'nombre' => $this->nombrePlan,
            'receta_id' => $this->receta,
            'cantidad' => $this->cantidad,
        ]);

        \App\Helpers\HistorialHelper::registrar(
            'Guardó plan de producción',
            'Receta ID: ' . $this->receta . ', Cantidad: ' . $this->cantidad,
            'Planes Producción'
        );

        session()->flash('success', 'Plan de producción guardado exitosamente');
        $this->cargarPlanes();
        $this->planCalculado = false;
        $this->nombrePlan = '';
    }

    public function cargarPlanes()
    {
        $this->planesProduccion = PlanProduccion::with('receta')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function togglePlanes()
    {
        $this->mostrarPlanes = !$this->mostrarPlanes;
    }

    public function verDetalle($planId)
    {
        $this->planDetalle = PlanProduccion::with('receta')->find($planId);
        $this->mostrarDetalle = true;
    }

    public function cerrarDetalle()
    {
        $this->mostrarDetalle = false;
        $this->planDetalle = null;
    }

    public function prepararEditar($planId)
    {
        $this->planEditar = PlanProduccion::with('receta')->find($planId);
        $this->editarCantidad = $this->planEditar->cantidad;
    }

    public function actualizarPlan()
    {
        if (!$this->planEditar || $this->editarCantidad <= 0) {
            session()->flash('error', 'La cantidad debe ser mayor a 0');
            return;
        }

        $this->planEditar->cantidad = $this->editarCantidad;
        $this->planEditar->save();

        \App\Helpers\HistorialHelper::registrar(
            'Actualizó plan de producción',
            'ID: ' . $this->planEditar->id . ', Receta: ' . $this->planEditar->receta->nombre . ', Nueva cantidad: ' . $this->editarCantidad,
            'Planes Producción'
        );

        session()->flash('success', 'Plan actualizado exitosamente');
        $this->cargarPlanes();
        $this->planEditar = null;
        $this->editarCantidad = 0;
    }

    public function cancelarEdicion()
    {
        $this->planEditar = null;
        $this->editarCantidad = 0;
    }

    public function eliminarPlan($planId)
    {
        $plan = PlanProduccion::find($planId);
        if ($plan) {
            \App\Helpers\HistorialHelper::registrar(
                'Eliminó plan de producción',
                'ID: ' . $plan->id . ', Receta: ' . $plan->receta->nombre . ', Cantidad: ' . $plan->cantidad,
                'Planes Producción'
            );
            $plan->delete();
            session()->flash('success', 'Plan eliminado exitosamente');
            $this->cargarPlanes();
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
                            Calcular cantidad de insumos necesarios para produccion de [x] unidades de una receta
                            (plato).
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="select-style-1">
                            <label>¿Que receta quieres producir?</label>
                            <div class="select-position">
                                <select wire:model="receta">
                                    <option value="">Seleccione una receta</option>
                                    @foreach ($recetas as $recetita)
                                        <option value="{{ $recetita->id }}">{{ $recetita->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="input-style-1">
                            <label>¿Que cantidad quieres producir?</label>
                            <input type="number" wire:model="cantidad" placeholder="Cantidad a producir">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="table-wrapper table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="lead-info">
                                            <h6>Insumo</h6>
                                        </th>
                                        <th class="lead-email">
                                            <h6>Cantidad Necesaria</h6>
                                        </th>
                                        <th class="lead-email">
                                            <h6>Stock</h6>
                                        </th>
                                    </tr>
                                    <!-- end table row-->
                                </thead>
                                <tbody>
                                    @foreach ($insumos as $insumo)
                                        <tr>
                                            <td class="min-width">
                                                <p>{{ $insumo['nombre'] }}</p>
                                            </td>
                                            <td class="min-width">
                                                <p>{{ $insumo['cantidad'] }} {{ $insumo['unidad_medida'] }}</p>
                                            </td>
                                            <td class="min-width">
                                                @if($insumo['stock_suficiente'])
                                                    <span class="status-btn success-btn">
                                                        ✅ {{ $insumo['stock_actual'] }} {{ $insumo['unidad_medida'] }}
                                                    </span>
                                                @else
                                                    <span class="status-btn close-btn">
                                                        ❌ {{ $insumo['stock_actual'] }} {{ $insumo['unidad_medida'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    <!-- end table row -->
                                </tbody>
                            </table>
                            <!-- end table -->
                        </div>

                        <div class="button-group">
                            <button wire:click="calcular" class="main-btn active-btn btn-hover">
                                <i class="lni lni-android"></i> Calcular
                            </button>
                        </div>

                        @if($planCalculado && count($insumos) > 0)
                            <div class="input-style-1 mt-3">
                                <label>Nombre del Plan (opcional para guardar)</label>
                                <input type="text" wire:model="nombrePlan" placeholder="Ej: MenuDiario, EventoEspecial">
                            </div>
                            <div class="button-group mt-2">
                                <button wire:click="guardarPlan" class="main-btn success-btn btn-hover">
                                    <i class="lni lni-save"></i> Guardar Plan
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Mensajes de éxito/error -->
                @if (session()->has('success'))
                    <div class="alert-box success-alert mt-3">
                        <div class="alert">
                            <p class="text-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert-box danger-alert mt-3">
                        <div class="alert">
                            <p class="text-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Lista de Planes de Producción Guardados -->
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="title d-flex justify-content-between align-items-center">
                    <h4>📋 Mis Planes de Producción</h4>
                    <button wire:click="togglePlanes" class="main-btn btn-hover">
                        <i class="lni {{ $mostrarPlanes ? 'lni-chevron-up' : 'lni-chevron-down' }}"></i>
                        {{ $mostrarPlanes ? 'Ocultar' : 'Mostrar' }}
                    </button>
                </div>

                @if($mostrarPlanes)
                    <div class="table-wrapper table-responsive mt-3">
                        @if(count($planesProduccion) > 0)
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><h6>ID</h6></th>
                                        <th><h6>Nombre</h6></th>
                                        <th><h6>Receta</h6></th>
                                        <th><h6>Cantidad</h6></th>
                                        <th><h6>Fecha</h6></th>
                                        <th><h6>Acciones</h6></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($planesProduccion as $plan)
                                        <tr>
                                            <td>{{ $plan->id }}</td>
                                            <td><strong>{{ $plan->nombre }}</strong></td>
                                            <td>{{ $plan->receta->nombre }}</td>
                                            <td>{{ $plan->cantidad }} unidades</td>
                                            <td>{{ $plan->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="action">
                                                    <button class="text-primary me-2"
                                                            wire:click="verDetalle({{ $plan->id }})"
                                                            title="Ver detalle">
                                                        <i class="lni lni-eye"></i>
                                                    </button>
                                                    <button class="text-warning me-2"
                                                            wire:click="prepararEditar({{ $plan->id }})"
                                                            title="Editar">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    <button class="text-danger"
                                                            wire:click="eliminarPlan({{ $plan->id }})"
                                                            wire:confirm="¿Está seguro de eliminar este plan?"
                                                            title="Eliminar">
                                                        <i class="lni lni-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert-box warning-alert">
                                <div class="alert">
                                    <p class="text-medium">No hay planes de producción guardados</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Ver Detalle -->
        @if($mostrarDetalle && $planDetalle)
            <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">📋 Detalle del Plan de Producción #{{ $planDetalle->id }}</h5>
                            <button type="button" class="btn-close" wire:click="cerrarDetalle"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Receta:</strong> {{ $planDetalle->receta->nombre }}<br>
                                <strong>Cantidad:</strong> {{ $planDetalle->cantidad }} unidades<br>
                                <strong>Fecha creación:</strong> {{ $planDetalle->created_at->format('d/m/Y H:i') }}
                            </div>

                            <h6>Insumos Necesarios:</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Cantidad Necesaria</th>
                                        <th>Stock Actual</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($planDetalle->calcularInsumos() as $insumo)
                                        <tr>
                                            <td>{{ $insumo['nombre'] }}</td>
                                            <td>{{ $insumo['cantidad'] }} {{ $insumo['unidad_medida'] }}</td>
                                            <td>{{ $insumo['stock_actual'] }} {{ $insumo['unidad_medida'] }}</td>
                                            <td>
                                                @if($insumo['stock_suficiente'])
                                                    <span class="badge bg-success">✅ Disponible</span>
                                                @else
                                                    <span class="badge bg-danger">❌ Insuficiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cerrarDetalle">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal Editar Plan -->
        @if($planEditar)
            <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">✏️ Editar Plan de Producción #{{ $planEditar->id }}</h5>
                            <button type="button" class="btn-close" wire:click="cancelarEdicion"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Receta:</strong> {{ $planEditar->receta->nombre }}
                            </div>
                            <div class="input-style-1">
                                <label>Nueva Cantidad</label>
                                <input type="number" wire:model="editarCantidad" min="1" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cancelarEdicion">Cancelar</button>
                            <button type="button" class="btn btn-primary" wire:click="actualizarPlan">
                                <i class="lni lni-save"></i> Guardar Cambios
                            </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
