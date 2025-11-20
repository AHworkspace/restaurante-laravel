<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background-color: #6F4E37; color: white;">
                    <h4 class="mb-0">Editar Movimiento de Inventario</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('movimientos.update', $movimiento)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="mb-3">
                            <label for="insumo_id" class="form-label">Insumo</label>
                            <select name="insumo_id" id="insumo_id" class="form-control" required>
                                <option value="">Seleccione un insumo</option>
                                <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($insumo->id); ?>" <?php echo e(old('insumo_id', $movimiento->insumo_id) == $insumo->id ? 'selected' : ''); ?>><?php echo e($insumo->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control" min="0.01" step="0.01" required value="<?php echo e(old('cantidad', $movimiento->cantidad_original ?? $movimiento->cantidad)); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="unidad_medida_id" class="form-label">Unidad de Medida</label>
                            <select name="unidad_medida_id" id="unidad_medida_id" class="form-control">
                                <option value="">Seleccione unidad (por defecto: unidad del insumo)</option>
                                <?php $__currentLoopData = $unidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unidad->id); ?>" <?php echo e(old('unidad_medida_id', $movimiento->unidad_medida_id) == $unidad->id ? 'selected' : ''); ?>>
                                        <?php echo e($unidad->nombre); ?> (<?php echo e($unidad->abreviatura); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small id="unidad-base-info" class="form-text text-muted"></small>
                            <small id="conversion-info" class="form-text text-info" style="display: none;"></small>
                            <?php if($movimiento->cantidad_convertida && $movimiento->cantidad_original != $movimiento->cantidad_convertida): ?>
                                <small class="form-text text-success">
                                    ℹ️ Conversión actual: <?php echo e(number_format($movimiento->cantidad_original, 2)); ?>

                                    = <?php echo e(number_format($movimiento->cantidad_convertida, 2)); ?>

                                    <?php echo e($movimiento->insumo->unidad_medida->abreviatura); ?>

                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Movimiento</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="entrada" <?php echo e(old('tipo', $movimiento->tipo) == 'entrada' ? 'selected' : ''); ?>>Entrada</option>
                                <option value="salida" <?php echo e(old('tipo', $movimiento->tipo) == 'salida' ? 'selected' : ''); ?>>Salida</option>
                            </select>
                        </div>
                        <div id="entrada-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="costo_compra" class="form-label">Costo total de esta compra (Bs.)</label>
                                <input type="number" name="costo_compra" id="costo_compra" class="form-control" min="0" step="0.01" value="<?php echo e(old('costo_compra', optional($movimiento->compra)->costo_total)); ?>">
                                <small id="costo-sugerido" class="form-text text-muted"></small>
                            </div>
                            <div class="mb-3">
                                <label for="proveedor_id" class="form-label">Proveedor</label>
                                <select name="proveedor_id" id="proveedor_id" class="form-control">
                                    <option value="">Seleccione un proveedor</option>
                                    <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proveedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($proveedor->id); ?>" <?php echo e(old('proveedor_id', optional($movimiento->compra)->proveedor_id) == $proveedor->id ? 'selected' : ''); ?>>
                                            <?php echo e($proveedor->nombre); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="detalle_compra" class="form-label">Detalle de la compra (opcional)</label>
                                <textarea name="detalle_compra" id="detalle_compra" class="form-control" rows="2"><?php echo e(old('detalle_compra', optional($movimiento->compra)->descripcion)); ?></textarea>
                            </div>
                        </div>
                        <div id="salida-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="receta_id" class="form-label">Receta donde se usará (opcional)</label>
                                <select name="receta_id" id="receta_id" class="form-control">
                                    <option value="">Selecciona una receta</option>
                                    <?php $__currentLoopData = $recetas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($receta->id); ?>" <?php echo e(old('receta_id') == $receta->id ? 'selected' : ''); ?>><?php echo e($receta->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="form-text text-muted">Solo es informativo, no afecta las ventas.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo</label>
                        <input type="text" name="motivo" id="motivo" class="form-control" maxlength="100" required value="<?php echo e(old('motivo', $movimiento->motivo)); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" required value="<?php echo e(old('fecha', $movimiento->created_at->format('Y-m-d'))); ?>">
                        </div>
                        <div class="text-end">
                            <a href="<?php echo e(route('movimientos.index')); ?>" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary" style="background-color: #6F4E37; border-color: #6F4E37;">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    $insumosJson = $insumos->mapWithKeys(function($insumo) {
        return [$insumo->id => [
            'unidad_id' => $insumo->unidad_medida->id ?? null,
            'unidad_nombre' => $insumo->unidad_medida->nombre ?? '',
            'unidad_abrev' => $insumo->unidad_medida->abreviatura ?? '',
            'costo_estandar' => $insumo->costo_estandar,
        ]];
    })->toArray();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const insumoSelect = document.getElementById('insumo_id');
    const unidadSelect = document.getElementById('unidad_medida_id');
    const cantidadInput = document.getElementById('cantidad');
    const unidadBaseInfo = document.getElementById('unidad-base-info');
    const conversionInfo = document.getElementById('conversion-info');
    const tipoSelect = document.getElementById('tipo');
    const entradaFields = document.getElementById('entrada-fields');
    const salidaFields = document.getElementById('salida-fields');
    const costoInput = document.getElementById('costo_compra');
    const costoSugerido = document.getElementById('costo-sugerido');

    const insumosData = <?php echo json_encode($insumosJson, 15, 512) ?>;

    function actualizarUnidadBase() {
        const insumoId = insumoSelect.value;
        if (insumoId && insumosData[insumoId]) {
            const unidad = insumosData[insumoId];
            unidadBaseInfo.textContent = 'ℹ️ Unidad base del insumo: ' + unidad.unidad_nombre + ' (' + unidad.unidad_abrev + ')';

            // Si no hay unidad seleccionada, pre-seleccionar unidad base
            if (!unidadSelect.value) {
                unidadSelect.value = unidad.unidad_id;
            }
        } else {
            unidadBaseInfo.textContent = '';
        }
        actualizarConversion();
    }

    function actualizarConversion() {
        const insumoId = insumoSelect.value;
        const unidadId = unidadSelect.value;
        const cantidad = parseFloat(cantidadInput.value);

        if (insumoId && unidadId && cantidad && insumosData[insumoId]) {
            const unidadBase = insumosData[insumoId];
            if (unidadId != unidadBase.unidad_id) {
                conversionInfo.style.display = 'block';
                conversionInfo.textContent = '💡 La cantidad se convertirá automáticamente a ' + unidadBase.unidad_abrev;
            } else {
                conversionInfo.style.display = 'none';
            }
        } else {
            conversionInfo.style.display = 'none';
        }

        actualizarCostoSugerido();
    }

    function actualizarTipoCampos() {
        if (tipoSelect.value === 'entrada') {
            entradaFields.style.display = 'block';
            salidaFields.style.display = 'none';
            actualizarCostoSugerido();
        } else {
            entradaFields.style.display = 'none';
            salidaFields.style.display = 'block';
        }
    }

    function actualizarCostoSugerido() {
        if (tipoSelect.value !== 'entrada') {
            costoSugerido.textContent = '';
            return;
        }
        const insumoId = insumoSelect.value;
        const cantidad = parseFloat(cantidadInput.value);
        if (insumoId && insumosData[insumoId] && insumosData[insumoId].costo_estandar && cantidad) {
            const sugerido = (insumosData[insumoId].costo_estandar * cantidad).toFixed(2);
            costoSugerido.textContent = 'Sugerido: Bs. ' + sugerido + ' ( ' + cantidad + ' × ' + insumosData[insumoId].costo_estandar + ' )';
            if (!costoInput.value) {
                costoInput.value = sugerido;
            }
        } else {
            costoSugerido.textContent = 'Sin costo estándar registrado para este insumo.';
        }
    }

    insumoSelect.addEventListener('change', actualizarUnidadBase);
    unidadSelect.addEventListener('change', actualizarConversion);
    cantidadInput.addEventListener('input', actualizarConversion);
    tipoSelect.addEventListener('change', actualizarTipoCampos);

    // Inicializar al cargar
    actualizarUnidadBase();
    actualizarTipoCampos();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/movimientos/edit.blade.php ENDPATH**/ ?>