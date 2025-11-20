<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            <?php echo e(__('Movimientos de Inventario')); ?>

                        </span>
                        <div class="float-right">
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                            <a href="<?php echo e(route('movimientos.create')); ?>"
                               class="btn"
                               style="background-color: #6F4E37; color: white; border-radius: 5px; padding: 8px 20px;">
                                <?php echo e(__('CREAR NUEVO')); ?>

                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($message = Session::get('success')): ?>
                    <div class="alert alert-success">
                        <p><?php echo e($message); ?></p>
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <?php
                        $tipoFiltro = request('tipo');
                        $esEntrada = $tipoFiltro === 'entrada';
                        $esSalida = $tipoFiltro === 'salida';
                    ?>
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-3">
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo e(request('fecha_inicio')); ?>" placeholder="Fecha inicio">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo e(request('fecha_fin')); ?>" placeholder="Fecha final">
                        </div>
                        <div class="col-md-3">
                            <select name="tipo" class="form-control">
                                <option value="">Tipo (todos)</option>
                                <option value="entrada" <?php echo e(request('tipo') === 'entrada' ? 'selected' : ''); ?>>Entradas</option>
                                <option value="salida" <?php echo e(request('tipo') === 'salida' ? 'selected' : ''); ?>>Salidas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>No</th>
                                    <th>Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Unidad</th>
                                    <?php if($esEntrada): ?>
                                        <th>Compra</th>
                                        <th>Proveedor</th>
                                    <?php elseif($esSalida): ?>
                                        <th>Receta</th>
                                    <?php endif; ?>
                                    <th>Tipo</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th>
                                        <span class="fw-bold text-uppercase" style="color:#7A5C58;">Acciones</span>
                                    </th>
                                    <?php if($esEntrada): ?>
                                        <th class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-btn">Seleccionar todos</button>
                                        </th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $cantidadMostrar = $movimiento->cantidad_original ?? $movimiento->cantidad;
                                        $unidadMostrar = $movimiento->unidad_medida ?? $movimiento->insumo->unidad_medida;
                                        $mostrarConversion = $movimiento->cantidad_convertida &&
                                                             $movimiento->cantidad_original &&
                                                             abs($movimiento->cantidad_original - $movimiento->cantidad_convertida) > 0.001;
                                        $recetaNombre = $movimiento->receta->nombre ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo e(++$i); ?></td>
                                        <td><?php echo e($movimiento->insumo->nombre); ?></td>
                                        <td>
                                            <?php echo e(number_format($cantidadMostrar, 2)); ?>

                                            <?php if($mostrarConversion): ?>
                                                <br>
                                                <small class="text-muted">
                                                    (<?php echo e(number_format($movimiento->cantidad_convertida, 2)); ?>

                                                    <?php echo e($movimiento->insumo->unidad_medida->abreviatura); ?>)
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo e($unidadMostrar->abreviatura); ?>

                                        </td>
                                        <?php if($esEntrada): ?>
                                            <td>
                                                <?php if($movimiento->compra): ?>
                                                    <strong>Bs. <?php echo e(number_format($movimiento->compra->costo_total, 2)); ?></strong>
                                                    <?php if($movimiento->compra->descripcion): ?>
                                                        <div><small class="text-muted">Detalle: <?php echo e($movimiento->compra->descripcion); ?></small></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $proveedorNombre = $movimiento->compra->proveedorRel->nombre ?? $movimiento->compra->proveedor ?? null;
                                                ?>
                                                <?php echo e($proveedorNombre ?? 'N/A'); ?>

                                            </td>
                                        <?php elseif($esSalida): ?>
                                            <td><?php echo e($recetaNombre ?? 'Sin receta'); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge <?php echo e($movimiento->tipo == 'entrada' ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo e($movimiento->tipo); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($movimiento->motivo); ?></td>
                                        <td><?php echo e($movimiento->created_at->format('d/m/Y')); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <form action="<?php echo e(route('movimientos.destroy',$movimiento->id)); ?>" method="POST" class="d-flex gap-2 mb-0">
                                                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                                                    <a class="btn"
                                                       href="<?php echo e(route('movimientos.edit',$movimiento->id)); ?>"
                                                       style="background-color: #6F4E37; color: white; border-radius: 5px; padding: 8px 20px;">
                                                        EDITAR
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin|cocinero|ayudante_cocina')): ?>
                                                    <button type="submit"
                                                            class="btn"
                                                            style="background-color: #6F4E37; color: white; border-radius: 5px; padding: 8px 20px;">
                                                        ELIMINAR
                                                    </button>
                                                    <?php endif; ?>
                                                </form>
                                            </div>
                                        </td>
                                        <?php if($esEntrada): ?>
                                            <td class="text-end">
                                                <div class="form-check d-inline-block m-0">
                                                    <input type="checkbox"
                                                           class="form-check-input movement-checkbox"
                                                           value="<?php echo e($movimiento->id); ?>"
                                                           data-cost="<?php echo e($movimiento->compra->costo_total ?? 0); ?>"
                                                           data-date="<?php echo e($movimiento->created_at->format('Y-m-d')); ?>">
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if($esEntrada): ?>
                        <div class="card mt-3" id="selection-panel" style="display: none;">
                            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <strong>Seleccionados:</strong> <span id="selected-count">0</span>
                                    <span class="ms-3"><strong>Total costo:</strong> Bs. <span id="selected-total">0.00</span></span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <input type="text" class="form-control" id="selection-name" placeholder="Nombre del reporte (opcional)">
                                    <button type="button" class="btn btn-primary" id="save-selection-btn">Guardar selección</button>
                                </div>
                                <div class="flex-grow-1">
                                    <small id="selection-message" class="text-success"></small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php echo $movimientos->links(); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.movement-checkbox');
    if (!checkboxes.length) {
        return;
    }

    const selectAllBtn = document.getElementById('select-all-btn');
    const panel = document.getElementById('selection-panel');
    const selectedCountEl = document.getElementById('selected-count');
    const selectedTotalEl = document.getElementById('selected-total');
    const saveBtn = document.getElementById('save-selection-btn');
    const nameInput = document.getElementById('selection-name');
    const messageEl = document.getElementById('selection-message');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const guardarUrl = "<?php echo e(route('movimientos.guardarSeleccion')); ?>";
    const fechaInicioInput = document.querySelector('input[name="fecha_inicio"]');
    const fechaFinInput = document.querySelector('input[name="fecha_fin"]');

    const selectedMap = new Map();

    function updatePanel() {
        if (!panel) return;
        if (selectedMap.size === 0) {
            panel.style.display = 'none';
            saveBtn.disabled = true;
            messageEl.textContent = '';
            return;
        }
        panel.style.display = 'block';
        const total = Array.from(selectedMap.values()).reduce((acc, val) => acc + val.cost, 0);
        selectedCountEl.textContent = selectedMap.size;
        selectedTotalEl.textContent = total.toFixed(2);
        saveBtn.disabled = false;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const id = parseInt(cb.value, 10);
            const cost = parseFloat(cb.dataset.cost || 0);
            if (cb.checked) {
                selectedMap.set(id, { cost: cost });
            } else {
                selectedMap.delete(id);
            }
            updatePanel();
        });
    });

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            const selectAll = selectedMap.size !== checkboxes.length;
            checkboxes.forEach(cb => {
                cb.checked = selectAll;
                cb.dispatchEvent(new Event('change'));
            });
        });
    }

    saveBtn?.addEventListener('click', async () => {
        if (selectedMap.size === 0) return;
        saveBtn.disabled = true;
        messageEl.className = '';
        messageEl.textContent = 'Guardando selección...';
        const payload = {
            movimientos: Array.from(selectedMap.keys()),
            nombre: nameInput.value,
            fecha_inicio: fechaInicioInput.value || null,
            fecha_fin: fechaFinInput.value || null,
        };
        try {
            const response = await fetch(guardarUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'No se pudo guardar la selección.');
            }
            messageEl.className = 'text-success';
            messageEl.textContent = 'Selección guardada correctamente. Puedes verla en la sección de reportes.';
            nameInput.value = '';
            selectedMap.clear();
            checkboxes.forEach(cb => cb.checked = false);
            updatePanel();
        } catch (error) {
            console.error(error);
            messageEl.className = 'text-danger';
            messageEl.textContent = error.message;
            saveBtn.disabled = false;
        } finally {
            setTimeout(() => {
                messageEl.textContent = '';
            }, 4000);
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/movimientos/index.blade.php ENDPATH**/ ?>