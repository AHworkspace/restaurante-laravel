@extends('layouts.app')

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

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Ingresa los datos de la nueva receta que deseas registrar.
                        </p>
                    </div>
                </div>
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

                <form action="{{ route('recetas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="input-style-1">
                        <label>Nombre de la receta</label>
                        <input type="text" name="nombre" placeholder="Ingresa el nombre">
                    </div>

                    <div class="input-style-1">
                        <label>Descripción (para el menú principal)</label>
                        <textarea placeholder="Ingresa una descripción corta que se mostrará en el menú principal. Ej: 'Una pizza clásica italiana con masa fina, salsa de tomate, mozzarella fresca y albahaca. Perfecta para una cena ligera.'" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                        <small class="text-muted">Esta descripción aparecerá en las tarjetas del menú principal.</small>
                    </div>

                    <div class="input-style-1">
                        <label>Indicaciones (instrucciones de preparación)</label>
                        <textarea placeholder="Ingresa las indicaciones que debe tener tu receta.." name="indicaciones" rows="5"></textarea>
                    </div>

                    <!-- stock_minimum -->
                    <div class="input-style-1">
                        <label>Tiempo de preparación</label>
                        <input type="number" name="tiempo_preparacion" placeholder="Tiempo en minutos">
                    </div>

                    <!-- imagen -->
                    <div class="input-style-1">
                        <label>Imagen</label>
                        <input type="file" name="imagen" accept="image/*">
                    </div>

                    <div class="input-style-1">
                        <label>Precio</label>
                        <input type="number" name="precio" placeholder="Precio de la receta" min="0" step="0.01">
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
                                    <!-- Aquí se agregarán los insumos dinámicamente -->
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
                                <input type="number" id="cantidad-temp" placeholder="Cantidad">
                            </div>
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-success mt-5" id="add-insumo">Agregar Insumo</button>
                        </div>
                        <div class="col-2"></div>
                    </div>

                    <script>
                        let insumoIndex = 0;

                        document.getElementById('add-insumo').addEventListener('click', function() {
                            const tbody = document.getElementById('insumos-table-body');
                            const insumoTemp = document.getElementById('insumo-temp');
                            const cantidadTemp = document.getElementById('cantidad-temp');

                            // Validar que se haya ingresado una cantidad
                            if (!cantidadTemp.value || cantidadTemp.value <= 0) {
                                alert('Debes ingresar una cantidad válida.');
                                return;
                            }

                            // Crear la fila con los datos
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${insumoTemp.options[insumoTemp.selectedIndex].text}</td>
                                <td>${cantidadTemp.value}</td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-insumo">
                                        <i class="lni lni-trash-can"></i>
                                    </button>
                                </td>
                            `;

                            // Agregar campos ocultos para enviar los datos al backend
                            const hiddenId = document.createElement('input');
                            hiddenId.type = 'hidden';
                            hiddenId.name = `insumos[${insumoIndex}][id]`;
                            hiddenId.value = insumoTemp.value;

                            const hiddenCantidad = document.createElement('input');
                            hiddenCantidad.type = 'hidden';
                            hiddenCantidad.name = `insumos[${insumoIndex}][cantidad]`;
                            hiddenCantidad.value = cantidadTemp.value;

                            row.appendChild(hiddenId);
                            row.appendChild(hiddenCantidad);

                            tbody.appendChild(row);

                            // Limpiar campos temporales
                            cantidadTemp.value = '';

                            // Manejar la eliminación de la fila
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

                    <!-- categoria_id -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Guardar Receta</button>
                        <a href="{{ route('recetas.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
