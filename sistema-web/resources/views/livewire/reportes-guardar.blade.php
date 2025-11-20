<?php

use Livewire\Volt\Component;
use App\Models\ReportePersonalizado;
use App\Models\ReporteMovimiento;

new class extends Component {
    public $nombreReporte = '';
    public $descripcionReporte = '';
    public $fechaInicio = '';
    public $fechaFin = '';

    public $reportesGuardados = [];
    public $mostrarReportes = false;

    // Para ver detalles
    public $reporteDetalle = null;
    public $mostrarDetalle = false;

    // Para editar
    public $reporteEditar = null;
    public $editarNombre = '';
    public $editarDescripcion = '';
    public $editarFechaInicio = '';
    public $editarFechaFin = '';

    public function mount()
    {
        $this->cargarReportes();
    }

    public function guardarReporte()
    {
        if (empty($this->nombreReporte)) {
            session()->flash('error', 'Debe ingresar un nombre para el reporte');
            return;
        }

        if (empty($this->fechaInicio) || empty($this->fechaFin)) {
            session()->flash('error', 'Debe seleccionar las fechas de inicio y fin');
            return;
        }

        ReportePersonalizado::create([
            'nombre' => $this->nombreReporte,
            'fecha_desde' => $this->fechaInicio,
            'fecha_hasta' => $this->fechaFin,
            'descripcion' => $this->descripcionReporte,
        ]);

        \App\Helpers\HistorialHelper::registrar(
            'Guardó reporte personalizado',
            'Nombre: ' . $this->nombreReporte . ', Período: ' . $this->fechaInicio . ' a ' . $this->fechaFin,
            'Reportes'
        );

        session()->flash('success', 'Reporte guardado exitosamente');
        $this->cargarReportes();
        $this->nombreReporte = '';
        $this->descripcionReporte = '';
        $this->fechaInicio = '';
        $this->fechaFin = '';
    }

    public function cargarReportes()
    {
        $ventas = ReportePersonalizado::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reporte) {
                return [
                    'id' => $reporte->id,
                    'nombre' => $reporte->nombre,
                    'fecha_desde' => $reporte->fecha_desde,
                    'fecha_hasta' => $reporte->fecha_hasta,
                    'descripcion' => $reporte->descripcion,
                    'created_at' => $reporte->created_at,
                    'tipo' => 'ventas',
                ];
            });

        $compras = ReporteMovimiento::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reporte) {
                return [
                    'id' => $reporte->id,
                    'nombre' => $reporte->nombre ?? 'Movimientos seleccionados',
                    'fecha_desde' => $reporte->fecha_desde,
                    'fecha_hasta' => $reporte->fecha_hasta,
                    'descripcion' => $reporte->datos ? 'Movimientos seleccionados' : null,
                    'created_at' => $reporte->created_at,
                    'tipo' => 'compras',
                    'total_movimientos' => $reporte->total_movimientos,
                    'total_costo' => $reporte->total_costo,
                ];
            });

        $this->reportesGuardados = $ventas->merge($compras)
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
    }

    public function toggleReportes()
    {
        $this->mostrarReportes = !$this->mostrarReportes;
    }

    public function verDetalle($reporteId)
    {
        $reporte = ReportePersonalizado::find($reporteId);

        if (!$reporte) {
            session()->flash('error', 'Reporte no encontrado');
            return;
        }

        // Debug: ver los datos del reporte
        \Log::info('📊 Ver Detalle Reporte', [
            'id' => $reporte->id,
            'nombre' => $reporte->nombre,
            'fecha_desde' => $reporte->fecha_desde,
            'fecha_hasta' => $reporte->fecha_hasta
        ]);

        // Cerrar la sección de reportes guardados
        $this->mostrarReportes = false;

        // Enviar las fechas al JavaScript para que cargue el reporte
        $this->dispatch('cargarReporteGuardado',
            fechaInicio: (string) $reporte->fecha_desde,
            fechaFin: (string) $reporte->fecha_hasta,
            nombre: $reporte->nombre
        );

        session()->flash('info', '📊 Visualizando reporte: ' . $reporte->nombre);
    }

    public function prepararEditar($reporteId)
    {
        $this->reporteEditar = ReportePersonalizado::find($reporteId);
        $this->editarNombre = $this->reporteEditar->nombre;
        $this->editarDescripcion = $this->reporteEditar->descripcion ?? '';
        $this->editarFechaInicio = $this->reporteEditar->fecha_desde;
        $this->editarFechaFin = $this->reporteEditar->fecha_hasta;
    }

    public function actualizarReporte()
    {
        if (!$this->reporteEditar || empty($this->editarNombre)) {
            session()->flash('error', 'El nombre es obligatorio');
            return;
        }

        $this->reporteEditar->nombre = $this->editarNombre;
        $this->reporteEditar->descripcion = $this->editarDescripcion;
        $this->reporteEditar->fecha_desde = $this->editarFechaInicio;
        $this->reporteEditar->fecha_hasta = $this->editarFechaFin;
        $this->reporteEditar->save();

        \App\Helpers\HistorialHelper::registrar(
            'Actualizó reporte personalizado',
            'ID: ' . $this->reporteEditar->id . ', Nombre: ' . $this->reporteEditar->nombre,
            'Reportes'
        );

        session()->flash('success', 'Reporte actualizado exitosamente');
        $this->cargarReportes();
        $this->reporteEditar = null;
        $this->editarNombre = '';
        $this->editarDescripcion = '';
        $this->editarFechaInicio = '';
        $this->editarFechaFin = '';
    }

    public function cancelarEdicion()
    {
        $this->reporteEditar = null;
        $this->editarNombre = '';
        $this->editarDescripcion = '';
        $this->editarFechaInicio = '';
        $this->editarFechaFin = '';
    }

    public function eliminarReporte($reporteId)
    {
        $reporte = ReportePersonalizado::find($reporteId);
        if ($reporte) {
            \App\Helpers\HistorialHelper::registrar(
                'Eliminó reporte personalizado',
                'ID: ' . $reporte->id . ', Nombre: ' . $reporte->nombre,
                'Reportes'
            );
            $reporte->delete();
            session()->flash('success', 'Reporte eliminado exitosamente');
            $this->cargarReportes();
        }
    }
}; ?>

<div>
    {{-- Mensajes flash --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Botones (MISMO ESTILO ORIGINAL) --}}
    <div class="col-md-6 text-end no-print">
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalGuardarReporte">
            <i class="lni lni-save"></i> Guardar Reporte Actual
        </button>
        <button type="button" wire:click="toggleReportes" class="btn btn-info me-2">
            <i class="lni lni-folder"></i> Mis Reportes
        </button>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="lni lni-printer"></i> Imprimir
        </button>
    </div>

    {{-- Sección de Reportes Guardados --}}
    @if ($mostrarReportes)
        <div class="container-fluid mb-4 no-print mt-3">
            <div class="card">
                <div class="card-header" style="background-color: #6F4E37; color: white;">
                    <h5 class="mb-0">📂 Mis Reportes Guardados</h5>
                </div>
                <div class="card-body">
                    @if (count($reportesGuardados) === 0)
                        <p class="text-center text-muted">No tienes reportes guardados aún.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Período</th>
                                        <th>Descripción</th>
                                        <th>Tipo</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportesGuardados as $reporte)
                                        <tr>
                                            <td>{{ $reporte['id'] }}</td>
                                            <td><strong>{{ $reporte['nombre'] }}</strong></td>
                                            <td>{{ $reporte['fecha_desde'] }} a {{ $reporte['fecha_hasta'] }}</td>
                                            <td>{{ $reporte['descripcion'] ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $reporte['tipo'] === 'ventas' ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ strtoupper($reporte['tipo']) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($reporte['created_at'])->format('d/m/Y') }}</td>
                                            <td class="d-flex gap-1 flex-wrap">
                                                @if ($reporte['tipo'] === 'ventas')
                                                    <button wire:click="verDetalle({{ $reporte['id'] }})"
                                                            class="btn btn-sm btn-info"
                                                            title="Ver Detalles">
                                                        <i class="lni lni-eye"></i>
                                                    </button>
                                                    <a href="{{ route('reportes.generar-pdf', $reporte['id']) }}"
                                                       class="btn btn-sm btn-danger"
                                                       title="Descargar PDF">
                                                        <i class="lni lni-download"></i>
                                                    </a>
                                                    <button wire:click="prepararEditar({{ $reporte['id'] }})"
                                                            class="btn btn-sm btn-warning"
                                                            title="Editar">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    <button wire:click="eliminarReporte({{ $reporte['id'] }})"
                                                            wire:confirm="¿Está seguro de eliminar el reporte '{{ $reporte['nombre'] }}'?"
                                                            class="btn btn-sm btn-danger"
                                                            title="Eliminar">
                                                        <i class="lni lni-trash"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-info btn-ver-mov"
                                                            data-url="{{ route('reportes.movimientos.ver', $reporte['id']) }}"
                                                            title="Ver Detalles">
                                                        <i class="lni lni-eye"></i>
                                                    </button>
                                                    <a href="{{ route('reportes.movimientos.pdf', $reporte['id']) }}"
                                                       class="btn btn-sm btn-danger"
                                                       title="Descargar PDF">
                                                        <i class="lni lni-download"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-warning btn-rename-mov"
                                                            data-url="{{ route('reportes.movimientos.actualizar', $reporte['id']) }}"
                                                            data-nombre="{{ $reporte['nombre'] }}"
                                                            title="Editar nombre">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger btn-delete-mov"
                                                            data-url="{{ route('reportes.movimientos.eliminar', $reporte['id']) }}"
                                                            title="Eliminar">
                                                        <i class="lni lni-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal para Guardar Reporte --}}
    <div class="modal fade" id="modalGuardarReporte" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #6F4E37; color: white;">
                    <h5 class="modal-title">Guardar Reporte Personalizado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Reporte *</label>
                        <input type="text" class="form-control" wire:model="nombreReporte" placeholder="Ej: Ventas Noviembre">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" wire:model="descripcionReporte" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>📅 Período:</strong> Selecciona las fechas a continuación.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Inicio *</label>
                            <input type="date" class="form-control" wire:model="fechaInicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin *</label>
                            <input type="date" class="form-control" wire:model="fechaFin">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="guardarReporte" class="btn btn-success">
                        <i class="lni lni-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar --}}
    @if ($reporteEditar)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #6F4E37; color: white;">
                        <h5 class="modal-title">✏️ Editar Reporte</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelarEdicion"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" class="form-control" wire:model="editarNombre">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" wire:model="editarDescripcion" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" wire:model="editarFechaInicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" wire:model="editarFechaFin">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelarEdicion">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="actualizarReporte">
                            <i class="lni lni-save"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script para cargar reporte en el visor --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('cargarReporteGuardado', (event) => {
                console.log('📊 Evento recibido:', event);

                const data = event[0] || event;
                console.log('📊 Data procesada:', data);

                // Convertir fechas del formato Laravel (YYYY-MM-DD o YYYY-MM-DD HH:MM:SS) a formato HTML5 (YYYY-MM-DD)
                const fechaInicio = (data.fechaInicio || '').split(' ')[0];
                const fechaFin = (data.fechaFin || '').split(' ')[0];

                console.log('📅 Fechas:', { fechaInicio, fechaFin });

                // Llenar los campos de fecha del formulario principal
                document.getElementById('fechaInicio').value = fechaInicio;
                document.getElementById('fechaFin').value = fechaFin;

                console.log('✅ Fechas establecidas en inputs');

                // Recargar los datos del gráfico automáticamente
                setTimeout(() => {
                    console.log('🔄 Recargando datos del gráfico...');
                    const periodoActivo = document.querySelector('[data-periodo].active');
                    if (periodoActivo && typeof cargarDatos === 'function') {
                        cargarDatos(periodoActivo.dataset.periodo);
                    } else {
                        // Fallback: cargar semana por defecto
                        cargarDatos('semana');
                    }
                }, 100);

                // Scroll suave hacia arriba para ver el reporte
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
</div>

