<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header" style="color: #7A5C58; font-weight: bold; font-size: 1.3rem;">Historial de Notificaciones de Insumos</div>
                <div class="card-body">
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-4">
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo e(request('fecha_inicio')); ?>" placeholder="Fecha inicio">
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo e(request('fecha_fin')); ?>" placeholder="Fecha final">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                    <ul class="list-group">
                        <?php $__empty_1 = true; $__currentLoopData = $notificaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $insumo = $notification->data['insumo_id'] ? \App\Models\Insumo::find($notification->data['insumo_id']) : null;
                                if ($insumo instanceof \Illuminate\Database\Eloquent\Collection) {
                                    $insumo = $insumo->first();
                                }
                                $esAlerta = isset($notification->data['message']) && str_contains($notification->data['message'], '⚠️');
                            ?>
                            <li class="list-group-item d-flex align-items-center <?php echo e($esAlerta ? 'bg-danger bg-opacity-10 border-danger' : (is_null($notification->read_at) ? 'bg-light fw-bold' : 'text-muted')); ?>" style="border-left: 5px solid #e53935;">
                                <?php if($esAlerta): ?>
                                    <span style="width: 22px; height: 22px; background: #e53935; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                        <i class="fas fa-exclamation-triangle" style="color: #fff; font-size: 14px;"></i>
                                    </span>
                                <?php elseif(str_contains($notification->data['message'] ?? '', '✅')): ?>
                                    <span style="width: 16px; height: 16px; background: #4caf50; border-radius: 50%; display: inline-block; margin-right: 10px;"></span>
                                <?php endif; ?>
                                <div style="flex:1;">
                                    <?php if($esAlerta): ?>
                                        <div><b>Insumo:</b> <?php echo e($notification->data['nombre_insumo'] ?? ($insumo?->nombre ?? '-')); ?></div>
                                        <div><b>Categoría:</b> <?php echo e($notification->data['categoria'] ?? ($insumo?->categoria?->nombre ?? '-')); ?></div>
                                        <div><b>Stock mínimo:</b> <?php echo e($notification->data['stock_minimo'] ?? ($insumo?->stock_minimo ?? '-')); ?></div>
                                        <div><b>Stock actual:</b> <?php echo e($notification->data['stock_actual'] ?? ($insumo?->getCantidadTotal() ?? '-')); ?></div>
                                        <div><b>Salida:</b> <?php echo e($notification->data['cantidad_salida'] ?? '-'); ?></div>
                                        <div><b>Fecha:</b> <?php echo e($notification->data['fecha'] ?? $notification->created_at->format('d/m/Y')); ?></div>
                                        <div><b>Hora:</b> <?php echo e($notification->data['hora'] ?? $notification->created_at->format('H:i')); ?></div>
                                        <div class="mt-1"><?php echo e($notification->data['message'] ?? 'Sin mensaje'); ?></div>
                                    <?php else: ?>
                                        <div><b>Insumo:</b> <?php echo e($insumo?->nombre ?? '-'); ?></div>
                                        <div><b>Categoría:</b> <?php echo e($insumo?->categoria?->nombre ?? '-'); ?></div>
                                        <div><b>Stock mínimo:</b> <?php echo e($insumo?->stock_minimo ?? '-'); ?></div>
                                        <div><b>Stock actual:</b> <?php echo e($insumo?->getCantidadTotal() ?? '-'); ?></div>
                                        <div><b>Fecha:</b> <?php echo e($notification->data['fecha'] ?? $notification->created_at->format('d/m/Y H:i')); ?></div>
                                        <div class="mt-1"><?php echo e(str_replace(" el día " . ($notification->data['fecha'] ?? ''), '', $notification->data['message'] ?? 'Sin mensaje')); ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="list-group-item text-center text-muted">No tienes notificaciones de insumos</li>
                        <?php endif; ?>
                    </ul>
                    <div class="mt-3">
                        <?php echo e($notificaciones->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/notificaciones/index.blade.php ENDPATH**/ ?>