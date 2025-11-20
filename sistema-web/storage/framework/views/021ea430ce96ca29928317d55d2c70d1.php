<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Categorias de Insumos')); ?></h2>
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
                    <h6 class="mb-10">Tabla de datos de categorias de insumos</h6>
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero')): ?>
                    <a href="<?php echo e(route('categorias.create')); ?>" class="btn btn-primary">Crear Categoría</a>
                    <?php endif; ?>
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
                                    <th class="lead-email">
                                        <h6>Descripción</h6>
                                    </th>
                                    <th>
                                        <h6>Acciones</h6>
                                    </th>
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="min-width">
                                            <p><?php echo e($categoria->id); ?></p>
                                        </td>
                                        <td class="min-width">
                                            <div class="lead">
                                                <div class="lead-text">
                                                    <p><?php echo e($categoria->nombre); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            <p><?php echo e($categoria->descripcion); ?></p>
                                        </td>
                                        <td>
                                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero')): ?>
                                            <div class="action d-flex" style="gap: 10px;">
                                                <a href="<?php echo e(route('categorias.edit', $categoria->id)); ?>"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #6F4E37; border-color: #6F4E37; font-size: 16px; padding: 8px 16px;">
                                                    EDITAR
                                                </a>
                                                <form action="<?php echo e(route('categorias.destroy', $categoria->id)); ?>"
                                                    method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #6F4E37; border-color: #6F4E37; font-size: 16px; padding: 8px 16px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No existen categorias</td>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/categorias/index.blade.php ENDPATH**/ ?>