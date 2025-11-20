@extends('layouts.app')

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Insumos del Restaurante') }}</h2>
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
                    <h6 class="mb-10">Tabla de todos los insumos del restaurante.</h6>
                    @role('admin|cocinero|ayudante_cocina')
                    <a href="{{ route('insumos.create') }}" class="btn btn-primary">Crear Insumo</a>
                    @endrole
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-4">
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Buscar insumo por nombre...">
                        </div>
                        <div class="col-md-4">
                            <select name="categoria" class="form-control">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="stock_minimo" class="form-control" value="{{ request('stock_minimo') }}" placeholder="Stock mínimo">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </form>
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
                                    <th>
                                        <h6>Costo Estándar</h6>
                                    </th>
                                    <th class="lead-email">
                                        <h6>Descripción</h6>
                                    </th>
                                    <th>
                                        <h6>Stock Minimo</h6>
                                    </th>
                                    <th>
                                        <h6>Categoria</h6>
                                    </th>
                                    <th>
                                        <h6>Stock Disponible</h6>
                                    </th>
                                    <th>
                                        <h6>Acciones</h6>
                                    </th>
                                </tr>
                                <!-- end table row-->
                            </thead>
                            <tbody>
                                @forelse ($insumos as $insumo)
                                    <tr>
                                        <td class="min-width">
                                            <p>{{ $insumo->id }}</p>
                                        </td>
                                        <td class="min-width">
                                            <div class="lead">
                                                @php
                                                    $imagenUrl = asset('images/cereales.jpg');
                                                    if ($insumo->imagen) {
                                                        $rutaArchivo = storage_path('app/public/' . $insumo->imagen);
                                                        if (file_exists($rutaArchivo)) {
                                                            $imagenUrl = asset('storage/' . $insumo->imagen);
                                                        }
                                                    }
                                                @endphp
                                                <div class="lead-image">
                                                    <img src="{{ $imagenUrl }}" alt="">
                                                </div>
                                                <div class="lead-text">
                                                    <p>{{$insumo->nombre}}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-width">
                                            @if($insumo->costo_estandar)
                                                <p>Bs. {{ number_format($insumo->costo_estandar, 2) }} / {{ $insumo->unidad_medida->abreviatura }}</p>
                                            @else
                                                <p class="text-muted">-</p>
                                            @endif
                                        </td>
                                        <td class="min-width">
                                            <p>{{ $insumo->descripcion }}</p>
                                        </td>
                                        <td class="min-width">
                                            <p>{{ $insumo->stock_minimo }}</p>
                                        </td>
                                        <td class="min-width">
                                            <p>{{ $insumo->categoria->nombre }}</p>
                                        </td>
                                        <td class="min-width">
                                            <p>{{ max(0, $insumo->getCantidadTotal()) }} {{$insumo->unidad_medida->abreviatura}}</p>
                                        </td>
                                        <td>
                                            <div class="action d-flex gap-2">
                                                @role('admin|cocinero|ayudante_cocina')
                                                <a href="{{ route('insumos.edit', $insumo->id) }}"
                                                   class="main-btn dark-btn btn-hover"
                                                   style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;">
                                                    EDITAR
                                                </a>
                                                <form action="{{ route('insumos.destroy', $insumo->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="main-btn dark-btn btn-hover"
                                                            style="background-color: #6F4E37; border-color: #6F4E37; font-size: 14px; padding: 5px 15px; border-radius: 6px;"
                                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este insumo?')">
                                                        ELIMINAR
                                                    </button>
                                                </form>
                                                @endrole
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No existen categorias</td>
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
