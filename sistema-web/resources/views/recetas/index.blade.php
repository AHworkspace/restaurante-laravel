@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Recetas') }}</h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="tables-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card-style mb-30">
                    <h6 class="mb-10">Tabla de recetas</h6>
                    <p>Estas recetas son platillos que prepara el sistema, cada receta está calculada para 1 porción.</p>
                    @can('recetas.crear')
                    <a href="{{ route('recetas.create') }}" class="main-btn dark-btn btn-hover mt-3">
                        <i class="lni lni-circle-plus"></i>
                        Crear nuevo
                    </a>
                    @endcan

                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-8">
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Buscar receta por nombre...">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </form>

                    <div class="table-wrapper table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="lead-info">
                                        <h6>Nombre</h6>
                                    </th>
                                    <th class="lead-precio">
                                        <h6>Precio</h6>
                                    </th>
                                    <th class="lead-email">
                                        <h6>Tiempo de preparación</h6>
                                    </th>
                                    <th class="lead-indicaciones">
                                        <h6>Indicaciones</h6>
                                    </th>
                                    @canany(['recetas.editar','recetas.eliminar'])
                                    <th>
                                        <h6>Acciones</h6>
                                    </th>
                                    @endcanany
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                @forelse ($recetas as $receta)
                                    <tr>
                                        <td class="min-width">
                                            <div class="lead">
                                                @php
                                                    $imagenUrl = asset('images/recetas.jpg');
                                                    if ($receta->imagen && Storage::disk('public')->exists($receta->imagen)) {
                                                        $imagenUrl = asset('storage/' . $receta->imagen);
                                                    }
                                                @endphp
                                                <div class="lead-image">
                                                    <img src="{{ $imagenUrl }}" alt="">
                                                </div>
                                                <div class="lead-text">
                                                    <p>{{$receta->nombre}}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            <p>${{ number_format($receta->precio, 2) }}</p>
                                        </td>
                                        <td class="min-width">
                                            <p>{{ $receta->tiempo_preparacion }} minutos</p>
                                        </td>
                                        <td class="min-width">
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#indicacionesModal{{ $receta->id }}">
                                                Ver Indicaciones
                                            </button>
                                            <!-- Modal -->
                                            <div class="modal fade" id="indicacionesModal{{ $receta->id }}" tabindex="-1" aria-labelledby="indicacionesModalLabel{{ $receta->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="indicacionesModalLabel{{ $receta->id }}">{{ $receta->nombre }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row g-4">
                                                                <div class="col-md-6">
                                                                    <h6 class="mb-3">Insumos por porcion</h6>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Insumo</th>
                                                                                    <th>Presentacion</th>
                                                                                    <th>Cantidad</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @forelse($receta->insumos as $insumo)
                                                                                    @php($presentacion = $insumo->presentaciones->firstWhere('id', $insumo->pivot->presentacion_id))
                                                                                    <tr>
                                                                                        <td>{{ $insumo->nombre }}</td>
                                                                                        <td>{{ $presentacion?->nombre ?: '-' }}</td>
                                                                                        <td>{{ $insumo->pivot->cantidad }} {{ $presentacion?->unidadStock()?->abreviatura ?: $insumo->unidad_medida?->abreviatura }}</td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr><td colspan="3" class="text-muted">Sin insumos registrados.</td></tr>
                                                                                @endforelse
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6 class="mb-3">Indicaciones</h6>
                                                                    <div style="white-space: pre-line;">
                                                                        {!! nl2br(e($receta->indicaciones)) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @canany(['recetas.editar','recetas.eliminar'])
                                        <td>
                                            <div class="action d-flex gap-2">
                                                <a href="{{ route('recetas.edit', $receta->id) }}"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;">
                                                    EDITAR
                                                </a>
                                                <form action="{{ route('recetas.destroy', $receta->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta receta?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No existen recetas disponibles</td>
                                    </tr>
                                @endforelse
                                <!-- end table row -->
                            </tbody>
                        </table>
                        <!-- end table -->
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
@endsection
