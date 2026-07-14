@csrf
@if($menuDia) @method('PUT') @endif
@php
    $editando = (bool) $menuDia;
    $seleccionadas = $seleccionadas ?? [];
    $presentacionesSeleccionadas = $presentacionesSeleccionadas ?? [];
    $recetasPublicadas = $editando ? $recetas->whereIn('id', $seleccionadas) : collect();
    $recetasNuevas = $editando ? $recetas->reject(fn($receta) => in_array($receta->id, $seleccionadas)) : $recetas;
    $presentacionesPublicadas = $editando ? $presentacionesDirectas->whereIn('id', $presentacionesSeleccionadas) : collect();
    $presentacionesNuevas = $editando ? $presentacionesDirectas->reject(fn($presentacion) => in_array($presentacion->id, $presentacionesSeleccionadas)) : $presentacionesDirectas;
    $hayPublicados = $recetasPublicadas->isNotEmpty() || $presentacionesPublicadas->isNotEmpty();
    $hayNuevos = $recetasNuevas->isNotEmpty() || $presentacionesNuevas->isNotEmpty();
    $resumenItem = function ($inicial, $adiciones, $disponible) {
        $adiciones = is_array($adiciones) ? $adiciones : [];
        $total = (int) $inicial + array_sum($adiciones);
        return [
            'inicial' => (int) $inicial,
            'adiciones' => $adiciones,
            'total' => $total,
            'disponible' => (int) $disponible,
            'vendido' => max(0, $total - (int) $disponible),
        ];
    };
@endphp

<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Titulo</label><input class="form-control" name="titulo" value="{{ old('titulo',$menuDia?->titulo) }}" required></div>
    <div class="col-md-3"><label class="form-label">Tipo de comida</label><select class="form-select" name="tipo_comida_id"><option value="">General</option>@foreach($tiposComida as $tipo)<option value="{{ $tipo->id }}" @selected(old('tipo_comida_id',$menuDia?->tipo_comida_id)==$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Fecha</label><input type="date" class="form-control" name="fecha" value="{{ old('fecha',$fechaPredefinida) }}" required></div>
    <div class="col-md-3"><label class="form-label">Hora inicio</label><input type="time" class="form-control" name="hora_inicio" value="{{ old('hora_inicio',substr((string)$menuDia?->hora_inicio,0,5)) }}"></div>
    <div class="col-md-3"><label class="form-label">Hora fin</label><input type="time" class="form-control" name="hora_fin" value="{{ old('hora_fin',substr((string)$menuDia?->hora_fin,0,5)) }}"></div>
    <div class="col-md-3 d-flex align-items-end"><label class="form-check"><input type="checkbox" class="form-check-input" name="visible_para_clientes" value="1" @checked(old('visible_para_clientes',$menuDia?->visible_para_clientes))> Visible para clientes</label></div>
    <div class="col-md-3 d-flex align-items-end"><label class="form-check"><input type="checkbox" class="form-check-input" name="visible_en_horario" value="1" @checked(old('visible_en_horario',$menuDia?->visible_en_horario))> Aplicar horario</label></div>
    <div class="col-12"><label class="form-label">Descripcion</label><textarea class="form-control" name="descripcion">{{ old('descripcion',$menuDia?->descripcion) }}</textarea></div>
</div>

@if($hayPublicados)
<section class="border-top mt-4 pt-4">
    <h4 class="mb-3">Ya publicados en este menu</h4>

    @if($recetasPublicadas->isNotEmpty())
        <h5 class="mb-3">Platos</h5>
        <div class="row">
            @foreach($recetasPublicadas as $receta)
                @php
                    $resumen = $resumenItem($cantidadesIniciales[$receta->id] ?? 0, $adicionesRecetas[$receta->id] ?? [], $cantidades[$receta->id] ?? 0);
                @endphp
                <div class="col-md-6 col-lg-4 mb-3"><div class="border rounded p-3 h-100">
                    <input type="hidden" name="recetas[]" value="{{ $receta->id }}">
                    <strong class="d-block mb-2">{{ $receta->nombre }}</strong>
                    <div class="small text-muted mb-2">Inicial: {{ $resumen['inicial'] }} | Total: {{ $resumen['total'] }} | Vendido: {{ $resumen['vendido'] }} | Disponible: {{ $resumen['disponible'] }}</div>
                    <div class="small mb-2">Adiciones: @forelse($resumen['adiciones'] as $adicion)<span class="badge bg-success me-1">+{{ $adicion }}</span>@empty <span class="text-muted">-</span> @endforelse</div>
                    <label class="small">Tipo de produccion</label>
                    <select class="form-select mb-2" name="tipos_produccion_recetas[{{ $receta->id }}]"><option value="">Sin grupo</option>@foreach($tiposProduccion as $tipo)<option value="{{ $tipo->id }}" @selected(old('tipos_produccion_recetas.'.$receta->id,$tiposRecetas[$receta->id]??null)==$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select>
                    <label class="small">Adicionar ahora</label>
                    <input type="number" min="0" class="form-control mb-2" name="adiciones[{{ $receta->id }}]" value="{{ old('adiciones.'.$receta->id,0) }}">
                    <label class="small">Precio de venta (Bs.)</label>
                    <input type="number" min="0" step="0.01" class="form-control mb-2" name="precios_recetas[{{ $receta->id }}]" value="{{ old('precios_recetas.'.$receta->id,$preciosRecetas[$receta->id]??$receta->precio) }}">
                    <label class="form-check small text-danger mb-0"><input class="form-check-input" type="checkbox" name="quitar_recetas[]" value="{{ $receta->id }}" @checked(in_array($receta->id, old('quitar_recetas', [])))> Quitar del menu</label>
                </div></div>
            @endforeach
        </div>
    @endif

    @if($presentacionesPublicadas->isNotEmpty())
        <h5 class="mb-3 mt-2">Productos directos</h5>
        <div class="row">
            @foreach($presentacionesPublicadas as $presentacion)
                @php
                    $imagen = $presentacion->imagen ? asset('storage/'.$presentacion->imagen) : ($presentacion->insumo->imagen ? asset('storage/'.$presentacion->insumo->imagen) : null);
                    $stockActual = (float) $presentacion->stockDisponible();
                    $unidadStock = $presentacion->unidadStock()?->abreviatura ?? $presentacion->unidadStock()?->nombre ?? '';
                    $resumen = $resumenItem($cantidadesInicialesPresentaciones[$presentacion->id] ?? 0, $adicionesPresentaciones[$presentacion->id] ?? [], $cantidadesPresentaciones[$presentacion->id] ?? 0);
                @endphp
                <div class="col-md-6 col-lg-4 mb-3"><div class="border rounded p-3 h-100">
                    <input type="hidden" name="presentaciones_directas[]" value="{{ $presentacion->id }}">
                    @if($imagen)<img src="{{ $imagen }}" alt="{{ $presentacion->nombre_completo }}" class="mb-2" style="width:56px;height:56px;object-fit:cover;border-radius:6px">@endif
                    <strong class="d-block mb-2">{{ $presentacion->nombre_completo }}</strong>
                    <p class="small text-muted mb-2">Stock actual: <strong>{{ number_format($stockActual,2) }} {{ $unidadStock }}</strong></p>
                    <div class="small text-muted mb-2">Inicial: {{ $resumen['inicial'] }} | Total: {{ $resumen['total'] }} | Vendido: {{ $resumen['vendido'] }} | Disponible: {{ $resumen['disponible'] }}</div>
                    <div class="small mb-2">Adiciones: @forelse($resumen['adiciones'] as $adicion)<span class="badge bg-success me-1">+{{ $adicion }}</span>@empty <span class="text-muted">-</span> @endforelse</div>
                    <label class="small">Tipo de produccion</label>
                    <select class="form-select mb-2" name="tipos_produccion_presentaciones[{{ $presentacion->id }}]"><option value="">Sin grupo</option>@foreach($tiposProduccion as $tipo)<option value="{{ $tipo->id }}" @selected(old('tipos_produccion_presentaciones.'.$presentacion->id,$tiposPresentaciones[$presentacion->id]??null)==$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select>
                    <label class="small">Adicionar ahora</label>
                    <input type="number" min="0" class="form-control mb-2" name="adiciones_presentaciones[{{ $presentacion->id }}]" value="{{ old('adiciones_presentaciones.'.$presentacion->id,0) }}">
                    <label class="small">Precio de venta (Bs.)</label>
                    <input type="number" min="0" step="0.01" class="form-control mb-2" name="precios_presentaciones[{{ $presentacion->id }}]" value="{{ old('precios_presentaciones.'.$presentacion->id,$preciosPresentaciones[$presentacion->id]??'') }}">
                    <label class="form-check small text-danger mb-0"><input class="form-check-input" type="checkbox" name="quitar_presentaciones[]" value="{{ $presentacion->id }}" @checked(in_array($presentacion->id, old('quitar_presentaciones', [])))> Quitar del menu</label>
                </div></div>
            @endforeach
        </div>
    @endif
</section>
@endif

@if($hayNuevos)
<section class="border-top mt-4 pt-4">
    <h4 class="mb-3">{{ $editando ? 'Agregar al menu' : 'Productos para publicar' }}</h4>

    @if($recetasNuevas->isNotEmpty())
        <h5 class="mb-3">Platos disponibles</h5>
        <div class="row">
            @foreach($recetasNuevas as $receta)
                <div class="col-md-6 col-lg-4 mb-3"><div class="border rounded p-3 h-100">
                    <label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="recetas[]" value="{{ $receta->id }}" @checked(in_array($receta->id, old('recetas', $seleccionadas)))> <strong>{{ $receta->nombre }}</strong></label>
                    <label class="small">Tipo de produccion</label>
                    <select class="form-select mb-2" name="tipos_produccion_recetas[{{ $receta->id }}]"><option value="">Sin grupo</option>@foreach($tiposProduccion as $tipo)<option value="{{ $tipo->id }}" @selected(old('tipos_produccion_recetas.'.$receta->id,$tiposRecetas[$receta->id]??null)==$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select>
                    <label class="small">Cantidad inicial</label>
                    <input type="number" min="1" class="form-control mb-2" name="cantidades[{{ $receta->id }}]" value="{{ old('cantidades.'.$receta->id,1) }}">
                    <label class="small">Precio de venta (Bs.)</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="precios_recetas[{{ $receta->id }}]" value="{{ old('precios_recetas.'.$receta->id,$receta->precio) }}">
                </div></div>
            @endforeach
        </div>
    @endif

    @if($presentacionesNuevas->isNotEmpty())
        <h5 class="mb-3 mt-2">Productos directos disponibles</h5>
        <div class="row">
            @foreach($presentacionesNuevas as $presentacion)
                @php
                    $imagen = $presentacion->imagen ? asset('storage/'.$presentacion->imagen) : ($presentacion->insumo->imagen ? asset('storage/'.$presentacion->insumo->imagen) : null);
                    $stockActual = (float) $presentacion->stockDisponible();
                    $unidadStock = $presentacion->unidadStock()?->abreviatura ?? $presentacion->unidadStock()?->nombre ?? '';
                @endphp
                <div class="col-md-6 col-lg-4 mb-3"><div class="border rounded p-3 h-100">
                    @if($imagen)<img src="{{ $imagen }}" alt="{{ $presentacion->nombre_completo }}" class="mb-2" style="width:56px;height:56px;object-fit:cover;border-radius:6px">@endif
                    <label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="presentaciones_directas[]" value="{{ $presentacion->id }}" @checked(in_array($presentacion->id, old('presentaciones_directas', $presentacionesSeleccionadas)))> <strong>{{ $presentacion->nombre_completo }}</strong></label>
                    <p class="small text-muted mb-2">Stock actual: <strong>{{ number_format($stockActual,2) }} {{ $unidadStock }}</strong></p>
                    <label class="small">Tipo de produccion</label>
                    <select class="form-select mb-2" name="tipos_produccion_presentaciones[{{ $presentacion->id }}]"><option value="">Sin grupo</option>@foreach($tiposProduccion as $tipo)<option value="{{ $tipo->id }}" @selected(old('tipos_produccion_presentaciones.'.$presentacion->id,$tiposPresentaciones[$presentacion->id]??null)==$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select>
                    <label class="small">Cantidad inicial</label>
                    <input type="number" min="1" class="form-control mb-2" name="cantidades_presentaciones[{{ $presentacion->id }}]" value="{{ old('cantidades_presentaciones.'.$presentacion->id,1) }}">
                    <label class="small">Precio de venta (Bs.)</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="precios_presentaciones[{{ $presentacion->id }}]" value="{{ old('precios_presentaciones.'.$presentacion->id,$preciosPresentaciones[$presentacion->id]??'') }}">
                </div></div>
            @endforeach
        </div>
    @endif
</section>
@endif

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary"><i class="lni lni-save"></i> Guardar menu</button> <a href="{{ route('menus-dia.index') }}" class="btn btn-secondary">Cancelar</a>
