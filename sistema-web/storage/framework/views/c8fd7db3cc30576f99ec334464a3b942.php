<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Insumos')); ?></h2>
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
                            Ingresa los datos del insumo
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

                <form action="<?php echo e(route('insumos.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="input-style-1">
                        <label>Nombre del insumo</label>
                        <input type="text" name="nombre" placeholder="Integresa el nombre" value="<?php echo e(old('nombre')); ?>">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción del insumo</label>
                        <textarea placeholder="Descripción.." name="descripcion" rows="5"><?php echo e(old('descripcion')); ?></textarea>
                    </div>

                    <!-- stock_minimum -->
                    <div class="input-style-1">
                        <label>Stock mínimo</label>
                        <input type="number" name="stock_minimo" placeholder="Integresa el stock mínimo" min="0" value="<?php echo e(old('stock_minimo')); ?>">
                    </div>

                    <div class="input-style-1">
                        <label>Costo estándar (referencial)</label>
                        <input type="number" name="costo_estandar" placeholder="Ej: 45.00" min="0" step="0.01" value="<?php echo e(old('costo_estandar')); ?>">
                        <small class="text-muted">Precio normal por unidad del insumo (solo referencia).</small>
                    </div>

                    <!-- imagen -->
                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                    </div>

                    <!-- categoria_id -->
                    <div class="select-style-1">
                        <label>Categoria</label>
                        <div class="select-position">
                            <select name="categoria_id">
                                <option value="">Selecciona una categoria</option>
                                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($categoria->id); ?>" <?php echo e(old('categoria_id') == $categoria->id ? 'selected' : ''); ?>><?php echo e($categoria->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <!-- unidad_medida -->
                    <div class="select-style-1">
                        <label>Unidad de medida</label>
                        <div class="select-position">
                            <select name="unidad_medida_id">
                                <option value="">Selecciona una unidad de medida</option>
                                <?php $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unidad->id); ?>" <?php echo e(old('unidad_medida_id') == $unidad->id ? 'selected' : ''); ?>><?php echo e($unidad->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar Categoria</button>
                        <a href="<?php echo e(route('insumos.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/insumos/create.blade.php ENDPATH**/ ?>