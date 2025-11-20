<?php
use Illuminate\Support\Facades\Storage;
?>

<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Ventas')); ?></h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="tables-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card-style mb-30">
                    <h6 class="mb-10">Tabla de ventas</h6>
                    <p>Registra las ventas que hiciste en el día o el momento, no importa cuantos platos el sistema calcula
                        cuanto de insumos gastaste.</p>
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cajero')): ?>
                    <a href="<?php echo e(route('ventas.create')); ?>" class="main-btn dark-btn btn-hover mt-3">
                        <i class="lni lni-circle-plus"></i>
                        Crear nuevo
                    </a>
                    <?php endif; ?>
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-4">
                            <select name="receta_id" class="form-control">
                                <option value="">Todas las recetas</option>
                                <?php $__currentLoopData = $recetas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($receta->id); ?>" <?php echo e(request('receta_id') == $receta->id ? 'selected' : ''); ?>><?php echo e($receta->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo e(request('fecha_inicio')); ?>" placeholder="Fecha inicio">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo e(request('fecha_fin')); ?>" placeholder="Fecha final">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                    <div class="table-wrapper table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="">
                                        <h6>Identificador</h6>
                                    </th>
                                    <th>
                                        <h6>Receta</h6>
                                    </th>
                                    <th>
                                        <h6>Fecha</h6>
                                    </th>
                                    <th>
                                        <h6>Cantidad</h6>
                                    </th>
                                    <th>
                                        <h6>Precio porción</h6>
                                    </th>
                                    <th>
                                        <h6>Total</h6>
                                    </th>
                                    <th>
                                        <h6>Acciones</h6>
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $ventas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="min-width">
                                            <p><?php echo e($venta->id); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <?php
                                                $imagenUrl = asset('images/recetas.jpg');
                                                if ($venta->receta->imagen) {
                                                    $rutaArchivo = storage_path('app/public/' . $venta->receta->imagen);
                                                    if (file_exists($rutaArchivo)) {
                                                        $imagenUrl = asset('storage/' . $venta->receta->imagen);
                                                    }
                                                }
                                            ?>
                                            <div class="lead">
                                                <div class="lead-image">
                                                    <img src="<?php echo e($imagenUrl); ?>"
                                                        alt="">
                                                </div>
                                                <div class="lead-text">
                                                    <p><?php echo e($venta->receta->nombre); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($venta->created_at->format('d/m/Y')); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($venta->cantidad); ?> Porciones</p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($venta->precio); ?> Bs.</p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($venta->total); ?> Bs.</p>
                                        </td>
                                        <td>
                                            <div class="action d-flex gap-2">
                                                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cajero')): ?>
                                                <a href="<?php echo e(route('ventas.edit', $venta->id)); ?>"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;">
                                                    EDITAR
                                                </a>
                                                <form action="<?php echo e(route('ventas.destroy', $venta->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta venta?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No existen ventas disponibles</td>
                                    </tr>
                                <?php endif; ?>
                                <!-- end table row -->
                            </tbody>
                        </table>
                        <!-- end table -->
                    </div>
                    <!-- Paginación -->
                    <div class="mt-3">
                        <?php echo e($ventas->links()); ?>

                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/ventas/index.blade.php ENDPATH**/ ?>