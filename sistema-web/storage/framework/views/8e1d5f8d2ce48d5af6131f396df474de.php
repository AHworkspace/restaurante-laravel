<?php

use Livewire\Volt\Component;
use App\Models\ReportePersonalizado;
use App\Models\ReporteMovimiento;

?>

<div>
    
    <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo e(session('info')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
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

    
    <!--[if BLOCK]><![endif]--><?php if($mostrarReportes): ?>
        <div class="container-fluid mb-4 no-print mt-3">
            <div class="card">
                <div class="card-header" style="background-color: #6F4E37; color: white;">
                    <h5 class="mb-0">📂 Mis Reportes Guardados</h5>
                </div>
                <div class="card-body">
                    <!--[if BLOCK]><![endif]--><?php if(count($reportesGuardados) === 0): ?>
                        <p class="text-center text-muted">No tienes reportes guardados aún.</p>
                    <?php else: ?>
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
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $reportesGuardados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reporte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($reporte['id']); ?></td>
                                            <td><strong><?php echo e($reporte['nombre']); ?></strong></td>
                                            <td><?php echo e($reporte['fecha_desde']); ?> a <?php echo e($reporte['fecha_hasta']); ?></td>
                                            <td><?php echo e($reporte['descripcion'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge <?php echo e($reporte['tipo'] === 'ventas' ? 'bg-primary' : 'bg-secondary'); ?>">
                                                    <?php echo e(strtoupper($reporte['tipo'])); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e(\Carbon\Carbon::parse($reporte['created_at'])->format('d/m/Y')); ?></td>
                                            <td class="d-flex gap-1 flex-wrap">
                                                <!--[if BLOCK]><![endif]--><?php if($reporte['tipo'] === 'ventas'): ?>
                                                    <button wire:click="verDetalle(<?php echo e($reporte['id']); ?>)"
                                                            class="btn btn-sm btn-info"
                                                            title="Ver Detalles">
                                                        <i class="lni lni-eye"></i>
                                                    </button>
                                                    <a href="<?php echo e(route('reportes.generar-pdf', $reporte['id'])); ?>"
                                                       class="btn btn-sm btn-danger"
                                                       title="Descargar PDF">
                                                        <i class="lni lni-download"></i>
                                                    </a>
                                                    <button wire:click="prepararEditar(<?php echo e($reporte['id']); ?>)"
                                                            class="btn btn-sm btn-warning"
                                                            title="Editar">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    <button wire:click="eliminarReporte(<?php echo e($reporte['id']); ?>)"
                                                            wire:confirm="¿Está seguro de eliminar el reporte '<?php echo e($reporte['nombre']); ?>'?"
                                                            class="btn btn-sm btn-danger"
                                                            title="Eliminar">
                                                        <i class="lni lni-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-info btn-ver-mov"
                                                            data-url="<?php echo e(route('reportes.movimientos.ver', $reporte['id'])); ?>"
                                                            title="Ver Detalles">
                                                        <i class="lni lni-eye"></i>
                                                    </button>
                                                    <a href="<?php echo e(route('reportes.movimientos.pdf', $reporte['id'])); ?>"
                                                       class="btn btn-sm btn-danger"
                                                       title="Descargar PDF">
                                                        <i class="lni lni-download"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-warning btn-rename-mov"
                                                            data-url="<?php echo e(route('reportes.movimientos.actualizar', $reporte['id'])); ?>"
                                                            data-nombre="<?php echo e($reporte['nombre']); ?>"
                                                            title="Editar nombre">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger btn-delete-mov"
                                                            data-url="<?php echo e(route('reportes.movimientos.eliminar', $reporte['id'])); ?>"
                                                            title="Eliminar">
                                                        <i class="lni lni-trash"></i>
                                                    </button>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
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

    
    <!--[if BLOCK]><![endif]--><?php if($reporteEditar): ?>
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
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
</div><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views\livewire/reportes-guardar.blade.php ENDPATH**/ ?>