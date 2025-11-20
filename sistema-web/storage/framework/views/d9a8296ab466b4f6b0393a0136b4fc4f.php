<?php
use Illuminate\Support\Facades\Storage;
?>

<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Editar Insumo')); ?></h2>
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

                <form action="<?php echo e(route('insumos.update', $insumo->id)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="input-style-1">
                        <label>Nombre del insumo</label>
                        <input type="text" name="nombre" value="<?php echo e(old('nombre', $insumo->nombre)); ?>" placeholder="Integresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción del insumo</label>
                        <textarea placeholder="Descripción.." name="descripcion" rows="5"><?php echo e(old('descripcion', $insumo->descripcion)); ?></textarea>
                    </div>

                    <div class="input-style-1">
                        <label>Stock mínimo</label>
                        <input type="number" name="stock_minimo" value="<?php echo e(old('stock_minimo', $insumo->stock_minimo)); ?>" placeholder="Integresa el stock mínimo" min="0">
                    </div>

                    <div class="input-style-1">
                        <label>Costo estándar (referencial)</label>
                        <input type="number" name="costo_estandar" value="<?php echo e(old('costo_estandar', $insumo->costo_estandar)); ?>" placeholder="Ej: 45.00" min="0" step="0.01">
                        <small class="text-muted">Precio normal por unidad del insumo (solo referencia).</small>
                    </div>

                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                        <?php if($insumo->imagen): ?>
                            <?php
                                $rutaArchivo = storage_path('app/public/' . $insumo->imagen);
                                $imagenUrl = file_exists($rutaArchivo) ? asset('storage/' . $insumo->imagen) : asset('images/cereales.jpg');
                            ?>
                            <div class="mt-2">
                                <span>Imagen actual:</span><br>
                                <img src="<?php echo e($imagenUrl); ?>" alt="Imagen actual" style="max-width: 200px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="select-style-1">
                        <label>Categoria</label>
                        <div class="select-position">
                            <select name="categoria_id">
                                <option value="">Selecciona una categoria</option>
                                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($categoria->id); ?>" <?php echo e($insumo->categoria_id == $categoria->id ? 'selected' : ''); ?>><?php echo e($categoria->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="select-style-1">
                        <label>Unidad de medida</label>
                        <div class="select-position">
                            <select name="unidad_medida_id">
                                <option value="">Selecciona una unidad de medida</option>
                                <?php $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unidad->id); ?>" <?php echo e($insumo->unidad_medida_id == $unidad->id ? 'selected' : ''); ?>><?php echo e($unidad->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Actualizar Insumo</button>
                        <a href="<?php echo e(route('insumos.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/insumos/edit.blade.php ENDPATH**/ ?>