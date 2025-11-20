<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Recetas')); ?></h2>
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
                    <h6 class="mb-10">Tabla de recetas</h6>
                    <p>Estas recetas son platillos que prepara el sistema, cada receta está calculada para 1 porción.</p>
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero')): ?>
                    <a href="<?php echo e(route('recetas.create')); ?>" class="main-btn dark-btn btn-hover mt-3">
                        <i class="lni lni-circle-plus"></i>
                        Crear nuevo
                    </a>
                    <?php endif; ?>

                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-8">
                            <input type="text" name="buscar" class="form-control" value="<?php echo e(request('buscar')); ?>" placeholder="Buscar receta por nombre...">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </form>

                    <div class="table-wrapper table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="lead-info">
                                        <h6>Nombre</h6>
                                    </th>
                                    <th class="lead-precio">
                                        <h6>Precio</h6>
                                    </th>
                                    <th class="lead-email">
                                        <h6>Tiempo de preparación</h6>
                                    </th>
                                    <th class="lead-indicaciones">
                                        <h6>Indicaciones</h6>
                                    </th>
                                    <th>Visible</th>
                                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero')): ?>
                                    <th>
                                        <h6>Acciones</h6>
                                    </th>
                                    <?php endif; ?>
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recetas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="min-width">
                                            <div class="lead">
                                                <div class="lead-image">
                                                    <img src="<?php echo e($receta->imagen ? asset('storage/' . $receta->imagen) : asset('images/recetas.jpg')); ?>" alt="">
                                                </div>
                                                <div class="lead-text">
                                                    <p><?php echo e($receta->nombre); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            <p>$<?php echo e(number_format($receta->precio, 2)); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($receta->tiempo_preparacion); ?> minutos</p>
                                        </td>
                                        <td class="min-width">
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#indicacionesModal<?php echo e($receta->id); ?>">
                                                Ver Indicaciones
                                            </button>
                                            <!-- Modal -->
                                            <div class="modal fade" id="indicacionesModal<?php echo e($receta->id); ?>" tabindex="-1" aria-labelledby="indicacionesModalLabel<?php echo e($receta->id); ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="indicacionesModalLabel<?php echo e($receta->id); ?>"><?php echo e($receta->nombre); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div style="white-space: pre-line;">
                                                                <?php echo nl2br(e($receta->indicaciones)); ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
                                            <form action="<?php echo e(route('recetas.toggleVisible', $receta->id)); ?>" method="POST" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <input type="checkbox" name="visible" onchange="this.form.submit()" <?php echo e($receta->visible ? 'checked' : ''); ?>>
                                            </form>
                                            <?php else: ?>
                                            <input type="checkbox" disabled <?php echo e($receta->visible ? 'checked' : ''); ?>>
                                            <?php endif; ?>
                                        </td>
                                        <?php if (! (auth()->user()->hasRole('mesero'))): ?>
                                        <td>
                                            <div class="action d-flex gap-2">
                                                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero')): ?>
                                                <a href="<?php echo e(route('recetas.edit', $receta->id)); ?>"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #5A2828; border-color: #5A2828; font-size: 14px; padding: 5px 15px; border-radius: 6px;">
                                                    EDITAR
                                                </a>
                                                <form action="<?php echo e(route('recetas.destroy', $receta->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #5A2828; border-color: #5A2828; font-size: 14px; padding: 5px 15px; border-radius: 6px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta receta?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No existen recetas disponibles</td>
                                    </tr>
                                <?php endif; ?>
                                <!-- end table row -->
                            </tbody>
                        </table>
                        <!-- end table -->
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views/recetas/index.blade.php ENDPATH**/ ?>