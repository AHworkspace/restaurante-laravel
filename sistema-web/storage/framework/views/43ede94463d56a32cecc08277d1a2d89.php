<?php $__env->startSection('content'); ?>
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2><?php echo e(__('Registro de Ventas')); ?></h2>
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
                            Ingresa los datos de tu venta, es importante recalcar qu cada venta representa a un plato
                            vendido.
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

                <form action="<?php echo e(route('ventas.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="select-style-1">
                        <label>¿Que plato vendiste?</label>
                        <div class="select-position">
                            <select name="receta_id" id="receta_id">
                                <option value="">Selecciona una receta</option>
                                <?php $__currentLoopData = $recetas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($receta->id); ?>" data-precio="<?php echo e($receta->precio); ?>"><?php echo e($receta->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="input-style-1">
                        <label>¿Cuantas unidades vendiste?</label>
                        <input type="number" name="cantidad" placeholder="Ingresa la cantidad" min="0"
                            step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>

                    <div class="input-style-1">
                        <label>¿A cuanto vendiste cada plato? (Bs.)</label>
                        <input type="number" name="precio" id="precio" placeholder="Ingresa el precio por unidad en Bs." min="0"
                            step="0.01" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
                    </div>
                    <div class="input-style-1">
                        <label>Precio total (Bs.)</label>
                        <input type="number" name="total" value="">
                    </div>
                    <div class="input-style-1">
                        <label>Fecha de la venta</label>
                        <input type="date" name="fecha" value="<?php echo e(date('Y-m-d')); ?>" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar Venta</button>
                        <a href="<?php echo e(route('ventas.index')); ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cantidadInput = document.querySelector('input[name="cantidad"]');
            const precioInput = document.querySelector('input[name="precio"]');
            const totalInput = document.querySelector('input[name="total"]');
            const recetaSelect = document.getElementById('receta_id');

            function calculateTotal() {
                const cantidad = parseFloat(cantidadInput.value) || 0;
                const precio = parseFloat(precioInput.value) || 0;
                totalInput.value = cantidad * precio;
            }

            cantidadInput.addEventListener('input', calculateTotal);
            precioInput.addEventListener('input', calculateTotal);

            recetaSelect.addEventListener('change', function() {
                const selected = recetaSelect.options[recetaSelect.selectedIndex];
                const precio = selected.getAttribute('data-precio');
                if (precio !== null) {
                    precioInput.value = precio;
                    calculateTotal();
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/ventas/create.blade.php ENDPATH**/ ?>