<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Proveedores')); ?></h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="card">
        <div class="card-header">
            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
            <a href="<?php echo e(route('proveedores.create')); ?>" class="btn btn-primary">Crear Proveedor</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($proveedor->id); ?></td>
                            <td><?php echo e($proveedor->nombre); ?></td>
                            <td><?php echo e($proveedor->direccion); ?></td>
                            <td><?php echo e($proveedor->telefono); ?></td>
                            <td><?php echo e($proveedor->email); ?></td>
                            <td><?php echo e($proveedor->descripcion ?? 'Sin descripción'); ?></td>
                            <td>
                                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                                <a href="<?php echo e(route('proveedores.edit', $proveedor->id)); ?>"
                                    class="btn btn-warning">Editar</a>
                                <form action="<?php echo e(route('proveedores.destroy', $proveedor->id)); ?>" method="POST"
                                    style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Seguro de eliminar?')">Eliminar</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/proveedores/index.blade.php ENDPATH**/ ?>