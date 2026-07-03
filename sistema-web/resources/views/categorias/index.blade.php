@extends('layouts.app')

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Categorias de Insumos') }}</h2>
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
                    <h6 class="mb-10">Tabla de datos de categorias de insumos</h6>
                    @can('categorias_insumos.crear')
                    <a href="{{ route('categorias.create') }}" class="btn btn-primary">Crear Categoría</a>
                    @endcan
                    <div class="table-wrapper table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <h6>ID</h6>
                                    </th>
                                    <th class="lead-info">
                                        <h6>Nombre</h6>
                                    </th>
                                    <th class="lead-email">
                                        <h6>Descripción</h6>
                                    </th>
                                    <th>
                                        <h6>Acciones</h6>
                                    </th>
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                @forelse ($categorias as $categoria)
                                    <tr>
                                        <td class="min-width">
                                            <p>{{ $categoria->id }}</p>
                                        </td>
                                        <td class="min-width">
                                            <div class="lead">
                                                <div class="lead-text">
                                                    <p>{{ $categoria->nombre }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            <p>{{ $categoria->descripcion }}</p>
                                        </td>
                                        <td>
                                            @canany(['categorias_insumos.editar','categorias_insumos.eliminar'])
                                            <div class="action d-flex" style="gap: 10px;">
                                                <a href="{{ route('categorias.edit', $categoria->id) }}"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #6F4E37; border-color: #6F4E37; font-size: 16px; padding: 8px 16px;">
                                                    EDITAR
                                                </a>
                                                <form action="{{ route('categorias.destroy', $categoria->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #6F4E37; border-color: #6F4E37; font-size: 16px; padding: 8px 16px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                            </div>
                                            @endcanany
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No existen categorias</td>
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
