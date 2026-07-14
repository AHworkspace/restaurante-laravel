@extends('layouts.app')

@section('content')
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-7">
                <div class="title mb-30">
                    <h2>Marcas y empresas</h2>
                </div>
            </div>
            @can('marcas.crear')
                <div class="col-md-5 text-end">
                    <button class="main-btn primary-btn" data-bs-toggle="modal" data-bs-target="#crearMarca">
                        <i class="lni lni-plus"></i> Nueva marca
                    </button>
                </div>
            @endcan
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card-style-3 mb-30">
        <div class="card-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Empresa fabricante</th>
                            <th>Proveedores</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($marcas as $marca)
                            <tr>
                                <td>
                                    <strong>{{ $marca->nombre }}</strong><br>
                                    <small class="text-muted">{{ $marca->descripcion }}</small>
                                </td>
                                <td>{{ $marca->empresa_fabricante ?: 'No especificada' }}</td>
                                <td>{{ $marca->proveedores->pluck('nombre')->implode(', ') ?: 'Sin asociación' }}</td>
                                <td>
                                    <span class="badge {{ $marca->activo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $marca->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td>
                                    @can('marcas.editar')
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarMarca{{ $marca->id }}" title="Editar">
                                            <i class="lni lni-pencil"></i>
                                        </button>
                                    @endcan
                                    @can('marcas.eliminar')
                                        <form class="d-inline" method="POST" action="{{ route('marcas.destroy', $marca) }}" onsubmit="return confirm('¿Eliminar esta marca?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="lni lni-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay marcas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('marcas.crear')
        <div class="modal fade" id="crearMarca" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('marcas.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5>Nueva marca o empresa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('marcas.form', ['marca' => null])
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('marcas.editar')
        @foreach ($marcas as $marca)
            <div class="modal fade" id="editarMarca{{ $marca->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('marcas.update', $marca) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5>Editar marca</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @include('marcas.form', ['marca' => $marca])
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endcan
@endsection
