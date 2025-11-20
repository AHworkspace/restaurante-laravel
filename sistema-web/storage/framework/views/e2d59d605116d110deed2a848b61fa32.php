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

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Ingresa los datos de las categorias
                        </p>
                    </div>
                </div>
                <?php if($errors->any()): ?>
                    <div class="alert-box danger-alert">
                        <div class="alert">
                            <ul>
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="text-medium"><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('categorias.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="input-style-1">
                        <label>Nombre de la Categoria</label>
                        <input type="text" name="nombre" placeholder="Integresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción de la categoria</label>
                        <textarea placeholder="Descripción.." name="descripcion" rows="5"></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar Categoria</button>
                        <a href="<?php echo e(route('categorias.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/categorias/create.blade.php ENDPATH**/ ?>