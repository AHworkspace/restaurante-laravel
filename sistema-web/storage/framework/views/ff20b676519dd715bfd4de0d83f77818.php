<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Insumos del Restaurante')); ?></h2>
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
                    <h6 class="mb-10">Tabla de todos los insumos del restaurante.</h6>
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                    <a href="<?php echo e(route('insumos.create')); ?>" class="btn btn-primary">Crear Insumo</a>
                    <?php endif; ?>
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-4">
                            <input type="text" name="buscar" class="form-control" value="<?php echo e(request('buscar')); ?>" placeholder="Buscar insumo por nombre...">
                        </div>
                        <div class="col-md-4">
                            <select name="categoria" class="form-control">
                                <option value="">Todas las categorías</option>
                                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($categoria->id); ?>" <?php echo e(request('categoria') == $categoria->id ? 'selected' : ''); ?>><?php echo e($categoria->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="stock_minimo" class="form-control" value="<?php echo e(request('stock_minimo')); ?>" placeholder="Stock mínimo">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </form>
                    <div class="table-wrapper table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <h6>ID</h6>
                                    </th>
                                    <th class="lead-info">
                                        <h6>Nombre</h6>
                                    </th>
                                    <th>
                                        <h6>Costo Estándar</h6>
                                    </th>
                                    <th class="lead-email">
                                        <h6>Descripción</h6>
                                    </th>
                                    <th>
                                        <h6>Stock Minimo</h6>
                                    </th>
                                    <th>
                                        <h6>Categoria</h6>
                                    </th>
                                    <th>
                                        <h6>Stock Disponible</h6>
                                    </th>
                                    <th>
                                        <h6>Acciones</h6>
                                    </th>
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="min-width">
                                            <p><?php echo e($insumo->id); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <div class="lead">
                                                <?php
                                                    $imagenUrl = asset('images/cereales.jpg');
                                                    if ($insumo->imagen) {
                                                        $rutaArchivo = storage_path('app/public/' . $insumo->imagen);
                                                        if (file_exists($rutaArchivo)) {
                                                            $imagenUrl = asset('storage/' . $insumo->imagen);
                                                        }
                                                    }
                                                ?>
                                                <div class="lead-image">
                                                    <img src="<?php echo e($imagenUrl); ?>" alt="">
                                                </div>
                                                <div class="lead-text">
                                                    <p><?php echo e($insumo->nombre); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            <?php if($insumo->costo_estandar): ?>
                                                <p>Bs. <?php echo e(number_format($insumo->costo_estandar, 2)); ?> / <?php echo e($insumo->unidad_medida->abreviatura); ?></p>
                                            <?php else: ?>
                                                <p class="text-muted">-</p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($insumo->descripcion); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($insumo->stock_minimo); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($insumo->categoria->nombre); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e(max(0, $insumo->getCantidadTotal())); ?> <?php echo e($insumo->unidad_medida->abreviatura); ?></p>
                                        </td>
                                        <td>
                                            <div class="action d-flex gap-2">
                                                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                                                <a href="<?php echo e(route('insumos.edit', $insumo->id)); ?>"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;">
                                                    EDITAR
                                                </a>
                                                <form action="<?php echo e(route('insumos.destroy', $insumo->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este insumo?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No existen categorias</td>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/insumos/index.blade.php ENDPATH**/ ?>