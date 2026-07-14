@extends($esCliente ? 'layouts.app-cliente' : 'layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="menu-background-wrapper">
        <!-- ========== title-wrapper start ========== -->
        <div class="title-wrapper pt-30 text-center">
            <h2 class="page-title">{{ __('Menu Principal') }}</h2>
            @if(isset($fecha))
                <p class="text-white">Fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
            @endif
        </div>
        <!-- ========== title-wrapper end ========== -->
        <div class="container mt-4">

        @if(isset($menusDia) && $menusDia->count() > 0)
            {{-- Contenedor de dashboards tipo carrusel --}}
            <div class="dashboard-carousel-container">
                @foreach($menusDia as $index => $menu)
                    {{-- Dashboard individual --}}
                    <div class="menu-dashboard {{ $index === 0 ? 'active' : '' }}"
                         data-dashboard-index="{{ $index }}"
                         data-menu-id="{{ $menu->id }}">

                        {{-- Header del dashboard --}}
                        <div class="dashboard-header">
                            <div class="dashboard-title-section">
                                <div class="dashboard-title-wrapper" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; width: 100%;">
                                    @php
                                        $etiquetaMenu = isset($menu->tipoComida) && $menu->tipoComida
                                            ? $menu->tipoComida->nombre
                                            : 'General';
                                    @endphp
                                    <span class="badge badge-tipo">{{ $etiquetaMenu }}</span>
                                    <h2 class="dashboard-main-title" style="margin: 0; flex: 1; min-width: 200px;">
                                        {{-- SIEMPRE mostrar el título guardado si el menú tiene ID --}}
                                        @if(isset($menu->id) && $menu->id)
                                            {{-- Menú guardado: SIEMPRE mostrar el título de la BD --}}
                                            {{ strtoupper($menu->titulo ?: ($menu->tipoComida ? $menu->tipoComida->nombre : 'Menú del Día')) }}
                                        @else
                                            {{-- Menú virtual: mostrar nombre del tipo de comida --}}
                                            {{ strtoupper($menu->tipoComida ? $menu->tipoComida->nombre : ($menu->titulo ?? 'Menú del Día')) }}
                                        @endif
                                    </h2>
                                </div>
                                <div class="dashboard-meta">
                                    @if(isset($menu->hora_inicio) && isset($menu->hora_fin) && $menu->hora_inicio && $menu->hora_fin)
                                        <span class="dashboard-time">
                                            🕐 {{ \Carbon\Carbon::parse($menu->hora_inicio)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($menu->hora_fin)->format('H:i') }}
                                        </span>
                                    @endif
                                    @if(!$esCliente)
                                        <button type="button"
                                                class="btn btn-success btn-registrar-header"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalRegistrarConsumo"
                                                data-receta-id=""
                                                data-receta-nombre=""
                                                data-receta-precio=""
                                                 data-tipo-comida-id="{{ $menu->tipoComida->id ?? null }}"
                                                 data-menu-dia-id="{{ $menu->id }}"
                                                title="Selecciona primero un plato">
                                            <i class="lni lni-plus"></i> Registrar Consumo
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="dashboard-counter">
                                Menú {{ $index + 1 }} de {{ $menusDia->count() }}
                            </div>
                        </div>

                        {{-- Contenido del dashboard --}}
                        <div class="dashboard-content">
                            @php
                                // Asegurar que recetas sea una colección
                                if (is_object($menu->recetas)) {
                                    if (method_exists($menu->recetas, 'count')) {
                                        $recetasMenu = $menu->recetas;
                                    } else {
                                        $recetasMenu = collect([$menu->recetas]);
                                    }
                                } else {
                                    $recetasMenu = collect($menu->recetas ?? []);
                                }
                                $recetasMenu = $recetasMenu->filter(function ($receta) {
                                    if (!isset($receta->pivot)) {
                                        return true;
                                    }
                                    return (int) ($receta->pivot->cantidad ?? 0) > 0;
                                })->values();
                                $productosDirectos = $menu->presentacionesDirectas->filter(fn($presentacion)=>(int)($presentacion->pivot->cantidad??0)>0)->values();
                                $ordenTipo=fn($item)=>$tiposProduccion->get($item->pivot->tipo_produccion_id)?->orden??999;
                                $recetasMenu=$recetasMenu->sortBy($ordenTipo)->values();
                                $productosDirectos=$productosDirectos->sortBy($ordenTipo)->values();
                            @endphp
                            @if(($recetasMenu && $recetasMenu->count() > 0) || $productosDirectos->count() > 0)
                                <div class="platos-grid">
                                    @php $grupoActual=null; @endphp
                                    @foreach($recetasMenu as $plato)
                                        @php $grupo=$tiposProduccion->get($plato->pivot->tipo_produccion_id)?->nombre??'Otros'; @endphp
                                        @if($grupo!==$grupoActual)<h3 class="tipo-produccion-titulo">{{ $grupo }}</h3>@php $grupoActual=$grupo; @endphp @endif
                                        <div class="plato-card"
                                             @if(!$esCliente)
                                             onclick="seleccionarPlatoParaConsumo(this)"
                                             @endif>
                                            @php
                                                $imagenUrl = asset('images/recetas.jpg');
                                                if ($plato->imagen && Storage::disk('public')->exists($plato->imagen)) {
                                                    $imagenUrl = asset('storage/' . $plato->imagen);
                                                }
                                            @endphp
                                            <div class="plato-image-wrapper">
                                                <img src="{{ $imagenUrl }}"
                                                     alt="{{ $plato->nombre }}"
                                                     class="plato-img">
                                            </div>
                                            <div class="plato-details">
                                                <h4 class="plato-name">{{ $plato->nombre }}</h4>
                                                <p class="plato-description">{{ \Illuminate\Support\Str::limit($plato->descripcion ?: $plato->indicaciones, 100) }}</p>
                                                 <div class="plato-actions">
                                                     <span class="plato-price">Bs. {{ number_format($plato->pivot->precio_venta ?? $plato->precio, 2) }}</span>
                                                     @if(!$esCliente)
                                                         <span class="badge {{ (int) $plato->pivot->cantidad <= 3 ? 'bg-danger' : 'bg-success' }}">
                                                             Quedan {{ (int) $plato->pivot->cantidad }} {{ (int) $plato->pivot->cantidad === 1 ? 'porción' : 'porciones' }}
                                                         </span>
                                                     @endif
                                                 </div>
                                            </div>
                                            @if(!$esCliente)
                                                <input type="hidden" class="plato-data-receta-id" value="{{ $plato->id }}">
                                                <input type="hidden" class="plato-data-receta-nombre" value="{{ $plato->nombre }}">
                                                <input type="hidden" class="plato-data-receta-precio" value="{{ $plato->pivot->precio_venta ?? $plato->precio }}">
                                                <input type="hidden" class="plato-data-tipo-comida-id" value="{{ $menu->tipoComida->id ?? null }}">
                                                <input type="hidden" class="plato-data-disponible" value="{{ (int) ($plato->pivot->cantidad ?? 0) }}">
                                            @endif
                                        </div>
                                    @endforeach
                                    @php $grupoActual=null; @endphp
                                    @foreach($productosDirectos as $presentacion)
                                        @php $grupo=$tiposProduccion->get($presentacion->pivot->tipo_produccion_id)?->nombre??'Otros'; @endphp
                                        @if($grupo!==$grupoActual)<h3 class="tipo-produccion-titulo">{{ $grupo }}</h3>@php $grupoActual=$grupo; @endphp @endif
                                        <div class="plato-card" @if(!$esCliente) onclick="seleccionarPlatoParaConsumo(this)" @endif>
                                            @php($imagenUrl=$presentacion->imagen&&Storage::disk('public')->exists($presentacion->imagen)?asset('storage/'.$presentacion->imagen):asset('images/cereales.jpg'))
                                            <div class="plato-image-wrapper"><img src="{{ $imagenUrl }}" alt="{{ $presentacion->nombre_completo }}" class="plato-img"></div>
                                            <div class="plato-details"><h4 class="plato-name">{{ $presentacion->nombre_completo }}</h4><p class="plato-description">{{ $presentacion->tipo_envase }}</p><div class="plato-actions"><span class="plato-price">Bs. {{ number_format($presentacion->pivot->precio_venta,2) }}</span>@if(!$esCliente)<span class="badge {{ (int)$presentacion->pivot->cantidad<=3?'bg-danger':'bg-success' }}">Quedan {{ (int)$presentacion->pivot->cantidad }} unidades</span>@endif</div></div>
                                            @if(!$esCliente)<input type="hidden" class="plato-data-receta-id" value="presentacion:{{ $presentacion->id }}"><input type="hidden" class="plato-data-receta-nombre" value="{{ $presentacion->nombre_completo }}"><input type="hidden" class="plato-data-receta-precio" value="{{ $presentacion->pivot->precio_venta }}"><input type="hidden" class="plato-data-tipo-comida-id" value="{{ $menu->tipoComida->id??null }}"><input type="hidden" class="plato-data-disponible" value="{{ (int)$presentacion->pivot->cantidad }}">@endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="dashboard-empty">
                                    <i class="lni lni-empty-file" style="font-size: 64px; color: #ccc;"></i>
                                    <p>No hay platos disponibles en este menú.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Controles de navegación --}}
                        <div class="dashboard-navigation">
                            @if($index > 0)
                                <button type="button"
                                        class="btn btn-nav btn-prev"
                                        onclick="showDashboard({{ $index - 1 }})">
                                    <i class="lni lni-arrow-left"></i> Menú Anterior
                                </button>
                            @else
                                <div></div>
                            @endif

                            <div class="dashboard-indicators">
                                @foreach($menusDia as $ind => $m)
                                    <button type="button"
                                            class="dashboard-indicator {{ $ind === $index ? 'active' : '' }}"
                                            onclick="showDashboard({{ $ind }})"
                                            title="{{ $m->titulo }}"></button>
                                @endforeach
                            </div>

                            @if($index < $menusDia->count() - 1)
                                <button type="button"
                                        class="btn btn-nav btn-next"
                                        onclick="showDashboard({{ $index + 1 }})">
                                    Siguiente Menú <i class="lni lni-arrow-right"></i>
                                </button>
                            @else
                                <div></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="lni lni-calendar" style="font-size: 48px;"></i>
                <h5 class="mt-3 mb-0">No hay menú disponible en este momento.</h5>
            </div>
        @endif

      </div>
    </div>

    <style>
        .menu-background-wrapper {
            position: relative;
            min-height: calc(100vh - 100px);
            background: url('{{ asset('images/tablero menu.jpg') }}') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            padding: 20px 0;
        }

        .menu-background-wrapper > * {
            position: relative;
            z-index: 1;
        }

        .page-title {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Contenedor de dashboards tipo carrusel */
        .dashboard-carousel-container {
            position: relative;
            width: 100%;
            min-height: 600px;
        }

        /* Dashboard individual */
        .menu-dashboard {
            display: none;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in;
        }

        .menu-dashboard.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header del dashboard */
        .dashboard-header {
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #654321;
        }

        .dashboard-title-section {
            flex: 1;
        }

        .dashboard-main-title {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .dashboard-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .badge-tipo {
            background: rgba(255, 255, 255, 0.25);
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .dashboard-time {
            font-size: 1rem;
            opacity: 0.95;
        }

        .dashboard-counter {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Contenido del dashboard */
        .dashboard-content {
            padding: 30px;
            background: rgba(253, 251, 248, 0.98);
            min-height: 500px;
        }

        /* Grid de platos */
        .platos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .tipo-produccion-titulo{grid-column:1/-1;font-size:1.25rem;margin:18px 0 2px;padding-bottom:8px;border-bottom:1px solid #ddd;letter-spacing:0}

        /* Tarjeta de plato */
        .plato-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .plato-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .plato-image-wrapper {
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #f0f0f0;
        }

        .plato-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .plato-card:hover .plato-img {
            transform: scale(1.1);
        }

        .plato-details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .plato-name {
            color: #4a1c1c;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .plato-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .plato-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }

        .plato-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #8B4513;
        }

        .btn-registrar {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-registrar-header {
            padding: 8px 14px;
            font-weight: 600;
            border-radius: 8px;
            white-space: nowrap;
            margin-left: 8px;
        }

        .plato-card.plato-selected,
        .card.plato-selected {
            outline: 3px solid rgba(40, 167, 69, 0.35);
            border-color: #28a745 !important;
        }

        .dashboard-empty {
            text-align: center;
            padding: 80px 20px;
            color: #999;
        }

        .dashboard-empty p {
            font-size: 1.2rem;
            margin-top: 20px;
        }

        /* Navegación entre dashboards */
        .dashboard-navigation {
            background: #f8f9fa;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 3px solid #dee2e6;
        }

        .btn-nav {
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 8px;
            background: #8B4513;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            background: #A0522D;
            transform: translateX(5px);
            color: white;
        }

        .btn-prev:hover {
            transform: translateX(-5px);
        }

        .dashboard-indicators {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .dashboard-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #8B4513;
            background: transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }

        .dashboard-indicator.active {
            background: #8B4513;
            transform: scale(1.3);
        }

        .dashboard-indicator:hover {
            background: #A0522D;
            border-color: #A0522D;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .platos-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .dashboard-main-title {
                font-size: 1.8rem;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .platos-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-navigation {
                flex-wrap: wrap;
                gap: 15px;
            }

            .btn-nav {
                flex: 1;
                min-width: 120px;
            }
        }
    </style>

    {{-- Modal para registrar consumo (solo para personal) --}}
    @if(!$esCliente)
    @include('consumos.modal-registro-rapido')
    @endif

    <script>
        @if(!$esCliente)
        function seleccionarPlatoParaConsumo(cardEl) {
            if (!cardEl) return;
            const dashboard = cardEl.closest('.menu-dashboard');
            const scope = dashboard || document;

            scope.querySelectorAll('.plato-card, .card').forEach(c => c.classList.remove('plato-selected'));
            cardEl.classList.add('plato-selected');

            const recetaId = cardEl.querySelector('.plato-data-receta-id')?.value || '';
            const recetaNombre = cardEl.querySelector('.plato-data-receta-nombre')?.value || '';
            const recetaPrecio = cardEl.querySelector('.plato-data-receta-precio')?.value || '';
            const tipoComidaId = cardEl.querySelector('.plato-data-tipo-comida-id')?.value || '';
            const disponible = cardEl.querySelector('.plato-data-disponible')?.value || '0';
            const menuDiaId = dashboard?.dataset.menuId || '';

            const btn = dashboard
                ? dashboard.querySelector('.btn-registrar-header')
                : document.querySelector('.btn-registrar-header');

            if (btn) {
                btn.setAttribute('data-receta-id', recetaId);
                btn.setAttribute('data-receta-nombre', recetaNombre);
                btn.setAttribute('data-receta-precio', recetaPrecio);
                btn.setAttribute('data-tipo-comida-id', tipoComidaId);
                btn.setAttribute('data-menu-dia-id', menuDiaId);
                btn.setAttribute('data-disponible', disponible);
                btn.title = recetaNombre ? ('Registrar consumo de ' + recetaNombre) : 'Selecciona primero un plato';
            }
        }
        @endif

        function showDashboard(index) {
            // Ocultar todos los dashboards
            document.querySelectorAll('.menu-dashboard').forEach(dashboard => {
                dashboard.classList.remove('active');
            });

            // Mostrar el dashboard seleccionado
            const dashboard = document.querySelector(`[data-dashboard-index="${index}"]`);
            if (dashboard) {
                dashboard.classList.add('active');

                // Actualizar indicadores
                document.querySelectorAll('.dashboard-indicator').forEach((indicator, i) => {
                    indicator.classList.toggle('active', i === index);
                });

                // Scroll suave al top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Navegación con teclado
        document.addEventListener('keydown', function(e) {
            const activeDashboard = document.querySelector('.menu-dashboard.active');
            if (!activeDashboard) return;

            const currentIndex = parseInt(activeDashboard.dataset.dashboardIndex);
            const totalDashboards = document.querySelectorAll('.menu-dashboard').length;

            if (e.key === 'ArrowLeft' && currentIndex > 0) {
                showDashboard(currentIndex - 1);
            } else if (e.key === 'ArrowRight' && currentIndex < totalDashboards - 1) {
                showDashboard(currentIndex + 1);
            }
        });

        // Funcionalidad para editar título del menú (solo admin y cocinero)
        @if(!$esCliente)
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar selección por defecto en cada dashboard
            if (!esVistaCliente) {
                document.querySelectorAll('.menu-dashboard').forEach(function(dashboard) {
                    const firstCard = dashboard.querySelector('.plato-card');
                    if (firstCard) seleccionarPlatoParaConsumo(firstCard);
                });
            }

            const fallbackFirst = document.querySelector('.row .card.h-100.shadow-sm');
            if (fallbackFirst) {
                seleccionarPlatoParaConsumo(fallbackFirst);
            }

            document.querySelectorAll('.btn-registrar-header').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    if (!this.getAttribute('data-receta-id')) {
                        e.preventDefault();
                        e.stopPropagation();
                        alert('Primero selecciona un plato.');
                    }
                });
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Botones de editar título
            document.querySelectorAll('.btn-edit-title').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menuId;
                    // Buscar el dashboard padre de este botón
                    const dashboard = this.closest('.menu-dashboard');
                    if (!dashboard) return;

                    const titleHeading = dashboard.querySelector(`.dashboard-main-title`);
                    const titleInput = dashboard.querySelector(`.editable-title-input[data-menu-id="${menuId}"]`);
                    const editBtn = dashboard.querySelector(`.btn-edit-title[data-menu-id="${menuId}"]`);
                    const saveBtn = dashboard.querySelector(`.btn-save-title[data-menu-id="${menuId}"]`);
                    const cancelBtn = dashboard.querySelector(`.btn-cancel-title[data-menu-id="${menuId}"]`);

                    if (titleInput && titleHeading) {
                        // Ocultar el título y mostrar el input
                        titleHeading.style.display = 'none';
                        titleInput.style.display = 'block';
                        titleInput.focus();
                        titleInput.select();

                        editBtn.style.display = 'none';
                        saveBtn.style.display = 'inline-flex';
                        cancelBtn.style.display = 'inline-flex';
                    }
                });
            });

            // Botones de guardar título
            document.querySelectorAll('.btn-save-title').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menuId;
                    // Buscar el dashboard padre de este botón
                    const dashboard = this.closest('.menu-dashboard');
                    if (!dashboard) return;

                    const titleHeading = dashboard.querySelector(`.dashboard-main-title`);
                    const titleInput = dashboard.querySelector(`.editable-title-input[data-menu-id="${menuId}"]`);
                    const editBtn = dashboard.querySelector(`.btn-edit-title[data-menu-id="${menuId}"]`);
                    const saveBtn = dashboard.querySelector(`.btn-save-title[data-menu-id="${menuId}"]`);
                    const cancelBtn = dashboard.querySelector(`.btn-cancel-title[data-menu-id="${menuId}"]`);

                    if (titleInput) {
                        const nuevoTitulo = titleInput.value.trim();
                        // Si está vacío, usar el título original
                        const tituloFinal = nuevoTitulo || titleInput.dataset.originalTitle;

                        // Obtener datos adicionales del botón de editar
                        const editBtnData = dashboard.querySelector(`.btn-edit-title[data-menu-id="${menuId}"]`);
                        const tipoComidaId = editBtnData ? editBtnData.dataset.tipoComidaId : null;
                        const fecha = editBtnData ? editBtnData.dataset.fecha : '{{ $fecha }}';

                        // Determinar la URL y el body según si es menú nuevo o existente
                        let url, body;
                        if (menuId === 'new') {
                            url = '/menus-dia/update-titulo';
                            body = JSON.stringify({
                                titulo: tituloFinal,
                                tipo_comida_id: tipoComidaId,
                                fecha: fecha
                            });
                        } else {
                            url = `/menus-dia/${menuId}/update-titulo`;
                            body = JSON.stringify({
                                titulo: tituloFinal,
                                fecha: fecha
                            });
                        }

                        // Enviar AJAX
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: body
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar el título visible
                                if (titleHeading) {
                                    titleHeading.textContent = data.titulo.toUpperCase();
                                    titleHeading.style.display = 'block';
                                }
                                titleInput.style.display = 'none';
                                titleInput.value = data.titulo;

                                editBtn.style.display = 'inline-flex';
                                saveBtn.style.display = 'none';
                                cancelBtn.style.display = 'none';

                                // Mostrar mensaje de éxito
                                const successMsg = document.createElement('div');
                                successMsg.textContent = '✅ Título actualizado exitosamente';
                                successMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 24px; border-radius: 4px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                                document.body.appendChild(successMsg);
                                setTimeout(() => successMsg.remove(), 3000);

                                // Si era un menú nuevo, actualizar el data-menu-id para futuras ediciones
                                if (menuId === 'new' && data.menu_id) {
                                    editBtn.dataset.menuId = data.menu_id;
                                    saveBtn.dataset.menuId = data.menu_id;
                                    cancelBtn.dataset.menuId = data.menu_id;
                                    titleInput.dataset.menuId = data.menu_id;

                                    // Actualizar también el data-menu-id del contenedor del dashboard
                                    const dashboardContainer = dashboard.closest('.menu-dashboard');
                                    if (dashboardContainer) {
                                        dashboardContainer.setAttribute('data-menu-id', data.menu_id);
                                    }
                                }

                                // Actualizar el texto del botón a "Editar Título" si hay título personalizado
                                if (editBtn) {
                                    const tituloOriginal = titleInput.dataset.originalTitle;
                                    const tituloNuevo = data.titulo;
                                    // Si el título nuevo es diferente al original (tipo de comida), es personalizado
                                    if (tituloNuevo !== tituloOriginal) {
                                        editBtn.querySelector('span').textContent = 'Editar Título';
                                        editBtn.title = 'Editar Título';
                                        editBtn.dataset.tieneTituloPersonalizado = '1';

                                        // Mostrar botón de eliminar si existe
                                        const eliminarBtn = dashboard.querySelector(`.btn-eliminar-title[data-menu-id="${data.menu_id || menuId}"]`);
                                        if (eliminarBtn) {
                                            eliminarBtn.style.display = 'inline-flex';
                                        }
                                    } else {
                                        editBtn.querySelector('span').textContent = 'Añadir Título';
                                        editBtn.title = 'Añadir Título';
                                        editBtn.dataset.tieneTituloPersonalizado = '0';
                                    }

                                    // Actualizar el data-original-title para futuras ediciones
                                    titleInput.dataset.originalTitle = data.titulo;
                                }

                                // NO recargar - el título ya se actualizó en pantalla
                                // El título se mantiene visible sin recargar
                            } else {
                                alert('Error al actualizar el título: ' + (data.message || ''));
                                // Restaurar
                                if (titleHeading) {
                                    titleHeading.style.display = 'block';
                                }
                                titleInput.style.display = 'none';
                                titleInput.value = titleInput.dataset.originalTitle;
                                editBtn.style.display = 'inline-flex';
                                saveBtn.style.display = 'none';
                                cancelBtn.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error de conexión al actualizar el título.');
                            // Restaurar
                            if (titleHeading) {
                                titleHeading.style.display = 'block';
                            }
                            titleInput.style.display = 'none';
                            titleInput.value = titleInput.dataset.originalTitle;
                            editBtn.style.display = 'inline-flex';
                            saveBtn.style.display = 'none';
                            cancelBtn.style.display = 'none';
                        });
                    }
                });
            });

            // Botones de cancelar edición
            document.querySelectorAll('.btn-cancel-title').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menuId;
                    // Buscar el dashboard padre de este botón
                    const dashboard = this.closest('.menu-dashboard');
                    if (!dashboard) return;

                    const titleHeading = dashboard.querySelector(`.dashboard-main-title`);
                    const titleInput = dashboard.querySelector(`.editable-title-input[data-menu-id="${menuId}"]`);
                    const editBtn = dashboard.querySelector(`.btn-edit-title[data-menu-id="${menuId}"]`);
                    const saveBtn = dashboard.querySelector(`.btn-save-title[data-menu-id="${menuId}"]`);
                    const cancelBtn = dashboard.querySelector(`.btn-cancel-title[data-menu-id="${menuId}"]`);

                    if (titleInput) {
                        // Restaurar
                        if (titleHeading) {
                            titleHeading.style.display = 'block';
                        }
                        titleInput.style.display = 'none';
                        titleInput.value = titleInput.dataset.originalTitle;
                        editBtn.style.display = 'inline-flex';
                        saveBtn.style.display = 'none';
                        cancelBtn.style.display = 'none';
                    }
                });
            });

            // Permitir guardar con Enter y cancelar con Escape
            document.querySelectorAll('.editable-title-input').forEach(titleInput => {
                titleInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const menuId = this.dataset.menuId;
                        const saveBtn = document.querySelector(`.btn-save-title[data-menu-id="${menuId}"]`);
                        if (saveBtn) {
                            saveBtn.click();
                        }
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        const menuId = this.dataset.menuId;
                        const cancelBtn = document.querySelector(`.btn-cancel-title[data-menu-id="${menuId}"]`);
                        if (cancelBtn) {
                            cancelBtn.click();
                        }
                    }
                });
            });

            // Botones de eliminar título
            document.querySelectorAll('.btn-eliminar-title').forEach(btn => {
                btn.addEventListener('click', function() {
                    const menuId = this.dataset.menuId;
                    const dashboard = this.closest('.menu-dashboard');
                    if (!dashboard || !menuId || menuId === 'new') return;

                    if (confirm('¿Estás seguro de que deseas eliminar el título personalizado? Se restaurará el nombre del tipo de comida.')) {
                        fetch(`/menus-dia/${menuId}/eliminar-titulo`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar el título visible
                                const titleHeading = dashboard.querySelector(`.dashboard-main-title`);
                                if (titleHeading) {
                                    titleHeading.textContent = data.titulo.toUpperCase();
                                }

                                // Actualizar el texto del botón
                                const editBtn = dashboard.querySelector(`.btn-edit-title[data-menu-id="${menuId}"]`);
                                if (editBtn) {
                                    editBtn.querySelector('span').textContent = 'Añadir Título';
                                    editBtn.title = 'Añadir Título';
                                    editBtn.dataset.tieneTituloPersonalizado = '0';
                                }

                                // Ocultar botón de eliminar
                                this.style.display = 'none';

                                // Mostrar mensaje de éxito
                                const successMsg = document.createElement('div');
                                successMsg.textContent = '✅ Título eliminado exitosamente';
                                successMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 24px; border-radius: 4px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                                document.body.appendChild(successMsg);
                                setTimeout(() => successMsg.remove(), 3000);

                                // NO recargar - el título ya se actualizó en pantalla
                                // El título se mantiene visible sin recargar
                            } else {
                                alert('Error al eliminar el título: ' + (data.message || ''));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error de conexión al eliminar el título.');
                        });
                    }
                });
            });
        });
        @endif
    </script>

    @if(!$esCliente)
    <script src="https://cdn.jsdelivr.net/npm/inferencejs"></script>
    @vite(['resources/js/game.js'])
    @endif
@endsection
