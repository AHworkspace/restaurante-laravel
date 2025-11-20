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
                    <h2>{{ __('Editar Receta') }}</h2>
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

                <form action="{{ route('recetas.update', $receta->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="input-style-1">
                        <label>Nombre de la receta</label>
                        <input type="text" name="nombre" value="{{ $receta->nombre }}" placeholder="Ingresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción (para el menú principal)</label>
                        <textarea placeholder="Ingresa una descripción corta que se mostrará en el menú principal. Ej: 'Una pizza clásica italiana con masa fina, salsa de tomate, mozzarella fresca y albahaca. Perfecta para una cena ligera.'" name="descripcion" rows="3">{{ old('descripcion', $receta->descripcion) }}</textarea>
                        <small class="text-muted">Esta descripción aparecerá en las tarjetas del menú principal.</small>
                    </div>

                    <div class="input-style-1">
                        <label>Indicaciones (instrucciones de preparación)</label>
                        <textarea placeholder="Ingresa las indicaciones que debe tener tu receta.." name="indicaciones" rows="5">{{ $receta->indicaciones }}</textarea>
                    </div>

                    <div class="input-style-1">
                        <label>Tiempo de preparación</label>
                        <input type="number" name="tiempo_preparacion" value="{{ $receta->tiempo_preparacion }}" placeholder="Tiempo en minutos">
                    </div>

                    <div class="input-style-1">
                        <label>Precio</label>
                        <input type="number" name="precio" value="{{ $receta->precio }}" placeholder="Precio de la receta" min="0" step="0.01">
                    </div>

                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                        @if($receta->imagen)
                            @php
                                $rutaArchivo = storage_path('app/public/' . $receta->imagen);
                                $imagenUrl = file_exists($rutaArchivo) ? asset('storage/' . $receta->imagen) : asset('images/recetas.jpg');
                            @endphp
                            <div class="mt-2">
                                <span>Imagen actual:</span><br>
                                <img src="{{ $imagenUrl }}" alt="Imagen actual" style="max-width: 200px; border-radius: 8px;">
                            </div>
                        @endif
                    </div>

                    <!-- insumos -->
                    <div class="input-style-1">
                        <label>Insumos</label>
                        <div class="table-wrapper table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="insumos-table-body">
                                    @foreach ($receta->insumos as $index => $insumo)
                                        <tr>
                                            <td>{{ $insumo->nombre }}</td>
                                            <td>{{ $insumo->pivot->cantidad }}</td>
                                            <td>
                                                <button type="button" class="btn btn-danger remove-insumo">
                                                    <i class="lni lni-trash-can"></i>
                                                </button>
                                            </td>
                                            <td style="display:none">
                                                <input type="hidden" name="insumos[{{ $index }}][id]" value="{{ $insumo->id }}">
                                                <input type="hidden" name="insumos[{{ $index }}][cantidad]" value="{{ $insumo->pivot->cantidad }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div class="select-style-1">
                                <label>Insumo</label>
                                <div class="select-position">
                                    <select id="insumo-temp">
                                        @foreach ($insumos as $insumo)
                                            <option value="{{ $insumo->id }}">{{ $insumo->nombre }} en {{$insumo->unidad_medida->abreviatura}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="input-style-1">
                                <label>Cantidad Requerida</label>
                                <input type="number" id="cantidad-temp" placeholder="Cantidad" min="1">
                            </div>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-success mt-5" id="add-insumo">Agregar Insumo</button>
                        </div>
                        <div class="col-2"></div>
                    </div>

                    <script>
                        // Asignar evento a los botones de eliminar ya existentes al cargar la página
                        document.querySelectorAll('.remove-insumo').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                btn.closest('tr').remove();
                            });
                        });
                        let insumoIndex = {{ $receta->insumos->count() }};
                        document.getElementById('add-insumo').addEventListener('click', function() {
                            const tbody = document.getElementById('insumos-table-body');
                            const insumoTemp = document.getElementById('insumo-temp');
                            const cantidadTemp = document.getElementById('cantidad-temp');
                            if (!cantidadTemp.value || cantidadTemp.value <= 0) {
                                alert('Debes ingresar una cantidad válida.');
                                return;
                            }
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${insumoTemp.options[insumoTemp.selectedIndex].text}</td>
                                <td>${cantidadTemp.value}</td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-insumo">
                                        <i class="lni lni-trash-can"></i>
                                    </button>
                                </td>
                                <td style="display:none">
                                    <input type="hidden" name="insumos[${insumoIndex}][id]" value="${insumoTemp.value}">
                                    <input type="hidden" name="insumos[${insumoIndex}][cantidad]" value="${cantidadTemp.value}">
                                </td>
                            `;
                            tbody.appendChild(row);
                            cantidadTemp.value = '';
                            row.querySelector('.remove-insumo').addEventListener('click', function() {
                                row.remove();
                            });
                            insumoIndex++;
                        });
                        document.querySelector('form').addEventListener('submit', function(event) {
                            const insumos = document.querySelectorAll('input[name^="insumos["]');
                            if (insumos.length === 0) {
                                alert('Debes agregar al menos un insumo.');
                                event.preventDefault();
                            }
                        });
                    </script>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Actualizar Receta</button>
                        <a href="{{ route('recetas.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
