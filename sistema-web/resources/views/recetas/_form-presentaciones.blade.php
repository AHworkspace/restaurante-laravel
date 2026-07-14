@csrf
@if($receta)
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" value="{{ old('nombre', $receta?->nombre) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Tiempo de preparacion</label>
        <input type="number" min="1" class="form-control" name="tiempo_preparacion" value="{{ old('tiempo_preparacion', $receta?->tiempo_preparacion) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Precio</label>
        <input type="number" min="0" step="0.01" class="form-control" name="precio" value="{{ old('precio', $receta?->precio) }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Descripcion</label>
        <textarea class="form-control" name="descripcion">{{ old('descripcion', $receta?->descripcion) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Indicaciones</label>
        <textarea class="form-control" name="indicaciones" required>{{ old('indicaciones', $receta?->indicaciones) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Imagen</label>
        <input type="file" class="form-control" name="imagen" accept="image/*">
    </div>
</div>

<section class="border-top mt-4 pt-4">
    <h4>Presentaciones utilizadas</h4>
    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-7">
            <label class="form-label">Presentacion</label>
            <select id="presentacion-temp" class="form-select">
                <option value="">Selecciona una presentacion</option>
                @foreach($presentaciones->groupBy(fn($p) => $p->insumo?->nombre ?: 'Sin insumo') as $nombreInsumo => $grupo)
                    <optgroup label="{{ $nombreInsumo }}">
                        @foreach($grupo as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->nombre }} ({{ $p->unidadStock()?->abreviatura ?: '-' }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Cantidad por porcion</label>
            <input id="cantidad-temp" type="number" min="0.0001" step="0.0001" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" id="agregar-presentacion" class="btn btn-secondary">
                <i class="lni lni-plus"></i> Agregar
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Presentacion</th>
                    <th>Cantidad</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="presentaciones-receta">
                @if($receta)
                    @foreach($receta->insumos as $insumo)
                        @php($p = $presentaciones->firstWhere('id', $insumo->pivot->presentacion_id))
                        @if($p)
                            <tr>
                                <td>{{ $p->insumo?->nombre }}</td>
                                <td>{{ $p->nombre }} ({{ $p->unidadStock()?->abreviatura ?: '-' }})</td>
                                <td>{{ $insumo->pivot->cantidad }}</td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm quitar"><i class="lni lni-trash-can"></i></button>
                                    <input type="hidden" name="insumos[{{ $loop->index }}][presentacion_id]" value="{{ $p->id }}">
                                    <input type="hidden" name="insumos[{{ $loop->index }}][cantidad]" value="{{ $insumo->pivot->cantidad }}">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</section>

<div class="mt-4">
    <button class="btn btn-primary">Guardar receta</button>
    <a class="btn btn-secondary" href="{{ route('recetas.index') }}">Cancelar</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let i = document.querySelectorAll('#presentaciones-receta tr').length;
    const tbody = document.getElementById('presentaciones-receta');
    const select = document.getElementById('presentacion-temp');
    const cantidad = document.getElementById('cantidad-temp');

    tbody.addEventListener('click', event => {
        const boton = event.target.closest('.quitar');
        if (boton) boton.closest('tr').remove();
    });

    document.getElementById('agregar-presentacion').addEventListener('click', () => {
        if (!select.value || !cantidad.value || Number(cantidad.value) <= 0) return;
        const option = select.selectedOptions[0];
        const insumo = option.closest('optgroup')?.label || 'Sin insumo';
        const presentacion = option.textContent.trim();
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${insumo}</td>
            <td>${presentacion}</td>
            <td>${cantidad.value}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm quitar"><i class="lni lni-trash-can"></i></button>
                <input type="hidden" name="insumos[${i}][presentacion_id]" value="${select.value}">
                <input type="hidden" name="insumos[${i}][cantidad]" value="${cantidad.value}">
            </td>`;
        tbody.appendChild(tr);
        i++;
        cantidad.value = '';
    });
});
</script>
