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
                    <h2>{{ __('Editar Insumo') }}</h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                @if ($errors->any())
                    <div class="alert-box danger-alert">
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li class="text-medium">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('insumos.update', $insumo->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="input-style-1">
                        <label>Nombre del insumo</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $insumo->nombre) }}" placeholder="Integresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción del insumo</label>
                        <textarea placeholder="Descripción.." name="descripcion" rows="5">{{ old('descripcion', $insumo->descripcion) }}</textarea>
                    </div>

                    <div class="input-style-1">
                        <label>Stock mínimo</label>
                        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $insumo->stock_minimo) }}" placeholder="Integresa el stock mínimo" min="0">
                    </div>

                    <div class="input-style-1">
                        <label>Costo estándar (referencial)</label>
                        <input type="number" name="costo_estandar" value="{{ old('costo_estandar', $insumo->costo_estandar) }}" placeholder="Ej: 45.00" min="0" step="0.01">
                        <small class="text-muted">Precio normal por unidad del insumo (solo referencia).</small>
                    </div>

                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                        @if($insumo->imagen)
                            @php
                                $rutaArchivo = storage_path('app/public/' . $insumo->imagen);
                                $imagenUrl = file_exists($rutaArchivo) ? asset('storage/' . $insumo->imagen) : asset('images/cereales.jpg');
                            @endphp
                            <div class="mt-2">
                                <span>Imagen actual:</span><br>
                                <img src="{{ $imagenUrl }}" alt="Imagen actual" style="max-width: 200px; border-radius: 8px;">
                            </div>
                        @endif
                    </div>

                    <div class="select-style-1">
                        <label>Categoria</label>
                        <div class="select-position">
                            <select name="categoria_id">
                                <option value="">Selecciona una categoria</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ $insumo->categoria_id == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="select-style-1">
                        <label>Unidad de medida</label>
                        <div class="select-position">
                            <select name="unidad_medida_id">
                                <option value="">Selecciona una unidad de medida</option>
                                @foreach ($unidades as $unidad)
                                    <option value="{{ $unidad->id }}" {{ $insumo->unidad_medida_id == $unidad->id ? 'selected' : '' }}>{{ $unidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Actualizar Insumo</button>
                        <a href="{{ route('insumos.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
