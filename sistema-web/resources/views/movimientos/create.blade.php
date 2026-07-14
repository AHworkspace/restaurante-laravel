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
                        <div class="mb-3" id="compra-linea-group">
                            <label for="compra_linea_id" class="form-label">Linea de compra a recibir</label>
                            <select name="compra_linea_id" id="compra_linea_id" class="form-control">
                                <option value="">Selecciona una linea de compra pendiente</option>
                                @foreach($lineasCompra as $linea)
                                    <option value="{{ $linea->id }}"
                                            data-insumo="{{ $linea->insumo_id }}"
                                            data-presentacion="{{ $linea->presentacion_id }}"
                                            data-unidad="{{ $linea->unidad_medida_id ?: $linea->insumo?->unidad_medida_id }}"
                                            data-faltante="{{ $linea->cantidad_faltante }}"
                                            data-faltante-base="{{ $linea->cantidad_faltante_base }}"
                                            data-faltante-empaques="{{ $linea->faltante_desglosado['empaques'] }}"
                                            data-faltante-sueltas="{{ $linea->faltante_desglosado['sueltas'] }}"
                                            data-es-empaque="{{ $linea->faltante_desglosado['es_empaque'] ? 1 : 0 }}"
                                            data-costo="{{ $linea->costo_linea }}"
                                            data-factor="{{ $linea->factor_compra_base ?: 1 }}"
                                            data-base-abrev="{{ $linea->unidadInventario?->abreviatura ?: $linea->insumo?->unidad_medida?->abreviatura }}"
                                            data-proveedor="{{ $linea->compra?->proveedorRel?->nombre ?: $linea->compra?->proveedor }}"
                                            data-proveedor-id="{{ $linea->compra?->proveedor_id }}"
                                            data-marca="{{ $linea->marca?->nombre ?: 'Sin especificar' }}"
                                            data-pendiente-texto="{{ $linea->faltanteTexto() }}"
                                            @selected(old('compra_linea_id', request('compra_linea_id')) == $linea->id)>
                                        Compra {{ $linea->compra?->numero_documento ?: '#'.$linea->compra_id }} - {{ $linea->insumo?->nombre }} · {{ $linea->presentacion?->nombre }}@if($linea->formatoEmpaque) · {{ $linea->formatoEmpaque->nombre }}@endif @if($linea->marca)({{ $linea->marca->nombre }})@endif - Faltan {{ number_format($linea->cantidad_faltante, 2) }} {{ $linea->unidadMedida?->abreviatura }}
                                        | Pendiente claro: {{ $linea->faltanteTexto() }}
                                    </option>
                                @endforeach
                            </select>
                            <small id="compra-linea-info" class="form-text text-muted">Las entradas solo se registran desde compras pendientes para controlar faltantes y costos.</small>
                            <div id="compra-linea-resumen" class="alert alert-info mt-2 mb-0" style="display:none;"></div>
                        </div>
                        <div class="mb-3" id="insumo-group">
                            <label for="insumo_id" class="form-label">Insumo</label>
                            <select name="insumo_id" id="insumo_id" class="form-control">
                                <option value="">Seleccione un insumo</option>
                                @foreach($insumos as $insumo)
                                    <option value="{{ $insumo->id }}" {{ old('insumo_id') == $insumo->id ? 'selected' : '' }}>{{ $insumo->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Este campo se llena automaticamente al elegir una linea o una presentacion.</small>
                        </div>
                        <div class="mb-3" id="presentacion-group">
                            <label for="presentacion_id" class="form-label">Presentacion a descontar</label>
                            <select name="presentacion_id" id="presentacion_id" class="form-control">
                                <option value="">Selecciona una presentacion</option>
                                @foreach($presentaciones->groupBy(fn($p) => $p->insumo?->nombre ?: 'Sin insumo') as $nombreInsumo => $grupo)
                                    <optgroup label="{{ $nombreInsumo }}">
                                        @foreach($grupo as $presentacion)
                                            @php
                                                $stockPresentacion = $presentacion->stockDisponible();
                                            @endphp
                                            <option value="{{ $presentacion->id }}"
                                                    data-insumo="{{ $presentacion->insumo_id }}"
                                                    data-stock="{{ $stockPresentacion }}">
                                                {{ $presentacion->nombre }} - Stock: {{ number_format($stockPresentacion, 2) }} {{ $presentacion->unidadStock()?->abreviatura ?: '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <small id="presentacion-ayuda" class="form-text text-muted">En salidas solo se muestran presentaciones con stock disponible.</small>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad que se recibe</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control" min="0" step="0.01" required value="{{ old('cantidad') }}">
                            <small id="cantidad-ayuda" class="form-text text-muted">En entradas, esta cantidad corresponde al formato de la compra seleccionada. En salidas, corresponde a la unidad de medida elegida.</small>
                        </div>
                        <div class="mb-3 d-none" id="cantidad-suelta-group">
                            <label for="cantidad_suelta" class="form-label">Unidades interiores sueltas</label>
                            <input type="number" name="cantidad_suelta" id="cantidad_suelta" class="form-control" min="0" step="0.0001" value="{{ old('cantidad_suelta',0) }}">
                            <small class="form-text text-muted">Bolsas, botellas, latas u otras unidades recibidas fuera de un empaque completo.</small>
                        </div>
                        <div class="mb-3" id="unidad-group">
                            <label for="unidad_medida_id" class="form-label">Unidad de medida del movimiento</label>
                            <select name="unidad_medida_id" id="unidad_medida_id" class="form-control">
                                <option value="">Seleccione unidad (por defecto: unidad del insumo)</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                @endforeach
                            </select>
                            <small id="unidad-base-info" class="form-text text-muted">Solo medidas reales: kg, litro, unidad, arroba, etc. El empaque se maneja desde la compra.</small>
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
    $presentacionesJson=$presentaciones->mapWithKeys(fn($p)=>[$p->id=>['costo_estandar'=>(float)$p->costo_estandar,'unidad_stock_id'=>$p->unidad_stock_id]])->toArray();

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
    const cantidadAyuda = document.getElementById('cantidad-ayuda');
    const cantidadSueltaInput = document.getElementById('cantidad_suelta');
    const cantidadSueltaGroup = document.getElementById('cantidad-suelta-group');
    const presentacionSelect = document.getElementById('presentacion_id');
    const presentacionAyuda = document.getElementById('presentacion-ayuda');
    const unidadBaseInfo = document.getElementById('unidad-base-info');
    const conversionInfo = document.getElementById('conversion-info');
    const tipoSelect = document.getElementById('tipo');
    const compraLineaSelect = document.getElementById('compra_linea_id');
    const compraLineaGroup = document.getElementById('compra-linea-group');
    const compraLineaInfo = document.getElementById('compra-linea-info');
    const compraLineaResumen = document.getElementById('compra-linea-resumen');
    const entradaFields = document.getElementById('entrada-fields');
    const salidaFields = document.getElementById('salida-fields');
    const insumoGroup = document.getElementById('insumo-group');
    const presentacionGroup = document.getElementById('presentacion-group');
    const unidadGroup = document.getElementById('unidad-group');
    const costoInput = document.getElementById('costo_compra');
    const proveedorSelect = document.getElementById('proveedor_id');
    const costoSugerido = document.getElementById('costo-sugerido');
    const detalleCompraInput = document.getElementById('detalle_compra');
    const motivoInput = document.getElementById('motivo');
    const fechaInput = document.getElementById('fecha');

    const insumosData = @json($insumosJson);
    const presentacionesData = @json($presentacionesJson);
    const conversionesData = @json($conversionesJson);

    function bloquearCamposEntrada(bloquear) {
        [
            insumoSelect,
            presentacionSelect,
            unidadSelect,
            cantidadInput,
            cantidadSueltaInput,
            costoInput,
            proveedorSelect,
            detalleCompraInput,
            motivoInput,
            fechaInput,
        ].filter(Boolean).forEach(campo => campo.disabled = bloquear);
    }

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
            let primera='';presentacionSelect.querySelectorAll('option[data-insumo]').forEach(o=>{o.hidden=o.dataset.insumo!==String(insumoId);if(!o.hidden&&!primera)primera=o.value;});if(!presentacionSelect.value||presentacionSelect.selectedOptions[0]?.hidden)presentacionSelect.value=primera;
        } else {
            unidadBaseInfo.textContent = '';
        }
        actualizarConversion();
    }

    function actualizarConversion() {
        const insumoId = insumoSelect.value;
        const unidadId = unidadSelect.value;
        const presentacion=presentacionesData[presentacionSelect.value];
        const costoEstandar=Number(presentacion?.costo_estandar||0);
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
            compraLineaGroup.style.display = 'block';
            insumoGroup.style.display = 'block';
            presentacionGroup.style.display = 'block';
            unidadGroup.style.display = 'block';
            compraLineaSelect.required = true;
            presentacionSelect.required = false;
            insumoSelect.required = false;
            filtrarPresentacionesPorStock(false);
            bloquearCamposEntrada(!compraLineaSelect.value);
            actualizarCostoSugerido();
        } else {
            entradaFields.style.display = 'none';
            salidaFields.style.display = 'block';
            compraLineaGroup.style.display = 'none';
            insumoGroup.style.display = 'none';
            presentacionGroup.style.display = 'block';
            unidadGroup.style.display = 'block';
            compraLineaSelect.required = false;
            presentacionSelect.required = true;
            insumoSelect.required = false;
            compraLineaSelect.value = '';
            filtrarPresentacionesPorStock(true);
            cantidadInput.removeAttribute('max');
            cantidadSueltaGroup.classList.add('d-none');
            cantidadSueltaInput.value = 0;
            cantidadAyuda.textContent = 'En entradas, esta cantidad corresponde al formato de la compra seleccionada. En salidas, corresponde a la unidad de medida elegida.';
            bloquearCamposEntrada(false);
        }
    }

    function filtrarPresentacionesPorStock(soloConStock) {
        let primeraVisible = '';
        presentacionSelect.querySelectorAll('option[data-insumo]').forEach(option => {
            const stock = Number(option.dataset.stock || 0);
            option.hidden = soloConStock && stock <= 0;
            if (!option.hidden && !primeraVisible) primeraVisible = option.value;
        });

        presentacionSelect.querySelectorAll('optgroup').forEach(group => {
            const visibles = Array.from(group.querySelectorAll('option[data-insumo]')).some(option => !option.hidden);
            group.hidden = soloConStock && !visibles;
        });

        if (soloConStock && presentacionSelect.value && presentacionSelect.selectedOptions[0]?.hidden) {
            presentacionSelect.value = '';
        }

        presentacionAyuda.textContent = soloConStock
            ? 'Solo aparecen presentaciones con stock mayor a 0 para poder descontarlas.'
            : 'En entradas la presentacion se llena desde la linea de compra seleccionada.';
    }

    function aplicarLineaCompra() {
        const option = compraLineaSelect.options[compraLineaSelect.selectedIndex];
        if (!compraLineaSelect.value || !option) {
            cantidadInput.removeAttribute('max');
            cantidadInput.value = '';
            cantidadSueltaGroup.classList.add('d-none');
            cantidadSueltaInput.value = 0;
            insumoSelect.value = '';
            presentacionSelect.value = '';
            unidadSelect.value = '';
            costoInput.value = '';
            proveedorSelect.value = '';
            if (detalleCompraInput) detalleCompraInput.value = '';
            if (motivoInput) motivoInput.value = '';
            bloquearCamposEntrada(true);
            compraLineaInfo.textContent = 'Las entradas solo se registran desde compras pendientes para controlar faltantes y costos.';
            compraLineaResumen.style.display = 'none';
            compraLineaResumen.textContent = '';
            return;
        }
        bloquearCamposEntrada(false);
        insumoSelect.value = option.dataset.insumo;
        unidadSelect.value = option.dataset.unidad || '';
        presentacionSelect.value = option.dataset.presentacion || '';
        cantidadInput.max = option.dataset.faltanteEmpaques;
        cantidadInput.value = option.dataset.faltanteEmpaques;
        cantidadAyuda.textContent = 'Cantidad en formato de compra: recibe ' + option.dataset.faltanteEmpaques + ' de la compra seleccionada. Equivalencia pendiente: ' + (option.dataset.pendienteTexto || 'sin detalle') + '.';
        cantidadSueltaInput.value = option.dataset.faltanteSueltas || 0;
        cantidadSueltaGroup.classList.toggle('d-none', option.dataset.esEmpaque !== '1');
        insumoSelect.disabled = false;
        unidadSelect.disabled = false;
        const entradaBase = Number(option.dataset.faltanteBase || 0);
        const pendienteTexto = option.dataset.pendienteTexto || '';
        compraLineaInfo.textContent = 'Proveedor: ' + option.dataset.proveedor + '. Marca/empresa: ' + option.dataset.marca + '. Pendiente por recibir: ' + pendienteTexto + '. Entrada total al inventario: ' + entradaBase.toFixed(4) + ' ' + option.dataset.baseAbrev + '. Costo original: Bs. ' + Number(option.dataset.costo || 0).toFixed(2) + '.';
        compraLineaResumen.style.display = 'block';
        compraLineaResumen.textContent = 'Lectura clara: en Cantidad estás registrando el formato de compra. Para esta línea se recibirá ' + pendienteTexto + ', y eso entrará al stock como ' + entradaBase.toFixed(4) + ' ' + option.dataset.baseAbrev + '.';
        document.getElementById('motivo').value = 'Recepción de compra';
        actualizarUnidadBase();
        costoInput.value = Number(option.dataset.costo || 0).toFixed(2);
        proveedorSelect.value = option.dataset.proveedorId || '';
        ultimoCostoCalculado = costoInput.value;
        costoSugerido.textContent = 'Costo registrado originalmente en la línea de compra: Bs. ' + costoInput.value + '. Puedes modificarlo si corresponde.';
    }

    function actualizarCostoSugerido() {
        if (tipoSelect.value !== 'entrada') {
            costoSugerido.textContent = '';
            return;
        }
        if (compraLineaSelect.value) {
            const option = compraLineaSelect.options[compraLineaSelect.selectedIndex];
            costoSugerido.textContent = 'Costo registrado originalmente en la línea de compra: Bs. ' + Number(option.dataset.costo || 0).toFixed(2) + '. Puedes modificarlo si corresponde.';
            return;
        }

        costoSugerido.textContent = 'Selecciona una linea de compra pendiente para registrar la entrada.';
        return;

        const insumoId = insumoSelect.value;
        const cantidad = parseFloat(cantidadInput.value);
        const unidadId = unidadSelect.value;

        if (!insumoId || !insumosData[insumoId] || !costoEstandar || !cantidad) {
            costoSugerido.textContent = 'Sin costo estándar registrado para este insumo.';
            return;
        }

        const insumo = insumosData[insumoId];
        const unidadBaseId = parseInt(insumo.unidad_id);
        const unidadSeleccionada = unidadId ? parseInt(unidadId) : unidadBaseId;
        const factorHaciaBase = obtenerFactorConversion(unidadSeleccionada, unidadBaseId);
        if (factorHaciaBase === null || factorHaciaBase <= 0) {
            costoSugerido.textContent = 'Esta unidad no tiene conversión automática. Ingresa el costo total manualmente.';
            return;
        }
        const cantidadBase = cantidad * factorHaciaBase;
        const costoCalculadoDirecto = (cantidadBase * costoEstandar).toFixed(2);
        costoInput.value = costoCalculadoDirecto;
        ultimoCostoCalculado = costoCalculadoDirecto;
        costoSugerido.textContent = cantidad + ' ' + (unidadSelect.selectedOptions[0]?.text || insumo.unidad_abrev) +
            ' equivalen a ' + cantidadBase.toFixed(4) + ' ' + insumo.unidad_abrev +
            '. Costo calculado: Bs. ' + costoCalculadoDirecto + '.';
        return;
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
    presentacionSelect.addEventListener('change', function() {
        const opcion = presentacionSelect.selectedOptions[0];
        const presentacion = presentacionesData[presentacionSelect.value];
        insumoSelect.value = opcion?.dataset.insumo || '';
        if (presentacion?.unidad_stock_id) {
            unidadSelect.value = presentacion.unidad_stock_id;
        }
        actualizarUnidadBase();
        actualizarCostoSugerido();
    });
    unidadSelect.addEventListener('change', actualizarConversion);
    cantidadInput.addEventListener('input', actualizarConversion);
    tipoSelect.addEventListener('change', actualizarTipoCampos);
    compraLineaSelect.addEventListener('change', aplicarLineaCompra);


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

    // Asegurar que el flujo coincida con el tipo de movimiento antes de enviar.
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (tipoSelect.value === 'entrada' && !compraLineaSelect.value) {
                e.preventDefault();
                compraLineaSelect.focus();
                compraLineaInfo.textContent = 'Debes seleccionar una linea de compra pendiente para registrar una entrada.';
                compraLineaInfo.classList.remove('text-muted');
                compraLineaInfo.classList.add('text-danger');
                return;
            }
            if (tipoSelect.value === 'salida' && !presentacionSelect.value) {
                e.preventDefault();
                presentacionSelect.focus();
            }
        });
    }

    // Inicializar al cargar
    actualizarUnidadBase();
    actualizarTipoCampos();
    aplicarLineaCompra();

    // Calcular costo inicial si hay valores pre-cargados y es tipo entrada
    if (tipoSelect.value === 'entrada' && insumoSelect.value && cantidadInput.value) {
        actualizarCostoSugerido();
    }
});
</script>
@endsection
