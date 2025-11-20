<?php
use Illuminate\Support\Facades\Storage;
?>

<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Editar Receta')); ?></h2>
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

                <form action="<?php echo e(route('recetas.update', $receta->id)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="input-style-1">
                        <label>Nombre de la receta</label>
                        <input type="text" name="nombre" value="<?php echo e($receta->nombre); ?>" placeholder="Ingresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción (para el menú principal)</label>
                        <textarea placeholder="Ingresa una descripción corta que se mostrará en el menú principal. Ej: 'Una pizza clásica italiana con masa fina, salsa de tomate, mozzarella fresca y albahaca. Perfecta para una cena ligera.'" name="descripcion" rows="3"><?php echo e(old('descripcion', $receta->descripcion)); ?></textarea>
                        <small class="text-muted">Esta descripción aparecerá en las tarjetas del menú principal.</small>
                    </div>

                    <div class="input-style-1">
                        <label>Indicaciones (instrucciones de preparación)</label>
                        <textarea placeholder="Ingresa las indicaciones que debe tener tu receta.." name="indicaciones" rows="5"><?php echo e($receta->indicaciones); ?></textarea>
                    </div>

                    <div class="input-style-1">
                        <label>Tiempo de preparación</label>
                        <input type="number" name="tiempo_preparacion" value="<?php echo e($receta->tiempo_preparacion); ?>" placeholder="Tiempo en minutos">
                    </div>

                    <div class="input-style-1">
                        <label>Precio</label>
                        <input type="number" name="precio" value="<?php echo e($receta->precio); ?>" placeholder="Precio de la receta" min="0" step="0.01">
                    </div>

                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                        <?php if($receta->imagen): ?>
                            <?php
                                $rutaArchivo = storage_path('app/public/' . $receta->imagen);
                                $imagenUrl = file_exists($rutaArchivo) ? asset('storage/' . $receta->imagen) : asset('images/recetas.jpg');
                            ?>
                            <div class="mt-2">
                                <span>Imagen actual:</span><br>
                                <img src="<?php echo e($imagenUrl); ?>" alt="Imagen actual" style="max-width: 200px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- insumos -->
                    <div class="input-style-1">
                        <label>Insumos</label>
                        <div class="table-wrapper table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="insumos-table-body">
                                    <?php $__currentLoopData = $receta->insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($insumo->nombre); ?></td>
                                            <td><?php echo e($insumo->pivot->cantidad); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-danger remove-insumo">
                                                    <i class="lni lni-trash-can"></i>
                                                </button>
                                            </td>
                                            <td style="display:none">
                                                <input type="hidden" name="insumos[<?php echo e($index); ?>][id]" value="<?php echo e($insumo->id); ?>">
                                                <input type="hidden" name="insumos[<?php echo e($index); ?>][cantidad]" value="<?php echo e($insumo->pivot->cantidad); ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div class="select-style-1">
                                <label>Insumo</label>
                                <div class="select-position">
                                    <select id="insumo-temp">
                                        <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($insumo->id); ?>"><?php echo e($insumo->nombre); ?> en <?php echo e($insumo->unidad_medida->abreviatura); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="input-style-1">
                                <label>Cantidad Requerida</label>
                                <input type="number" id="cantidad-temp" placeholder="Cantidad" min="1">
                            </div>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-success mt-5" id="add-insumo">Agregar Insumo</button>
                        </div>
                        <div class="col-2"></div>
                    </div>

                    <script>
                        // Asignar evento a los botones de eliminar ya existentes al cargar la página
                        document.querySelectorAll('.remove-insumo').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                btn.closest('tr').remove();
                            });
                        });
                        let insumoIndex = <?php echo e($receta->insumos->count()); ?>;
                        document.getElementById('add-insumo').addEventListener('click', function() {
                            const tbody = document.getElementById('insumos-table-body');
                            const insumoTemp = document.getElementById('insumo-temp');
                            const cantidadTemp = document.getElementById('cantidad-temp');
                            if (!cantidadTemp.value || cantidadTemp.value <= 0) {
                                alert('Debes ingresar una cantidad válida.');
                                return;
                            }
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${insumoTemp.options[insumoTemp.selectedIndex].text}</td>
                                <td>${cantidadTemp.value}</td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-insumo">
                                        <i class="lni lni-trash-can"></i>
                                    </button>
                                </td>
                                <td style="display:none">
                                    <input type="hidden" name="insumos[${insumoIndex}][id]" value="${insumoTemp.value}">
                                    <input type="hidden" name="insumos[${insumoIndex}][cantidad]" value="${cantidadTemp.value}">
                                </td>
                            `;
                            tbody.appendChild(row);
                            cantidadTemp.value = '';
                            row.querySelector('.remove-insumo').addEventListener('click', function() {
                                row.remove();
                            });
                            insumoIndex++;
                        });
                        document.querySelector('form').addEventListener('submit', function(event) {
                            const insumos = document.querySelectorAll('input[name^="insumos["]');
                            if (insumos.length === 0) {
                                alert('Debes agregar al menos un insumo.');
                                event.preventDefault();
                            }
                        });
                    </script>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Actualizar Receta</button>
                        <a href="<?php echo e(route('recetas.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/recetas/edit.blade.php ENDPATH**/ ?>