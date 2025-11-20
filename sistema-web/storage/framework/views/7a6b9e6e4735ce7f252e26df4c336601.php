<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            <?php echo e(__('Movimientos de Inventario')); ?>

                        </span>
                        <div class="float-right">
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                            <a href="<?php echo e(route('movimientos.create')); ?>"
                               class="btn"
                               style="background-color: #5A2828; color: white; border-radius: 5px; padding: 8px 20px;">
                                <?php echo e(__('CREAR NUEVO')); ?>

                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($message = Session::get('success')): ?>
                    <div class="alert alert-success">
                        <p><?php echo e($message); ?></p>
                    </div>
                <?php endif; ?>

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
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>No</th>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Tipo</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(++$i); ?></td>
                                        <td><?php echo e($movimiento->insumo->nombre); ?></td>
                                        <td><?php echo e($movimiento->cantidad); ?></td>
                                        <td>
                                            <span class="badge <?php echo e($movimiento->tipo == 'entrada' ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo e($movimiento->tipo); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($movimiento->motivo); ?></td>
                                        <td><?php echo e($movimiento->created_at->format('d/m/Y')); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('movimientos.destroy',$movimiento->id)); ?>" method="POST" style="display: inline-flex; gap: 8px;">
                                                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                                                <a class="btn"
                                                   href="<?php echo e(route('movimientos.edit',$movimiento->id)); ?>"
                                                   style="background-color: #5A2828; color: white; border-radius: 5px; padding: 8px 20px;">
                                                    EDITAR
                                                </a>
                                                <?php endif; ?>
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                                                <button type="submit"
                                                        class="btn"
                                                        style="background-color: #5A2828; color: white; border-radius: 5px; padding: 8px 20px;">
                                                    ELIMINAR
                                                </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php echo $movimientos->links(); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views/movimientos/index.blade.php ENDPATH**/ ?>