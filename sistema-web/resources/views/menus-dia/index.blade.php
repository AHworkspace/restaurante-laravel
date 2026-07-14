@extends('layouts.app')

@section('content')
@php
    $hayFiltroReporte = collect(request()->except('page'))
        ->filter(fn ($valor) => $valor !== null && $valor !== '' && $valor !== 'todos')
        ->isNotEmpty();
@endphp

<div class="title-wrapper pt-30">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="title mb-30"><h2>Menus del Dia</h2></div>
        </div>
        <div class="col-md-6 text-end">
            <a class="main-btn primary-btn btn-hover" href="{{ route('menus-dia.create') }}">
                <i class="lni lni-plus"></i> Nuevo Menu
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card-styles">
    <div class="card-style-3 mb-30">
        <div class="card-content">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Menus</h6>
                            <h4>{{ $totalMenus }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Platos configurados</h6>
                            <h4>{{ $totalPlatos }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Visibles para clientes</h6>
                            <h4>{{ $menusVisibles }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" class="card bg-light p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small">Buscar</label>
                        <input class="form-control" name="buscar" value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small">Fecha</label>
                        <input type="date" class="form-control" name="fecha" value="{{ request('fecha') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small">Tipo</label>
                        <select class="form-select" name="tipo_comida_id">
                            <option value="">Todos</option>
                            @foreach($tiposComida as $tipo)
                                <option value="{{ $tipo->id }}" @selected(request('tipo_comida_id') == $tipo->id)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="todos" @selected(request('estado', 'todos') === 'todos')>Todos</option>
                            <option value="publicados" @selected(request('estado') === 'publicados')>Publicados ahora</option>
                            <option value="ocultos" @selected(request('estado') === 'ocultos')>Ocultos</option>
                            <option value="fuera_horario" @selected(request('estado') === 'fuera_horario')>Ocultos por horario</option>
                            <option value="programados" @selected(request('estado') === 'programados')>Programados</option>
                            <option value="finalizados" @selected(request('estado') === 'finalizados')>Finalizados</option>
                            <option value="inactivos" @selected(request('estado') === 'inactivos')>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary w-100"><i class="lni lni-search-alt"></i></button>
                    </div>
                </div>
            </form>

            <div class="table-wrapper table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            @if($hayFiltroReporte)
                                <th><button type="button" class="btn btn-sm btn-outline-primary reporte-select-all-top" data-target-sector="menus-dia">Seleccionar todos</button></th>
                            @endif
                            <th>Fecha</th>
                            <th>Menu</th>
                            <th>Tipo</th>
                            <th>Horario</th>
                            <th>Items</th>
                            <th>Porciones</th>
                            <th>Total estimado</th>
                            <th>Estado actual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menusDia as $menu)
                            @php
                                [$estadoPublicacion, $clasePublicacion] = $menu->estadoPublicacion();
                                $totalRecetas = $menu->recetas->sum(fn ($receta) => (float) ($receta->pivot->precio_venta ?? $receta->precio ?? 0) * (int) ($receta->pivot->cantidad_inicial ?? $receta->pivot->cantidad ?? 0));
                                $totalDirectos = $menu->presentacionesDirectas->sum(fn ($presentacion) => (float) ($presentacion->pivot->precio_venta ?? 0) * (int) ($presentacion->pivot->cantidad_inicial ?? $presentacion->pivot->cantidad ?? 0));
                                $totalEstimado = $totalRecetas + $totalDirectos;
                                $totalItems = $menu->recetas->count() + $menu->presentacionesDirectas->count();
                                $totalPorciones = $menu->recetas->sum(fn ($r) => (int) ($r->pivot->cantidad ?? 0)) + $menu->presentacionesDirectas->sum(fn ($p) => (int) ($p->pivot->cantidad ?? 0));
                            @endphp
                            <tr>
                                @if($hayFiltroReporte)
                                    <td><input type="checkbox" class="form-check-input reporte-row-checkbox" data-sector="menus-dia" value="{{ $menu->id }}" data-total="{{ $totalEstimado }}"></td>
                                @endif
                                <td>{{ $menu->fecha->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $menu->titulo }}</strong><br>
                                    <small>{{ $menu->descripcion }}</small>
                                </td>
                                <td>{{ $menu->tipoComida?->nombre ?: 'General' }}</td>
                                <td>{{ substr((string) $menu->hora_inicio, 0, 5) ?: '-' }} - {{ substr((string) $menu->hora_fin, 0, 5) ?: '-' }}</td>
                                <td>{{ $totalItems }}</td>
                                <td>{{ $totalPorciones }}</td>
                                <td>Bs. {{ number_format($totalEstimado, 2) }}</td>
                                <td><span class="badge {{ $clasePublicacion }}">{{ $estadoPublicacion }}</span></td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <a class="btn btn-info btn-sm" href="{{ route('menus-dia.show', $menu) }}" title="Ver detalles e historial"><i class="lni lni-eye"></i></a>
                                        <a class="btn btn-warning btn-sm" href="{{ route('menus-dia.edit', $menu) }}" title="Editar menu"><i class="lni lni-pencil"></i></a>
                                        <form method="POST" action="{{ route('menus-dia.toggle-visible', $menu) }}">
                                            @csrf
                                            <button class="btn btn-sm {{ $menu->visible_para_clientes ? 'btn-outline-secondary' : 'btn-success' }}" title="{{ $menu->visible_para_clientes ? 'Ocultar manualmente' : 'Permitir publicacion segun fecha y horario' }}">
                                                <i class="lni lni-reload me-1"></i>{{ $menu->visible_para_clientes ? 'Ocultar' : 'Publicar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $hayFiltroReporte ? 10 : 9 }}" class="text-center">No se encontraron menus.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($hayFiltroReporte)
                @include('reportes._guardar-filtrado', ['sector' => 'menus-dia', 'titulo' => 'Guardar reporte de menus filtrados'])
            @endif
        </div>
    </div>
</div>
@endsection
