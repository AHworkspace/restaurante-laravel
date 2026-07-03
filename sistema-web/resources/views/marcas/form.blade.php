<div class="mb-3">
    <label>Nombre de marca</label>
    <input class="form-control" name="nombre" value="{{ old('nombre', $marca?->nombre) }}" required>
</div>
<div class="mb-3">
    <label>Empresa fabricante</label>
    <input class="form-control" name="empresa_fabricante" value="{{ old('empresa_fabricante', $marca?->empresa_fabricante) }}">
</div>
<div class="mb-3">
    <label>Descripción</label>
    <textarea class="form-control" name="descripcion">{{ old('descripcion', $marca?->descripcion) }}</textarea>
</div>
<div class="mb-3">
    <label>Proveedores que manejan esta marca</label>
    <select class="form-select" name="proveedores_ids[]" multiple size="5">
        @foreach ($proveedores as $proveedor)
            <option value="{{ $proveedor->id }}" @selected($marca?->proveedores->contains($proveedor->id))>
                {{ $proveedor->nombre }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">La asociación es informativa y puede incluir varios proveedores.</small>
</div>
<input type="hidden" name="activo" value="0">
<label>
    <input type="checkbox" name="activo" value="1" @checked($marca?->activo ?? true)> Activa
</label>
