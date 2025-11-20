@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background-color: #6F4E37; color: white;">
                    <h4 class="mb-0">Registrar Movimiento de Inventario</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('movimientos.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="insumo_id" class="form-label">Insumo</label>
                            <select name="insumo_id" id="insumo_id" class="form-control" required>
                                <option value="">Seleccione un insumo</option>
                                @foreach($insumos as $insumo)
                                    <option value="{{ $insumo->id }}" {{ old('insumo_id') == $insumo->id ? 'selected' : '' }}>{{ $insumo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control" min="0.01" step="0.01" required value="{{ old('cantidad') }}">
                        </div>
                        <div class="mb-3">
                            <label for="unidad_medida_id" class="form-label">Unidad de Medida</label>
                            <select name="unidad_medida_id" id="unidad_medida_id" class="form-control">
                                <option value="">Seleccione unidad (por defecto: unidad del insumo)</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                @endforeach
                            </select>
                            <small id="unidad-base-info" class="form-text text-muted"></small>
                            <small id="conversion-info" class="form-text text-info" style="display: none;"></small>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Movimiento</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="entrada" {{ old('tipo', 'entrada') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="salida" {{ old('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                            </select>
                        </div>

                        <div id="entrada-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="costo_compra" class="form-label">Costo total de esta compra (Bs.)</label>
                                <input type="number" name="costo_compra" id="costo_compra" class="form-control" min="0" step="0.01" value="{{ old('costo_compra') }}">
                                <small id="costo-sugerido" class="form-text text-muted"></small>
                            </div>
                            <div class="mb-3">
                                <label for="proveedor_id" class="form-label">Proveedor</label>
                                <select name="proveedor_id" id="proveedor_id" class="form-control">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="detalle_compra" class="form-label">Detalle de la compra (opcional)</label>
                                <textarea name="detalle_compra" id="detalle_compra" class="form-control" rows="2" placeholder="Ej: Compra semanal de verduras">{{ old('detalle_compra') }}</textarea>
                            </div>
                        </div>

                        <div id="salida-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="receta_id" class="form-label">Receta donde se usará (opcional)</label>
                                <select name="receta_id" id="receta_id" class="form-control">
                                    <option value="">Selecciona una receta</option>
                                    @foreach($recetas as $receta)
                                        <option value="{{ $receta->id }}" {{ old('receta_id') == $receta->id ? 'selected' : '' }}>{{ $receta->nombre }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Solo es informativo, no afecta las ventas.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo</label>
                            <input type="text" name="motivo" id="motivo" class="form-control" maxlength="100" required value="{{ old('motivo') }}">
                        </div>
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required value="{{ old('fecha', date('Y-m-d')) }}">
                        </div>
                        <div class="text-end">
                            <a href="{{ route('movimientos.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary" style="background-color: #6F4E37; border-color: #6F4E37;">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $insumosJson = $insumos->mapWithKeys(function($insumo) {
        return [$insumo->id => [
            'unidad_id' => $insumo->unidad_medida->id ?? null,
            'unidad_nombre' => $insumo->unidad_medida->nombre ?? '',
            'unidad_abrev' => $insumo->unidad_medida->abreviatura ?? '',
            'costo_estandar' => $insumo->costo_estandar,
        ]];
    })->toArray();

    // Agregar conversiones de unidades para el cálculo del costo
    $conversionesJson = \App\Models\ConversionesUnidades::with(['unidadOrigen', 'unidadDestino'])->get()
        ->map(function($conv) {
            return [
                'origen_id' => $conv->unidad_origen_id,
                'destino_id' => $conv->unidad_destino_id,
                'factor' => $conv->factor_conversion,
            ];
        })->toArray();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const insumoSelect = document.getElementById('insumo_id');
    const unidadSelect = document.getElementById('unidad_medida_id');
    const cantidadInput = document.getElementById('cantidad');
    const unidadBaseInfo = document.getElementById('unidad-base-info');
    const conversionInfo = document.getElementById('conversion-info');
    const tipoSelect = document.getElementById('tipo');
    const entradaFields = document.getElementById('entrada-fields');
    const salidaFields = document.getElementById('salida-fields');
    const costoInput = document.getElementById('costo_compra');
    const costoSugerido = document.getElementById('costo-sugerido');

    const insumosData = @json($insumosJson);
    const conversionesData = @json($conversionesJson);

    // Función para obtener factor de conversión (puede ser directa o indirecta)
    // Evita recursión infinita usando un set de unidades visitadas
    // Retorna: cuántas unidades destino equivalen a 1 unidad origen
    function obtenerFactorConversion(unidadOrigenId, unidadDestinoId, visitadas = new Set()) {
        // Convertir a números para comparación correcta
        unidadOrigenId = parseInt(unidadOrigenId);
        unidadDestinoId = parseInt(unidadDestinoId);

        if (unidadOrigenId === unidadDestinoId) {
            return 1;
        }

        // Evitar recursión infinita
        const key = unidadOrigenId + '_' + unidadDestinoId;
        if (visitadas.has(key)) {
            return null;
        }
        visitadas.add(key);

        // Buscar conversión directa (origen -> destino)
        // Ejemplo: Arroba -> Kilogramo: 1 arroba = 11.3398 kg
        const conversionDirecta = conversionesData.find(c => {
            const origenId = parseInt(c.origen_id);
            const destinoId = parseInt(c.destino_id);
            return origenId === unidadOrigenId && destinoId === unidadDestinoId;
        });
        if (conversionDirecta) {
            return parseFloat(conversionDirecta.factor);
        }

        // Buscar conversión inversa (destino -> origen)
        // Ejemplo: Kilogramo -> Arroba: 1 kg = 1/11.3398 arrobas
        const conversionInversa = conversionesData.find(c => {
            const origenId = parseInt(c.origen_id);
            const destinoId = parseInt(c.destino_id);
            return origenId === unidadDestinoId && destinoId === unidadOrigenId;
        });
        if (conversionInversa) {
            return 1 / parseFloat(conversionInversa.factor);
        }

        // Intentar conversión indirecta a través de unidades intermedias
        // Buscar todas las unidades que tienen conversión con la unidad origen
        const conversionesOrigen = conversionesData.filter(c => {
            const origenId = parseInt(c.origen_id);
            const destinoId = parseInt(c.destino_id);
            return origenId === unidadOrigenId || destinoId === unidadOrigenId;
        });

        for (const conv of conversionesOrigen) {
            const origenId = parseInt(conv.origen_id);
            const destinoId = parseInt(conv.destino_id);
            const unidadIntermedia = origenId === unidadOrigenId ? destinoId : origenId;

            // Evitar usar la unidad destino como intermedia
            if (unidadIntermedia === unidadDestinoId) {
                continue;
            }

            // Factor de origen a intermedia
            // Si la conversión es origen -> intermedia, usar el factor directamente
            // Si es intermedia -> origen, usar 1/factor
            const factor1 = origenId === unidadOrigenId
                ? parseFloat(conv.factor)
                : (1 / parseFloat(conv.factor));

            // Factor de intermedia a destino (llamada recursiva con visitadas)
            const factor2 = obtenerFactorConversion(unidadIntermedia, unidadDestinoId, new Set(visitadas));

            if (factor2 !== null && !isNaN(factor2)) {
                return factor1 * factor2;
            }
        }

        return null;
    }

    // Función para convertir cantidad
    function convertirCantidad(cantidad, unidadOrigenId, unidadDestinoId) {
        const factor = obtenerFactorConversion(unidadOrigenId, unidadDestinoId);
        if (factor === null) {
            return cantidad; // No hay conversión, retornar original
        }
        return cantidad * factor;
    }

    function actualizarUnidadBase() {
        const insumoId = insumoSelect.value;
        if (insumoId && insumosData[insumoId]) {
            const unidad = insumosData[insumoId];
            unidadBaseInfo.textContent = 'ℹ️ Unidad base del insumo: ' + unidad.unidad_nombre + ' (' + unidad.unidad_abrev + ')';

            // Pre-seleccionar unidad base si no hay selección
            if (!unidadSelect.value) {
                unidadSelect.value = unidad.unidad_id;
            }
        } else {
            unidadBaseInfo.textContent = '';
        }
        actualizarConversion();
    }

    function actualizarConversion() {
        const insumoId = insumoSelect.value;
        const unidadId = unidadSelect.value;
        const cantidad = parseFloat(cantidadInput.value);

        if (insumoId && unidadId && cantidad && insumosData[insumoId]) {
            const unidadBase = insumosData[insumoId];
            if (unidadId != unidadBase.unidad_id) {
                // Mostrar que se convertirá (la conversión real se hace en el servidor)
                conversionInfo.style.display = 'block';
                conversionInfo.textContent = '💡 La cantidad se convertirá automáticamente a ' + unidadBase.unidad_abrev;
            } else {
                conversionInfo.style.display = 'none';
            }
        } else {
            conversionInfo.style.display = 'none';
        }

        actualizarCostoSugerido();
    }

    function actualizarTipoCampos() {
        if (tipoSelect.value === 'entrada') {
            entradaFields.style.display = 'block';
            salidaFields.style.display = 'none';
            actualizarCostoSugerido();
        } else {
            entradaFields.style.display = 'none';
            salidaFields.style.display = 'block';
        }
    }

    function actualizarCostoSugerido() {
        if (tipoSelect.value !== 'entrada') {
            costoSugerido.textContent = '';
            return;
        }

        const insumoId = insumoSelect.value;
        const cantidad = parseFloat(cantidadInput.value);
        const unidadId = unidadSelect.value;

        if (!insumoId || !insumosData[insumoId] || !insumosData[insumoId].costo_estandar || !cantidad) {
            costoSugerido.textContent = 'Sin costo estándar registrado para este insumo.';
            return;
        }

        const insumo = insumosData[insumoId];
        const unidadBaseId = parseInt(insumo.unidad_id);
        // Si no hay unidad seleccionada o está vacía, usar la unidad base
        const unidadSeleccionadaId = (unidadId && unidadId !== '') ? parseInt(unidadId) : unidadBaseId;
        const costoEstandar = parseFloat(insumo.costo_estandar);

        let costoCalculado = 0;
        let mensaje = '';

        // Obtener nombre de la unidad seleccionada
        const unidadSelectElement = unidadSelect.options[unidadSelect.selectedIndex];
        const unidadNombre = unidadSelectElement ? unidadSelectElement.text.split('(')[0].trim() : insumo.unidad_abrev;

        // Si es la misma unidad base, cálculo directo
        if (unidadSeleccionadaId === unidadBaseId) {
            costoCalculado = (costoEstandar * cantidad).toFixed(2);
            mensaje = '💰 Costo sugerido: Bs. ' + costoCalculado +
                     ' (' + cantidad + ' ' + insumo.unidad_abrev + ' × Bs. ' + costoEstandar + ' por ' + insumo.unidad_abrev + ')';
        } else {
            // Diferente unidad: necesitamos convertir
            // La función obtenerFactorConversion busca conversión directa, inversa e indirecta
            // Retorna: cuántas unidades seleccionadas equivalen a 1 unidad base
            // Ejemplos:
            // - Arroba -> Kilogramo: factor = 11.3398 (1 arroba = 11.3398 kg)
            // - Arroba -> Libra: factor = 25 (1 arroba = 25 libras)
            // - Quintal -> Kilogramo: factor = 45.3592 (1 quintal = 45.3592 kg)
            const factor = obtenerFactorConversion(unidadBaseId, unidadSeleccionadaId);

            if (factor === null || factor === 0 || isNaN(factor)) {
                // No hay conversión disponible
                costoCalculado = (costoEstandar * cantidad).toFixed(2);
                mensaje = '💰 Costo sugerido: Bs. ' + costoCalculado + ' (sin conversión disponible, cálculo aproximado)';
            } else {
                // Calcular precio unitario en la unidad seleccionada
                // Fórmula general: Si 1 unidad base = factor unidades seleccionadas
                // Y 1 unidad base cuesta costoEstandar
                // Entonces: 1 unidad seleccionada = costoEstandar / factor
                const precioUnitario = costoEstandar / factor;
                costoCalculado = (precioUnitario * cantidad).toFixed(2);

                mensaje = '💰 Costo sugerido: Bs. ' + costoCalculado;
                mensaje += ' (1 ' + unidadNombre + ' = Bs. ' + precioUnitario.toFixed(2);
                mensaje += ' | ' + cantidad + ' × Bs. ' + precioUnitario.toFixed(2) + ' = Bs. ' + costoCalculado + ')';
            }
        }

        costoSugerido.textContent = mensaje;
        costoSugerido.style.color = '#6F4E37';
        costoSugerido.style.fontWeight = '500';

        // Siempre actualizar el campo con el costo sugerido cuando cambian los valores
        // (insumo, cantidad, unidad) - el usuario puede editarlo después si quiere
        actualizandoAutomaticamente = true;
        costoInput.value = costoCalculado;
        ultimoCostoCalculado = costoCalculado;
        actualizandoAutomaticamente = false;
    }

    // Variables para controlar actualizaciones automáticas vs manuales
    let actualizandoAutomaticamente = false;
    let ultimoCostoCalculado = '';

    insumoSelect.addEventListener('change', actualizarUnidadBase);
    unidadSelect.addEventListener('change', actualizarConversion);
    cantidadInput.addEventListener('input', actualizarConversion);
    tipoSelect.addEventListener('change', actualizarTipoCampos);

    // Mostrar campos de entrada si el tipo es "entrada" al cargar
    if (tipoSelect.value === 'entrada') {
        entradaFields.style.display = 'block';
        salidaFields.style.display = 'none';
    } else {
        entradaFields.style.display = 'none';
        salidaFields.style.display = 'block';
    }

    // Detectar cuando el usuario edita manualmente el campo de costo
    costoInput.addEventListener('input', function() {
        // Solo marcar como editado si NO es una actualización automática
        if (!actualizandoAutomaticamente && costoInput.value !== ultimoCostoCalculado && costoInput.value !== '') {
            const mensajeOriginal = costoSugerido.textContent;
            if (mensajeOriginal && !mensajeOriginal.includes('(valor editado manualmente)')) {
                costoSugerido.textContent = mensajeOriginal.replace(/ \(valor editado manualmente\)$/, '') + ' (valor editado manualmente)';
                costoSugerido.style.color = '#666';
            }
        }
    });

    // Asegurar que los campos se muestren correctamente antes de enviar el formulario
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Asegurar que los campos de entrada estén visibles si el tipo es "entrada"
            if (tipoSelect.value === 'entrada') {
                entradaFields.style.display = 'block';
            }
        });
    }

    // Inicializar al cargar
    actualizarUnidadBase();
    actualizarTipoCampos();

    // Calcular costo inicial si hay valores pre-cargados y es tipo entrada
    if (tipoSelect.value === 'entrada' && insumoSelect.value && cantidadInput.value) {
        actualizarCostoSugerido();
    }
});
</script>
@endsection
