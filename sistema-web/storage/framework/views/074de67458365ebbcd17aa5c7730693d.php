<ul>
    <li class="nav-item <?php if(request()->routeIs('home')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('home')); ?>">
            <span class="icon">
                <i class="lni lni-restaurant"></i>
            </span>
            <span class="text"><?php echo e(__('Dashboard')); ?></span>
        </a>
    </li>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('predicciones.index')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('predicciones.index')); ?>">
            <span class="icon">
                <i class="lni lni-reddit"></i>
            </span>
            <span class="text"><?php echo e(__('Predicciones')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cajero|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('ventas.index') || request()->routeIs('ventas.create') || request()->routeIs('ventas.edit')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('ventas.index')); ?>">
            <span class="icon">
                <i class="lni lni-dollar"></i>
            </span>
            <span class="text"><?php echo e(__('Ventas')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|director|cocinero|ayudante_cocina')): ?>
    <li class="nav-item <?php if(request()->routeIs('movimientos.index') ||
            request()->routeIs('movimientos.create') ||
            request()->routeIs('movimientos.edit')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('movimientos.index')); ?>">
            <span class="icon">
                <i class="lni lni-cart"></i>
            </span>
            <span class="text"><?php echo e(__('Movimientos')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cocinero|director|ayudante_cocina')): ?>
    <li class="nav-item <?php if(request()->routeIs('recetas.index') ||
            request()->routeIs('recetas.create') ||
            request()->routeIs('recetas.edit') ||
            request()->routeIs('recetas.show')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('recetas.index')); ?>">
            <span class="icon">
                <i class="lni lni-service"></i>
            </span>
            <span class="text"><?php echo e(__('Recetas')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cocinero|director|ayudante_cocina')): ?>
    <li class="nav-item <?php if(request()->routeIs('insumos.index') || request()->routeIs('insumos.create') || request()->routeIs('insumos.edit')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('insumos.index')); ?>">
            <span class="icon">
                <i class="lni lni-sprout"></i>
            </span>
            <span class="text"><?php echo e(__('Insumos')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cocinero|director|ayudante_cocina')): ?>
    <li class="nav-item <?php if(request()->routeIs('proveedores.index') ||
            request()->routeIs('proveedores.create') ||
            request()->routeIs('proveedores.edit')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('proveedores.index')); ?>">
            <span class="icon">
                <i class="lni lni-delivery"></i>
            </span>
            <span class="text"><?php echo e(__('Proveedores')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cocinero|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('categorias.index') ||
            request()->routeIs('categorias.create') ||
            request()->routeIs('categorias.edit')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('categorias.index')); ?>">
            <span class="icon">
                <i class="lni lni-tag"></i>
            </span>
            <span class="text"><?php echo e(__('Categorías')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cocinero|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('unidades.index')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('unidades.index')); ?>">
            <span class="icon">
                <i class="lni lni-ruler-alt"></i>
            </span>
            <span class="text"><?php echo e(__('Unidades de medida')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|cajero|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('reportes.index')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('reportes.index')); ?>">
            <span class="icon">
                <i class="lni lni-stats-up"></i>
            </span>
            <span class="text"><?php echo e(__('Reportes')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('users.index')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('users.index')); ?>">
            <span class="icon">
                <i class="lni lni-user"></i>
            </span>
            <span class="text"><?php echo e(__('Usuarios')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|director|cocinero|ayudante_cocina')): ?>
    <li class="nav-item <?php if(request()->routeIs('notificaciones.index')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('notificaciones.index')); ?>">
            <span class="icon">
                <i class="lni lni-warning"></i>
            </span>
            <span class="text"><?php echo e(__('Alerta y Notificaciones')); ?></span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'admin|director')): ?>
    <li class="nav-item <?php if(request()->routeIs('historial.index')): ?> active <?php endif; ?>">
        <a href="<?php echo e(route('historial.index')); ?>">
            <span class="icon">
                <i class="lni lni-archive"></i>
            </span>
            <span class="text"><?php echo e(__('Historial')); ?></span>
        </a>
    </li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>