<?php

use Livewire\Volt\Component;
use App\Models\Receta;
use App\Models\PlanProduccion;

?>

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
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recetas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recetita): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($recetita->id); ?>"><?php echo e($recetita->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
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
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="min-width">
                                                <div class="lead">
                                                    <div class="lead-image">
                                                        <img src="<?php echo e($insumo['imagen'] ? asset('storage/' . $insumo['imagen']) : asset('images/recetas.jpg')); ?>"
                                                            alt="">
                                                    </div>
                                                    <div class="lead-text">
                                                        <p><?php echo e($insumo['nombre']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="min-width">
                                                <p><?php echo e($insumo['cantidad']); ?> <?php echo e($insumo['unidad_medida']); ?></p>
                                            </td>
                                            <td class="min-width">
                                                <!--[if BLOCK]><![endif]--><?php if($insumo['stock_suficiente']): ?>
                                                    <span class="status-btn success-btn">
                                                        ✅ <?php echo e($insumo['stock_actual']); ?> <?php echo e($insumo['unidad_medida']); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-btn close-btn">
                                                        ❌ <?php echo e($insumo['stock_actual']); ?> <?php echo e($insumo['unidad_medida']); ?>

                                                    </span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
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

                        <!--[if BLOCK]><![endif]--><?php if($planCalculado && count($insumos) > 0): ?>
                            <div class="input-style-1 mt-3">
                                <label>Nombre del Plan (opcional para guardar)</label>
                                <input type="text" wire:model="nombrePlan" placeholder="Ej: MenuDiario, EventoEspecial">
                            </div>
                            <div class="button-group mt-2">
                                <button wire:click="guardarPlan" class="main-btn success-btn btn-hover">
                                    <i class="lni lni-save"></i> Guardar Plan
                                </button>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!-- Mensajes de éxito/error -->
                <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
                    <div class="alert-box success-alert mt-3">
                        <div class="alert">
                            <p class="text-medium"><?php echo e(session('success')); ?></p>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <?php if(session()->has('error')): ?>
                    <div class="alert-box danger-alert mt-3">
                        <div class="alert">
                            <p class="text-medium"><?php echo e(session('error')); ?></p>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Lista de Planes de Producción Guardados -->
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="title d-flex justify-content-between align-items-center">
                    <h4>📋 Mis Planes de Producción</h4>
                    <button wire:click="togglePlanes" class="main-btn btn-hover">
                        <i class="lni <?php echo e($mostrarPlanes ? 'lni-chevron-up' : 'lni-chevron-down'); ?>"></i>
                        <?php echo e($mostrarPlanes ? 'Ocultar' : 'Mostrar'); ?>

                    </button>
                </div>

                <!--[if BLOCK]><![endif]--><?php if($mostrarPlanes): ?>
                    <div class="table-wrapper table-responsive mt-3">
                        <!--[if BLOCK]><![endif]--><?php if(count($planesProduccion) > 0): ?>
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
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $planesProduccion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($plan->id); ?></td>
                                            <td><strong><?php echo e($plan->nombre); ?></strong></td>
                                            <td><?php echo e($plan->receta->nombre); ?></td>
                                            <td><?php echo e($plan->cantidad); ?> unidades</td>
                                            <td><?php echo e($plan->created_at->format('d/m/Y H:i')); ?></td>
                                            <td>
                                                <div class="action">
                                                    <button class="text-primary me-2"
                                                            wire:click="verDetalle(<?php echo e($plan->id); ?>)"
                                                            title="Ver detalle">
                                                        <i class="lni lni-eye"></i>
                                                    </button>
                                                    <button class="text-warning me-2"
                                                            wire:click="prepararEditar(<?php echo e($plan->id); ?>)"
                                                            title="Editar">
                                                        <i class="lni lni-pencil"></i>
                                                    </button>
                                                    <button class="text-danger"
                                                            wire:click="eliminarPlan(<?php echo e($plan->id); ?>)"
                                                            wire:confirm="¿Está seguro de eliminar este plan?"
                                                            title="Eliminar">
                                                        <i class="lni lni-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="alert-box warning-alert">
                                <div class="alert">
                                    <p class="text-medium">No hay planes de producción guardados</p>
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Modal Ver Detalle -->
        <!--[if BLOCK]><![endif]--><?php if($mostrarDetalle && $planDetalle): ?>
            <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">📋 Detalle del Plan de Producción #<?php echo e($planDetalle->id); ?></h5>
                            <button type="button" class="btn-close" wire:click="cerrarDetalle"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Receta:</strong> <?php echo e($planDetalle->receta->nombre); ?><br>
                                <strong>Cantidad:</strong> <?php echo e($planDetalle->cantidad); ?> unidades<br>
                                <strong>Fecha creación:</strong> <?php echo e($planDetalle->created_at->format('d/m/Y H:i')); ?>

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
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $planDetalle->calcularInsumos(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($insumo['nombre']); ?></td>
                                            <td><?php echo e($insumo['cantidad']); ?> <?php echo e($insumo['unidad_medida']); ?></td>
                                            <td><?php echo e($insumo['stock_actual']); ?> <?php echo e($insumo['unidad_medida']); ?></td>
                                            <td>
                                                <!--[if BLOCK]><![endif]--><?php if($insumo['stock_suficiente']): ?>
                                                    <span class="badge bg-success">✅ Disponible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">❌ Insuficiente</span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cerrarDetalle">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Modal Editar Plan -->
        <!--[if BLOCK]><![endif]--><?php if($planEditar): ?>
            <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">✏️ Editar Plan de Producción #<?php echo e($planEditar->id); ?></h5>
                            <button type="button" class="btn-close" wire:click="cancelarEdicion"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Receta:</strong> <?php echo e($planEditar->receta->nombre); ?>

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
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views\livewire/calculoinsumo.blade.php ENDPATH**/ ?>