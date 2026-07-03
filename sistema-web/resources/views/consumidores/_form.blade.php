@csrf
@if(isset($consumidor)) @method('PUT') @endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="nombre_completo">Nombre completo</label>
        <input class="form-control" id="nombre_completo" name="nombre_completo" required
               value="{{ old('nombre_completo', $consumidor->nombre_completo ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="ci">CI</label>
        <input class="form-control" id="ci" name="ci" required value="{{ old('ci', $consumidor->ci ?? '') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="codigo_unico">Codigo unico</label>
        <input class="form-control" id="codigo_unico" name="codigo_unico"
               value="{{ old('codigo_unico', $consumidor->codigo_unico ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="email">Correo</label>
        <input type="email" class="form-control" id="email" name="email"
               value="{{ old('email', $consumidor->email ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="fuerza_id">Fuerza</label>
        <select class="form-control" id="fuerza_id" name="fuerza_id">
            <option value="">Sin asignar</option>
            @foreach($fuerzas as $fuerza)
                <option value="{{ $fuerza->id }}" @selected(old('fuerza_id', $consumidor->fuerza_id ?? null) == $fuerza->id)>{{ $fuerza->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="institucion_id">Institucion</label>
        <select class="form-control" id="institucion_id" name="institucion_id">
            <option value="">Sin asignar</option>
            @foreach($instituciones as $institucion)
                <option value="{{ $institucion->id }}" @selected(old('institucion_id', $consumidor->institucion_id ?? null) == $institucion->id)>{{ $institucion->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="grado_id">Grado o rango</label>
        <select class="form-control" id="grado_id" name="grado_id">
            <option value="">Sin asignar</option>
            @foreach($grados as $grado)
                <option value="{{ $grado->id }}" @selected(old('grado_id', $consumidor->grado_id ?? null) == $grado->id)>{{ $grado->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label" for="activo">Estado</label>
        <select class="form-control" id="activo" name="activo">
            <option value="1" @selected(old('activo', $consumidor->activo ?? true) == 1)>Activo</option>
            <option value="0" @selected(old('activo', $consumidor->activo ?? true) == 0)>Inactivo</option>
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="observaciones">Observaciones</label>
        <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $consumidor->observaciones ?? '') }}</textarea>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<button class="btn btn-primary" type="submit">Guardar</button>
<a class="btn btn-secondary" href="{{ route('consumidores.index') }}">Cancelar</a>
