<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-dark text-white rounded-top-4" style="font-weight: bold; font-size: 1.3rem;">Historial de Acciones</div>
                <div class="card-body bg-light rounded-bottom-4">
                    <form method="GET" class="mb-3 row g-2 align-items-end filtros-historial">
                        <div class="col-md-2">
                            <select name="usuario" class="form-control">
                                <option value="">Todos los usuarios</option>
                                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($usuario); ?>" <?php echo e(request('usuario') == $usuario ? 'selected' : ''); ?>><?php echo e($usuario); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="rol" class="form-control">
                                <option value="">Todos los roles</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($rol); ?>" <?php echo e(request('rol') == $rol ? 'selected' : ''); ?>><?php echo e($rol); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="accion" class="form-control" placeholder="Acción" value="<?php echo e(request('accion')); ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="seccion" class="form-control">
                                <option value="">Todas las secciones</option>
                                <?php $__currentLoopData = $secciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seccion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($seccion); ?>" <?php echo e(request('seccion') == $seccion ? 'selected' : ''); ?>><?php echo e($seccion); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo e(request('fecha_inicio')); ?>">
                        </div>
                        <div class="col-md-1">
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo e(request('fecha_fin')); ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn w-100">FILTRAR</button>
                        </div>
                        <div class="col-md-1">
                            <a href="<?php echo e(route('historial.index')); ?>" class="btn btn-secondary w-100">LIMPIAR</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table historial-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Sección</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Acción</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="<?php if($loop->even): ?> table-light <?php endif; ?>">
                                        <td><?php echo e($item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : ''); ?></td>
                                        <td><?php echo e($item->hora); ?></td>
                                        <td><?php echo e($item->seccion); ?></td>
                                        <td><?php echo e($item->usuario); ?></td>
                                        <td><?php echo e($item->rol); ?></td>
                                        <td><?php echo e($item->accion); ?></td>
                                        <td><?php echo e($item->detalles); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay acciones registradas en el historial</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <?php echo e($historial->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .historial-table {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
        background: #fff;
    }
    .historial-table th, .historial-table td {
        border: none !important;
        border-bottom: 1.5px solid #e0e0e0 !important;
        padding: 1.1rem 1.5rem !important;
        font-size: 1.13rem;
        vertical-align: middle !important;
        background: #fff !important;
    }
    .historial-table th {
        background: #f3eee6 !important;
        color: #7A5C58 !important;
        font-weight: 600;
    }
    .historial-table tr {
        height: 60px;
    }
    .historial-table tbody tr:nth-child(even) td {
        background: #faf6f3 !important;
    }
    .historial-table tbody tr:hover td {
        background: #ede3d9 !important;
    }
    .filtros-historial input, .filtros-historial button, .filtros-historial select {
        border-radius: 0.6rem !important;
        font-size: 1.05rem;
    }
    .filtros-historial button {
        background: #6F4E37;
        color: #fff;
        font-weight: 600;
        border: none;
        padding: 0.5rem 1.2rem;
        transition: background 0.2s;
    }
    .filtros-historial button:hover {
        background: #5D403D;
    }
    .filtros-historial .btn-secondary {
        background: #6c757d;
        color: #fff;
        font-weight: 600;
        border: none;
        padding: 0.5rem 1.2rem;
        transition: background 0.2s;
    }
    .filtros-historial .btn-secondary:hover {
        background: #5a6268;
    }
    .filtros-historial select {
        background-color: #fff;
        border: 1px solid #ced4da;
        color: #495057;
    }
    .filtros-historial select:focus {
        border-color: #7A5C58;
        box-shadow: 0 0 0 0.2rem rgba(122, 92, 88, 0.25);
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/historial/index.blade.php ENDPATH**/ ?>