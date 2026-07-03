@extends('layouts.app')

@section('content')
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Registrar Pago') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <form method="GET" action="{{ route('pagos.create') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">📅 Pendientes por fecha</label>
                        <input type="date" class="form-control" name="fecha_pendientes" value="{{ $fechaPendientes ?? now()->toDateString() }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" type="submit">Actualizar</button>
                    </div>
                    <div class="col-md-7 text-end">
                        <small class="text-muted">Selecciona un cliente de esta lista para cargar su cobro sin buscarlo manualmente.</small>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Consumidor</th>
                                <th>Consumos pendientes</th>
                                <th class="text-end">Total pendiente</th>
                                <th>Resumen</th>
                                <th style="width: 150px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($pendientesDelDia ?? collect()) as $pendiente)
                                <tr>
                                    <td>
                                        <strong>{{ $pendiente['consumidor']->nombre_completo ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">
                                            CI: {{ $pendiente['consumidor']->ci ?? 'N/A' }}
                                            | {{ $pendiente['consumidor']->grado->nombre ?? 'N/A' }}
                                            | {{ $pendiente['consumidor']->fuerza->nombre ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td>{{ $pendiente['cantidad_consumos'] }}</td>
                                    <td class="text-end"><strong>Bs. {{ number_format($pendiente['total_pendiente'], 2) }}</strong></td>
                                    <td><small>{{ $pendiente['resumen_platos'] ?: 'Sin resumen' }}</small></td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-primary btn-sm w-100 btn-usar-pendiente"
                                                data-id="{{ $pendiente['consumidor']->id }}"
                                                data-nombre="{{ $pendiente['consumidor']->nombre_completo }}"
                                                data-ci="{{ $pendiente['consumidor']->ci }}"
                                                data-grado="{{ $pendiente['consumidor']->grado->nombre ?? 'N/A' }}"
                                                data-fuerza="{{ $pendiente['consumidor']->fuerza->nombre ?? 'N/A' }}"
                                                data-pendiente="{{ number_format($pendiente['total_pendiente'], 2, '.', '') }}"
                                                data-adelantado="{{ number_format($pendiente['consumidor']->saldoAdelantadoDisponible(), 2, '.', '') }}">
                                            Usar en cobro
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay consumos pendientes en la fecha seleccionada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-style-3 mb-30">
            <div class="card-content">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pagos.store') }}" method="POST" id="formPago">
                    @csrf

                    {{-- Consumidor --}}
                    <div class="mb-3">
                        <label class="form-label">👤 Consumidor *</label>
                        <input type="text"
                               class="form-control"
                               id="buscar_consumidor"
                               placeholder="Escribe CI o nombre para buscar..."
                               autocomplete="off">
                        <input type="hidden" name="consumidor_id" id="consumidor_id_selected" required>
                        <div id="resultados_consumidores" class="list-group mt-2" style="display:none; max-height: 300px; overflow-y: auto;"></div>
                        <div id="info_consumidor" class="card bg-light mt-2" style="display:none;">
                            <div class="card-body">
                                <h6 id="consumidor_nombre" class="mb-1"></h6>
                                <small id="consumidor_detalles" class="text-muted d-block mb-2"></small>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-danger">💰 Pendiente: Bs. <span id="consumidor_pendiente">0.00</span></small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-success">💵 Adelantado disponible: Bs. <span id="consumidor_adelantado">0.00</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tipo de pago --}}
                    <div class="mb-3">
                        <label class="form-label">💰 Tipo de Pago *</label>
                        <select name="tipo_pago" id="tipo_pago" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="consumo_especifico">Pago de Consumos Específicos</option>
                            <option value="adelanto">Adelanto (Pago por Anticipado)</option>
                            <option value="cuenta_periodo">Pago de Cuenta Período (Día/Semana/Mes)</option>
                        </select>
                    </div>

                    {{-- Monto --}}
                    <div class="mb-3">
                        <label class="form-label">💵 Monto (Bs.) *</label>
                        <input type="number" name="monto" class="form-control"
                               step="0.01" min="0.01" required
                               placeholder="0.00">
                    </div>

                    {{-- Método de pago --}}
                    <div class="mb-3">
                        <label class="form-label">💳 Método de Pago *</label>
                        <select name="metodo_pago" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="qr">QR</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    {{-- Período pagado (solo para cuenta_periodo) --}}
                    <div class="mb-3" id="periodo_pagado_group" style="display:none;">
                        <label class="form-label">📅 Período Pagado</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <select class="form-select" id="periodo_tipo_visual">
                                    <option value="dia">Día</option>
                                    <option value="rango">Rango de fechas</option>
                                    <option value="mes">Mes</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="date" id="periodo_dia" class="form-control">
                                <input type="date" id="periodo_semana" class="form-control" style="display:none;" title="Fecha inicial">
                                <input type="month" id="periodo_mes" class="form-control" style="display:none;">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary w-100" id="agregar_periodo">Agregar</button>
                                <input type="hidden" id="periodo_dia_hasta">
                                <input type="hidden" id="periodo_mes_hasta">
                            </div>
                        </div>
                        <div class="row g-2 mt-1" id="rango_fecha_final" style="display:none;">
                            <div class="col-md-5 offset-md-4">
                                <input type="date" id="periodo_semana_hasta" class="form-control" title="Fecha final">
                            </div>
                        </div>
                        <input type="hidden" name="periodo_pagado" id="periodo_pagado_hidden">
                        <div id="periodos_seleccionados" class="d-flex flex-wrap gap-2 mt-3"></div>
                        <small class="text-muted d-block mt-2">Puedes agregar uno o varios días, semanas o meses.</small>
                        <div id="detalle_periodo" class="border rounded p-3 mt-3" style="background-color:#f8f9fa;">
                            <p class="text-muted mb-0">Selecciona un cliente y un período para ver sus consumos.</p>
                        </div>
                    </div>

                    {{-- Consumos pendientes (solo para consumo_especifico) --}}
                    <div class="mb-3" id="consumos_pendientes_group" style="display:none;">
                        <label class="form-label">🍽️ Seleccionar Consumos a Pagar</label>
                        <small class="text-muted d-block mb-2">Marca los consumos que deseas pagar con este monto</small>
                        <div id="lista_consumos_pendientes" class="border rounded p-3" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                            <p class="text-muted mb-0">Selecciona un consumidor primero</p>
                        </div>
                        <small class="text-info d-block mt-2">
                            💡 El monto del pago se distribuirá automáticamente entre los consumos seleccionados
                        </small>
                    </div>

                    {{-- Fecha y hora --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Pago</label>
                            <input type="date" name="fecha_pago" class="form-control"
                                   value="{{ old('fecha_pago', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hora de Pago</label>
                            <input type="time" name="hora_pago" class="form-control"
                                   value="{{ old('hora_pago', now()->format('H:i')) }}">
                        </div>
                    </div>

                    {{-- Referencia (para QR/transferencia) --}}
                    <div class="mb-3" id="referencia_group">
                        <label class="form-label">🔖 Referencia (opcional)</label>
                        <input type="text" name="referencia" class="form-control"
                               placeholder="Número de referencia para QR o transferencia"
                               maxlength="100">
                    </div>

                    {{-- Observaciones --}}
                    <div class="mb-3">
                        <label class="form-label">📝 Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"
                                  placeholder="Notas adicionales sobre el pago...">{{ old('observaciones') }}</textarea>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">Registrar Pago</button>
                        <a href="{{ route('pagos.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Autocomplete de consumidores
        var buscarConsumidor = document.getElementById('buscar_consumidor');
        var resultadosConsumidores = document.getElementById('resultados_consumidores');
        var consumidorIdSelected = document.getElementById('consumidor_id_selected');
        var infoConsumidor = document.getElementById('info_consumidor');
        var consumosPendientes = null;

        if (buscarConsumidor) {
            buscarConsumidor.addEventListener('input', function() {
                var query = this.value;
                if (query.length >= 2) {
                    fetch('{{ route("consumidores.buscar") }}?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            resultadosConsumidores.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(function(consumidor) {
                                    var item = document.createElement('a');
                                    item.href = '#';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.innerHTML = '<strong>' + consumidor.nombre_completo + '</strong><br>' +
                                                    '<small>CI: ' + consumidor.ci + ' | Pendiente: Bs. ' + consumidor.pendiente + '</small>';
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        seleccionarConsumidor(consumidor);
                                    });
                                    resultadosConsumidores.appendChild(item);
                                });
                                resultadosConsumidores.style.display = 'block';
                            } else {
                                resultadosConsumidores.innerHTML = '<div class="list-group-item">No se encontraron resultados</div>';
                                resultadosConsumidores.style.display = 'block';
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    resultadosConsumidores.style.display = 'none';
                }
            });
        }

        // Selección rápida desde pendientes del día
        document.querySelectorAll('.btn-usar-pendiente').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var consumidor = {
                    id: this.dataset.id,
                    nombre_completo: this.dataset.nombre,
                    ci: this.dataset.ci,
                    grado: this.dataset.grado,
                    fuerza: this.dataset.fuerza,
                    pendiente: this.dataset.pendiente,
                    adelantado: this.dataset.adelantado
                };
                seleccionarConsumidor(consumidor);
                var tipoPago = document.getElementById('tipo_pago');
                tipoPago.value = 'consumo_especifico';
                tipoPago.dispatchEvent(new Event('change'));
                document.querySelector('input[name="monto"]').value = consumidor.pendiente || '';
                window.scrollTo({ top: document.getElementById('formPago').offsetTop - 30, behavior: 'smooth' });
            });
        });

        function seleccionarConsumidor(consumidor) {
            consumidorIdSelected.value = consumidor.id;
            buscarConsumidor.value = consumidor.nombre_completo;
            document.getElementById('consumidor_nombre').textContent = consumidor.nombre_completo;
            document.getElementById('consumidor_detalles').textContent = 'CI: ' + consumidor.ci + ' | ' + consumidor.grado + ' | ' + consumidor.fuerza;
            document.getElementById('consumidor_pendiente').textContent = consumidor.pendiente;

            if (consumidor.adelantado !== undefined) {
                document.getElementById('consumidor_adelantado').textContent = consumidor.adelantado || '0.00';
            } else {
                // Cargar información adicional del consumidor (incluyendo adelantado)
                fetch('{{ route("consumidores.buscar") }}?q=' + encodeURIComponent(consumidor.ci))
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0 && data[0].id == consumidor.id) {
                            document.getElementById('consumidor_adelantado').textContent = data[0].adelantado || '0.00';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            resultadosConsumidores.style.display = 'none';
            infoConsumidor.style.display = 'block';

            // Cargar consumos pendientes si el tipo de pago es consumo_especifico
            if (document.getElementById('tipo_pago').value === 'consumo_especifico') {
                cargarConsumosPendientes(consumidor.id);
            } else if (document.getElementById('tipo_pago').value === 'cuenta_periodo') {
                cargarConsumosPeriodo();
            }
        }

        // Mostrar/ocultar campos según tipo de pago
        document.getElementById('tipo_pago').addEventListener('change', function() {
            var tipoPago = this.value;
            var periodoGroup = document.getElementById('periodo_pagado_group');
            var consumosGroup = document.getElementById('consumos_pendientes_group');

            if (tipoPago === 'cuenta_periodo') {
                periodoGroup.style.display = 'block';
                consumosGroup.style.display = 'none';
                cargarConsumosPeriodo();
            } else if (tipoPago === 'consumo_especifico') {
                periodoGroup.style.display = 'none';
                consumosGroup.style.display = 'block';
                if (consumidorIdSelected.value) {
                    cargarConsumosPendientes(consumidorIdSelected.value);
                }
            } else {
                periodoGroup.style.display = 'none';
                consumosGroup.style.display = 'none';
            }
        });

        // Constructor visual del período pagado
        var periodoTipoVisual = document.getElementById('periodo_tipo_visual');
        var periodoDia = document.getElementById('periodo_dia');
        var periodoSemana = document.getElementById('periodo_semana');
        var periodoMes = document.getElementById('periodo_mes');
        var periodoDiaHasta = document.getElementById('periodo_dia_hasta');
        var periodoSemanaHasta = document.getElementById('periodo_semana_hasta');
        var periodoMesHasta = document.getElementById('periodo_mes_hasta');
        var periodoHidden = document.getElementById('periodo_pagado_hidden');
        var periodosSeleccionados = [];

        function actualizarInputPeriodoVisible() {
            var tipo = periodoTipoVisual.value;
            periodoDia.style.display = tipo === 'dia' ? 'block' : 'none';
            periodoSemana.style.display = tipo === 'semana' ? 'block' : 'none';
            periodoMes.style.display = tipo === 'mes' ? 'block' : 'none';
            periodoDiaHasta.style.display = tipo === 'dia' ? 'block' : 'none';
            periodoSemanaHasta.style.display = tipo === 'semana' ? 'block' : 'none';
            periodoMesHasta.style.display = tipo === 'mes' ? 'block' : 'none';
            actualizarPeriodoHidden();
        }

        function actualizarPeriodoHidden() {
            var tipo = periodoTipoVisual.value;
            var desde = tipo === 'dia' ? periodoDia.value : (tipo === 'semana' ? periodoSemana.value : periodoMes.value);
            var hastaInput = tipo === 'dia' ? periodoDiaHasta : (tipo === 'semana' ? periodoSemanaHasta : periodoMesHasta);
            var hasta = hastaInput.value || desde;
            periodoHidden.value = desde ? tipo + ':' + desde + ':' + hasta : '';
            cargarConsumosPeriodo();
        }

        function cargarConsumosPeriodo() {
            var detalle = document.getElementById('detalle_periodo');
            var tipo = periodoTipoVisual.value;
            var desde = tipo === 'dia' ? periodoDia.value : (tipo === 'semana' ? periodoSemana.value : periodoMes.value);
            var hasta = tipo === 'dia' ? periodoDiaHasta.value : (tipo === 'semana' ? periodoSemanaHasta.value : periodoMesHasta.value);
            if (!consumidorIdSelected.value || !desde || document.getElementById('tipo_pago').value !== 'cuenta_periodo') {
                detalle.innerHTML = '<p class="text-muted mb-0">Selecciona un cliente y un período para ver sus consumos.</p>';
                return;
            }
            detalle.innerHTML = '<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
            var url = '{{ route("pagos.consumos-periodo") }}?consumidor_id=' + encodeURIComponent(consumidorIdSelected.value)
                + '&tipo=' + encodeURIComponent(tipo) + '&desde=' + encodeURIComponent(desde)
                + '&hasta=' + encodeURIComponent(hasta || desde);
            fetch(url).then(function(response) {
                if (!response.ok) throw new Error('No se pudo consultar el período.');
                return response.json();
            }).then(function(data) {
                periodoHidden.value = data.periodo;
                if (!data.consumos.length) {
                    detalle.innerHTML = '<div class="alert alert-info mb-0">No hay consumos pendientes en este período.</div>';
                    return;
                }
                var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                html += '<thead><tr><th>Fecha</th><th>Hora</th><th>Plato</th><th>Tipo</th><th>Cant.</th><th class="text-end">Total</th><th class="text-end">Pagado</th><th class="text-end">Restante</th></tr></thead><tbody>';
                data.consumos.forEach(function(consumo) {
                    html += '<tr><td>' + consumo.fecha + '</td><td>' + consumo.hora + '</td><td>' + consumo.plato + '</td><td><small>' + consumo.tipo + '</small></td><td>' + consumo.cantidad + '</td>';
                    html += '<td class="text-end">Bs. ' + Number(consumo.total).toFixed(2) + '</td><td class="text-end text-success">Bs. ' + Number(consumo.pagado).toFixed(2) + '</td>';
                    html += '<td class="text-end"><strong>Bs. ' + Number(consumo.saldo).toFixed(2) + '</strong><br><small>' + consumo.estado + '</small></td></tr>';
                });
                html += '</tbody><tfoot><tr class="table-info"><td colspan="7" class="text-end"><strong>Total pendiente del período:</strong></td><td class="text-end"><strong>Bs. ' + Number(data.total_pendiente).toFixed(2) + '</strong></td></tr></tfoot></table></div>';
                detalle.innerHTML = html;
                document.querySelector('input[name="monto"]').value = Number(data.total_pendiente).toFixed(2);
            }).catch(function() {
                detalle.innerHTML = '<div class="alert alert-danger mb-0">No se pudo cargar el detalle del período.</div>';
            });
        }

        function periodoElegido() {
            var tipo = periodoTipoVisual.value;
            if (tipo === 'rango') {
                if (!periodoSemana.value || !periodoSemanaHasta.value) return '';
                if (periodoSemana.value > periodoSemanaHasta.value) {
                    alert('La fecha inicial no puede ser posterior a la fecha final.');
                    return '';
                }
                return periodoSemana.value + '~' + periodoSemanaHasta.value;
            }
            return tipo === 'dia' ? periodoDia.value : periodoMes.value;
        }

        function actualizarInputPeriodoVisible() {
            var tipo = periodoTipoVisual.value;
            periodoDia.style.display = tipo === 'dia' ? 'block' : 'none';
            periodoSemana.style.display = tipo === 'rango' ? 'block' : 'none';
            periodoMes.style.display = tipo === 'mes' ? 'block' : 'none';
            document.getElementById('rango_fecha_final').style.display = tipo === 'rango' ? 'flex' : 'none';
            periodosSeleccionados = [];
            renderizarPeriodosSeleccionados();
        }

        function renderizarPeriodosSeleccionados() {
            var contenedor = document.getElementById('periodos_seleccionados');
            contenedor.innerHTML = '';
            periodosSeleccionados.forEach(function(valor, indice) {
                var etiqueta = document.createElement('span');
                etiqueta.className = 'badge bg-primary d-inline-flex align-items-center gap-2 p-2';
                var texto = periodoTipoVisual.value === 'rango' ? valor.replace('~', ' al ') : valor;
                etiqueta.innerHTML = texto + ' <button type="button" class="btn-close btn-close-white quitar-periodo" data-indice="' + indice + '" aria-label="Quitar"></button>';
                contenedor.appendChild(etiqueta);
            });
            periodoHidden.value = periodosSeleccionados.length
                ? 'seleccion:' + periodoTipoVisual.value + ':' + periodosSeleccionados.join(',')
                : '';
            cargarConsumosPeriodo();
        }

        function cargarConsumosPeriodo() {
            var detalle = document.getElementById('detalle_periodo');
            if (!consumidorIdSelected.value || !periodosSeleccionados.length || document.getElementById('tipo_pago').value !== 'cuenta_periodo') {
                detalle.innerHTML = '<p class="text-muted mb-0">Selecciona un cliente y agrega uno o varios periodos.</p>';
                return;
            }
            detalle.innerHTML = '<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
            var url = '{{ route("pagos.consumos-periodo") }}?consumidor_id=' + encodeURIComponent(consumidorIdSelected.value)
                + '&tipo=' + encodeURIComponent(periodoTipoVisual.value)
                + '&valores=' + encodeURIComponent(periodosSeleccionados.join(','));
            fetch(url).then(function(response) {
                if (!response.ok) throw new Error('Consulta invalida.');
                return response.json();
            }).then(function(data) {
                periodoHidden.value = data.periodo;
                if (!data.consumos.length) {
                    detalle.innerHTML = '<div class="alert alert-info mb-0">No hay consumos pendientes en la seleccion.</div>';
                    document.querySelector('input[name="monto"]').value = '';
                    return;
                }
                var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Fecha</th><th>Hora</th><th>Plato</th><th>Tipo</th><th>Cant.</th><th class="text-end">Total</th><th class="text-end">Pagado</th><th class="text-end">Restante</th></tr></thead><tbody>';
                data.consumos.forEach(function(consumo) {
                    html += '<tr><td>' + consumo.fecha + '</td><td>' + consumo.hora + '</td><td>' + consumo.plato + '</td><td><small>' + consumo.tipo + '</small></td><td>' + consumo.cantidad + '</td>';
                    html += '<td class="text-end">Bs. ' + Number(consumo.total).toFixed(2) + '</td><td class="text-end text-success">Bs. ' + Number(consumo.pagado).toFixed(2) + '</td><td class="text-end"><strong>Bs. ' + Number(consumo.saldo).toFixed(2) + '</strong><br><small>' + consumo.estado + '</small></td></tr>';
                });
                html += '</tbody><tfoot><tr class="table-info"><td colspan="7" class="text-end"><strong>Total pendiente:</strong></td><td class="text-end"><strong>Bs. ' + Number(data.total_pendiente).toFixed(2) + '</strong></td></tr></tfoot></table></div>';
                detalle.innerHTML = html;
                document.querySelector('input[name="monto"]').value = Number(data.total_pendiente).toFixed(2);
            }).catch(function() {
                detalle.innerHTML = '<div class="alert alert-danger mb-0">No se pudo cargar el detalle seleccionado.</div>';
            });
        }

        periodoTipoVisual.addEventListener('change', actualizarInputPeriodoVisible);
        document.getElementById('agregar_periodo').addEventListener('click', function() {
            var valor = periodoElegido();
            if (valor && periodosSeleccionados.indexOf(valor) === -1) {
                periodosSeleccionados.push(valor);
                periodosSeleccionados.sort();
                renderizarPeriodosSeleccionados();
            }
        });
        document.getElementById('periodos_seleccionados').addEventListener('click', function(event) {
            var boton = event.target.closest('.quitar-periodo');
            if (!boton) return;
            periodosSeleccionados.splice(Number(boton.dataset.indice), 1);
            renderizarPeriodosSeleccionados();
        });
        [periodoDia, periodoSemana, periodoMes, periodoDiaHasta, periodoSemanaHasta, periodoMesHasta]
            .forEach(function(input) { input.addEventListener('change', actualizarPeriodoHidden); });
        periodoDia.value = '{{ now()->toDateString() }}';
        periodoDiaHasta.value = periodoDia.value;
        periodoSemana.value = '{{ now()->startOfWeek()->toDateString() }}';
        periodoSemanaHasta.value = '{{ now()->endOfWeek()->toDateString() }}';
        periodoMes.value = '{{ now()->format('Y-m') }}';
        periodoMesHasta.value = periodoMes.value;
        actualizarInputPeriodoVisible();

        function cargarConsumosPendientes(consumidorId) {
            var listaDiv = document.getElementById('lista_consumos_pendientes');
            listaDiv.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

            fetch('{{ route("consumos.pendientes", ":id") }}'.replace(':id', consumidorId))
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        listaDiv.innerHTML = '<div class="alert alert-info mb-0"><p class="mb-0">No hay consumos pendientes para este consumidor.</p></div>';
                        return;
                    }

                    var html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                    html += '<thead><tr><th><input type="checkbox" id="select_all_consumos"></th><th>Fecha</th><th>Hora</th><th>Plato</th><th>Tipo</th><th>Cant.</th><th class="text-end">Total</th><th class="text-end">Pagado</th><th class="text-end">Restante</th></tr></thead>';
                    html += '<tbody>';

                    var totalPendiente = 0;
                    data.forEach(function(consumo) {
                        totalPendiente += parseFloat(consumo.total_raw);
                        html += '<tr>';
                        html += '<td><input type="checkbox" class="consumo-checkbox" name="consumos_ids[]" value="' + consumo.id + '" data-total="' + consumo.total_raw + '"></td>';
                        html += '<td>' + consumo.fecha + '</td>';
                        html += '<td>' + consumo.hora + '</td>';
                        html += '<td>' + consumo.receta_nombre + '</td>';
                        html += '<td><small>' + consumo.tipo_comida + '</small></td>';
                        html += '<td>' + consumo.cantidad + '</td>';
                        html += '<td class="text-end">Bs. ' + Number(consumo.total_original).toFixed(2) + '</td>';
                        html += '<td class="text-end text-success">Bs. ' + Number(consumo.monto_pagado).toFixed(2) + '</td>';
                        html += '<td class="text-end"><strong>Bs. ' + Number(consumo.saldo).toFixed(2) + '</strong><br><small>' + consumo.estado_pago + '</small></td>';
                        html += '</tr>';
                    });

                    html += '</tbody>';
                    html += '<tfoot><tr class="table-info"><td colspan="6" class="text-end"><strong>Total Seleccionado:</strong></td><td class="text-end"><strong id="total_seleccionado">Bs. 0.00</strong></td></tr></tfoot>';
                    html += '</table></div>';

                    listaDiv.innerHTML = html;

                    // Agregar event listeners
                    document.querySelectorAll('.consumo-checkbox').forEach(function(checkbox) {
                        checkbox.addEventListener('change', calcularTotalSeleccionado);
                    });

                    document.getElementById('select_all_consumos').addEventListener('change', function() {
                        var isChecked = this.checked;
                        document.querySelectorAll('.consumo-checkbox').forEach(function(cb) {
                            cb.checked = isChecked;
                        });
                        calcularTotalSeleccionado();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    listaDiv.innerHTML = '<div class="alert alert-danger mb-0"><p class="mb-0">Error al cargar los consumos pendientes.</p></div>';
                });
        }

        function calcularTotalSeleccionado() {
            var total = 0;
            document.querySelectorAll('.consumo-checkbox:checked').forEach(function(checkbox) {
                total += parseFloat(checkbox.getAttribute('data-total'));
            });
            document.getElementById('total_seleccionado').textContent = 'Bs. ' + total.toFixed(2);
            if (document.getElementById('tipo_pago').value === 'consumo_especifico' && total > 0) {
                document.querySelector('input[name="monto"]').value = total.toFixed(2);
            }
        }
    </script>
@endsection
