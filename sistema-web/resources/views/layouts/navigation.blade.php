<ul>
    <li class="nav-item @if (request()->routeIs('home')) active @endif">
        <a href="{{ route('home') }}">
            <span class="icon">
                <i class="lni lni-restaurant"></i>
            </span>
            <span class="text">{{ __('Dashboard') }}</span>
        </a>
    </li>

    @hasanyrole('admin|director')
    <li class="nav-item @if (request()->routeIs('predicciones.index')) active @endif">
        <a href="{{ route('predicciones.index') }}">
            <span class="icon">
                <i class="lni lni-reddit"></i>
            </span>
            <span class="text">{{ __('Predicciones') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cajero|director')
    <li class="nav-item @if (request()->routeIs('ventas.index') || request()->routeIs('ventas.create') || request()->routeIs('ventas.edit')) active @endif">
        <a href="{{ route('ventas.index') }}">
            <span class="icon">
                <i class="lni lni-dollar"></i>
            </span>
            <span class="text">{{ __('Ventas') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|director|cocinero|ayudante_cocina')
    <li class="nav-item @if (request()->routeIs('movimientos.index') ||
            request()->routeIs('movimientos.create') ||
            request()->routeIs('movimientos.edit')) active @endif">
        <a href="{{ route('movimientos.index') }}">
            <span class="icon">
                <i class="lni lni-cart"></i>
            </span>
            <span class="text">{{ __('Movimientos') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cocinero|director|ayudante_cocina')
    <li class="nav-item @if (request()->routeIs('recetas.index') ||
            request()->routeIs('recetas.create') ||
            request()->routeIs('recetas.edit') ||
            request()->routeIs('recetas.show')) active @endif">
        <a href="{{ route('recetas.index') }}">
            <span class="icon">
                <i class="lni lni-service"></i>
            </span>
            <span class="text">{{ __('Recetas') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cocinero|director|ayudante_cocina')
    <li class="nav-item @if (request()->routeIs('insumos.index') || request()->routeIs('insumos.create') || request()->routeIs('insumos.edit')) active @endif">
        <a href="{{ route('insumos.index') }}">
            <span class="icon">
                <i class="lni lni-sprout"></i>
            </span>
            <span class="text">{{ __('Insumos') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cocinero|director|ayudante_cocina')
    <li class="nav-item @if (request()->routeIs('proveedores.index') ||
            request()->routeIs('proveedores.create') ||
            request()->routeIs('proveedores.edit')) active @endif">
        <a href="{{ route('proveedores.index') }}">
            <span class="icon">
                <i class="lni lni-delivery"></i>
            </span>
            <span class="text">{{ __('Proveedores') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cocinero|director')
    <li class="nav-item @if (request()->routeIs('categorias.index') ||
            request()->routeIs('categorias.create') ||
            request()->routeIs('categorias.edit')) active @endif">
        <a href="{{ route('categorias.index') }}">
            <span class="icon">
                <i class="lni lni-tag"></i>
            </span>
            <span class="text">{{ __('Categorías') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cocinero|director')
    <li class="nav-item @if (request()->routeIs('unidades.index')) active @endif">
        <a href="{{ route('unidades.index') }}">
            <span class="icon">
                <i class="lni lni-ruler-alt"></i>
            </span>
            <span class="text">{{ __('Unidades de medida') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|cajero|director')
    <li class="nav-item @if (request()->routeIs('reportes.index')) active @endif">
        <a href="{{ route('reportes.index') }}">
            <span class="icon">
                <i class="lni lni-stats-up"></i>
            </span>
            <span class="text">{{ __('Reportes') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|director')
    <li class="nav-item @if (request()->routeIs('users.index')) active @endif">
        <a href="{{ route('users.index') }}">
            <span class="icon">
                <i class="lni lni-user"></i>
            </span>
            <span class="text">{{ __('Usuarios') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|director|cocinero|ayudante_cocina')
    <li class="nav-item @if (request()->routeIs('notificaciones.index')) active @endif">
        <a href="{{ route('notificaciones.index') }}">
            <span class="icon">
                <i class="lni lni-warning"></i>
            </span>
            <span class="text">{{ __('Alerta y Notificaciones') }}</span>
        </a>
    </li>
    @endhasanyrole

    @hasanyrole('admin|director')
    <li class="nav-item @if (request()->routeIs('historial.index')) active @endif">
        <a href="{{ route('historial.index') }}">
            <span class="icon">
                <i class="lni lni-archive"></i>
            </span>
            <span class="text">{{ __('Historial') }}</span>
        </a>
    </li>
    @endhasanyrole
</ul>
