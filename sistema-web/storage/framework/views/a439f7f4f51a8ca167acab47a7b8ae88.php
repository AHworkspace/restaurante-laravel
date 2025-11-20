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

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Ingresa los datos de la nueva receta que deseas registrar.
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

                <form action="<?php echo e(route('recetas.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="input-style-1">
                        <label>Nombre de la receta</label>
                        <input type="text" name="nombre" placeholder="Ingresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Indicaciones</label>
                        <textarea placeholder="Ingresa las indicaciones que debe tener tu receta.." name="indicaciones" rows="5"></textarea>
                    </div>

                    <!-- stock_minimum -->
                    <div class="input-style-1">
                        <label>Tiempo de preparación</label>
                        <input type="number" name="tiempo_preparacion" placeholder="Tiempo en minutos">
                    </div>

                    <!-- imagen -->
                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                    </div>

                    <div class="input-style-1">
                        <label>Precio</label>
                        <input type="number" name="precio" placeholder="Precio de la receta" min="0" step="0.01">
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
                                    <!-- Aquí se agregarán los insumos dinámicamente -->
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
                                <input type="number" id="cantidad-temp" placeholder="Cantidad">
                            </div>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-success mt-5" id="add-insumo">Agregar Insumo</button>
                        </div>
                        <div class="col-2"></div>
                    </div>

                    <script>
                        let insumoIndex = 0;

                        document.getElementById('add-insumo').addEventListener('click', function() {
                            const tbody = document.getElementById('insumos-table-body');
                            const insumoTemp = document.getElementById('insumo-temp');
                            const cantidadTemp = document.getElementById('cantidad-temp');

                            // Validar que se haya ingresado una cantidad
                            if (!cantidadTemp.value || cantidadTemp.value <= 0) {
                                alert('Debes ingresar una cantidad válida.');
                                return;
                            }

                            // Crear la fila con los datos
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${insumoTemp.options[insumoTemp.selectedIndex].text}</td>
                                <td>${cantidadTemp.value}</td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-insumo">
                                        <i class="lni lni-trash-can"></i>
                                    </button>
                                </td>
                            `;

                            // Agregar campos ocultos para enviar los datos al backend
                            const hiddenId = document.createElement('input');
                            hiddenId.type = 'hidden';
                            hiddenId.name = `insumos[${insumoIndex}][id]`;
                            hiddenId.value = insumoTemp.value;

                            const hiddenCantidad = document.createElement('input');
                            hiddenCantidad.type = 'hidden';
                            hiddenCantidad.name = `insumos[${insumoIndex}][cantidad]`;
                            hiddenCantidad.value = cantidadTemp.value;

                            row.appendChild(hiddenId);
                            row.appendChild(hiddenCantidad);

                            tbody.appendChild(row);

                            // Limpiar campos temporales
                            cantidadTemp.value = '';

                            // Manejar la eliminación de la fila
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

                    <!-- categoria_id -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar Receta</button>
                        <a href="<?php echo e(route('recetas.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/recetas/create.blade.php ENDPATH**/ ?>