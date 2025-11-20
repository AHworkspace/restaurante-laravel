<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3, h4 { color: #5A2828; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 12px; }
        th { background: #f5f5f5; }
        .summary { margin-top: 15px; }
        .summary p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>Reporte de Movimientos</h1>
    <p><strong>Nombre:</strong> <?php echo e($reporte->nombre ?? 'Sin nombre'); ?></p>
    <p><strong>Período:</strong> <?php echo e(optional($reporte->fecha_desde)->format('d/m/Y')); ?> - <?php echo e(optional($reporte->fecha_hasta)->format('d/m/Y')); ?></p>
    <div class="summary">
        <p><strong>Total de movimientos:</strong> <?php echo e($reporte->total_movimientos); ?></p>
        <p><strong>Total invertido:</strong> Bs. <?php echo e(number_format($reporte->total_costo, 2)); ?></p>
        <p><strong>Fecha de generación:</strong> <?php echo e($reporte->created_at->format('d/m/Y H:i')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Insumo</th>
                <th>Cantidad</th>
                <th>Tipo</th>
                <th>Motivo</th>
                <th>Proveedor</th>
                <th>Costo (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $datos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($item['fecha'])->format('d/m/Y')); ?></td>
                    <td><?php echo e($item['insumo']); ?></td>
                    <td><?php echo e(number_format($item['cantidad'], 2)); ?> <?php echo e($item['unidad']); ?></td>
                    <td><?php echo e(ucfirst($item['tipo'])); ?></td>
                    <td><?php echo e($item['motivo']); ?></td>
                    <td><?php echo e($item['proveedor'] ?: '-'); ?></td>
                    <td><?php echo e(number_format($item['costo'], 2)); ?></td>
                </tr>
                <?php if(!empty($item['detalle'])): ?>
                    <tr>
                        <td colspan="8"><strong>Detalle:</strong> <?php echo e($item['detalle']); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>

<?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/reportes/pdf_movimientos.blade.php ENDPATH**/ ?>