@extends('layouts.app')

@section('content')
    @php
        $hayFiltroReporte = collect(request()->except('page'))
            ->filter(fn ($valor) => $valor !== null && $valor !== '' && $valor !== 'todos')
            ->isNotEmpty();
    @endphp
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Pagos') }}</h2>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('pagos.create') }}" class="main-btn primary-btn btn-hover">
                    <i class="lni lni-plus"></i> Registrar Pago
                </a>
            </div>
        </div>
    </div>

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                {{-- Resumen estadístico --}}
                @php
                    $queryPagos = \App\Models\Pago::query();
                    if (request('consumidor_id')) $queryPagos->where('consumidor_id', request('consumidor_id'));
                    if (request('tipo_pago')) $queryPagos->where('tipo_pago', request('tipo_pago'));
                    if (request('fecha_desde')) $queryPagos->where('fecha_pago', '>=', request('fecha_desde'));
                    if (request('fecha_hasta')) $queryPagos->where('fecha_pago', '<=', request('fecha_hasta'));

                    $totalEfectivo = (clone $queryPagos)->where('metodo_pago', 'efectivo')->sum('monto');
                    $totalQR = (clone $queryPagos)->where('metodo_pago', 'qr')->sum('monto');
                    $totalTransferencia = (clone $queryPagos)->where('metodo_pago', 'transferencia')->sum('monto');
                    $totalGeneral = $queryPagos->sum('monto');
                @endphp
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6>💰 Efectivo</h6>
                                <h4>Bs. {{ number_format($totalEfectivo, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6>📱 QR</h6>
                                <h4>Bs. {{ number_format($totalQR, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h6>🏦 Transferencia</h6>
                                <h4>Bs. {{ number_format($totalTransferencia, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6>📊 Total General</h6>
                                <h4>Bs. {{ number_format($totalGeneral, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filtros --}}
                <form method="GET" class="mb-4">
                    <div class="card bg-light p-3">
                        <h6 class="mb-3">🔍 Filtros de Búsqueda</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">Buscar Consumidor</label>
                                <input type="text" id="buscar_consumidor_pagos"
                                       class="form-control"
                                       placeholder="Escribe CI o nombre..."
                                       value="{{ request('buscar_consumidor') }}">
                                <input type="hidden" name="consumidor_id" id="consumidor_id_pagos" value="{{ request('consumidor_id') }}">
                                <div id="resultados_consumidor_pagos" class="list-group mt-1" style="display:none; max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000; width: 100%;"></div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Tipo de Pago</label>
                                <select name="tipo_pago" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="consumo_especifico" {{ request('tipo_pago') == 'consumo_especifico' ? 'selected' : '' }}>Consumo Específico</option>
                                    <option value="adelanto" {{ request('tipo_pago') == 'adelanto' ? 'selected' : '' }}>Adelanto</option>
                                    <option value="cuenta_periodo" {{ request('tipo_pago') == 'cuenta_periodo' ? 'selected' : '' }}>Cuenta Período</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Método</label>
                                <select name="metodo_pago" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="efectivo" {{ request('metodo_pago') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                    <option value="qr" {{ request('metodo_pago') == 'qr' ? 'selected' : '' }}>QR</option>
                                    <option value="transferencia" {{ request('metodo_pago') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Fecha Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="lni lni-search-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <a href="{{ route('pagos.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="lni lni-reload"></i> Limpiar Filtros
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Tabla de pagos --}}
                <div class="table-wrapper table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                @if($hayFiltroReporte)
                                    <th><button type="button" class="btn btn-sm btn-outline-primary reporte-select-all-top" data-target-sector="pagos">Seleccionar todos</button></th>
                                @endif
                                <th>ID</th>
                                <th>Consumidor</th>
                                <th>Monto</th>
                                <th>Tipo</th>
                                <th>Método</th>
                                <th>Período</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Referencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr>
                                    @if($hayFiltroReporte)
                                        <td><input type="checkbox" class="form-check-input reporte-row-checkbox" data-sector="pagos" value="{{ $pago->id }}" data-total="{{ $pago->monto }}"></td>
                                    @endif
                                    <td>{{ $pago->id }}</td>
                                    <td>
                                        <strong>{{ $pago->consumidor->nombre_completo }}</strong><br>
                                        <small class="text-muted">{{ $pago->consumidor->ci }}</small>
                                    </td>
                                    <td><strong class="text-success">Bs. {{ number_format($pago->monto, 2) }}</strong></td>
                                    <td>
                                        @if($pago->tipo_pago == 'consumo_especifico')
                                            <span class="badge bg-primary">Consumo Específico</span>
                                        @elseif($pago->tipo_pago == 'adelanto')
                                            <span class="badge bg-warning">Adelanto</span>
                                        @else
                                            <span class="badge bg-info">Cuenta Período</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pago->metodo_pago == 'efectivo')
                                            <span class="badge bg-success">Efectivo</span>
                                        @elseif($pago->metodo_pago == 'qr')
                                            <span class="badge bg-info">QR</span>
                                        @else
                                            <span class="badge bg-secondary">Transferencia</span>
                                        @endif
                                    </td>
                                    <td>{{ $pago->periodo_pagado ?? 'N/A' }}</td>
                                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pago->hora_pago)->format('H:i') }}</td>
                                    <td>{{ $pago->referencia ?? '-' }}</td>
                                    <td>
                                        <div class="action d-flex gap-2">
                                            <a href="{{ route('pagos.show', $pago->id) }}"
                                               class="btn btn-info btn-sm" title="Ver Detalles">
                                                <i class="lni lni-eye"></i>
                                            </a>
                                            <a href="{{ route('consumidores.show', $pago->consumidor_id) }}"
                                               class="btn btn-secondary btn-sm" title="Ver Consumidor">
                                                <i class="lni lni-user"></i>
                                            </a>
                                            <a href="{{ route('consumos.index', ['consumidor_id' => $pago->consumidor_id]) }}"
                                               class="btn btn-primary btn-sm" title="Ver Consumos">
                                                <i class="lni lni-dinner"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $hayFiltroReporte ? 11 : 10 }}" class="text-center">No se encontraron pagos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-3">
                    {{ $pagos->appends(request()->query())->links() }}
                </div>
                @if($hayFiltroReporte)
                    @include('reportes._guardar-filtrado', ['sector' => 'pagos', 'titulo' => 'Guardar reporte de pagos'])
                @endif
            </div>
        </div>
    </div>

    <script>
        // Autocomplete para buscar consumidor en filtros de pagos
        var buscarConsumidorPagos = document.getElementById('buscar_consumidor_pagos');
        var resultadosConsumidorPagos = document.getElementById('resultados_consumidor_pagos');
        var consumidorIdPagos = document.getElementById('consumidor_id_pagos');

        if (buscarConsumidorPagos) {
            buscarConsumidorPagos.addEventListener('input', function() {
                var query = this.value;
                if (query.length >= 2) {
                    fetch('{{ route("consumidores.buscar") }}?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            resultadosConsumidorPagos.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(function(consumidor) {
                                    var item = document.createElement('a');
                                    item.href = '#';
                                    item.className = 'list-group-item list-group-item-action';
                                    item.innerHTML = '<strong>' + consumidor.nombre_completo + '</strong><br>' +
                                                    '<small>CI: ' + consumidor.ci + '</small>';
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        consumidorIdPagos.value = consumidor.id;
                                        buscarConsumidorPagos.value = consumidor.nombre_completo;
                                        resultadosConsumidorPagos.style.display = 'none';
                                    });
                                    resultadosConsumidorPagos.appendChild(item);
                                });
                                resultadosConsumidorPagos.style.display = 'block';
                            } else {
                                resultadosConsumidorPagos.innerHTML = '<div class="list-group-item">No se encontraron resultados</div>';
                                resultadosConsumidorPagos.style.display = 'block';
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    resultadosConsumidorPagos.style.display = 'none';
                    consumidorIdPagos.value = '';
                }
            });
        }

        // Cerrar resultados al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!buscarConsumidorPagos?.contains(e.target) && !resultadosConsumidorPagos?.contains(e.target)) {
                resultadosConsumidorPagos.style.display = 'none';
            }
        });
    </script>
@endsection
