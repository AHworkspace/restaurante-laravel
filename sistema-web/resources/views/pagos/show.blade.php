@extends('layouts.app')

@section('content')
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Detalle del Pago') }}</h2>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
                    <i class="lni lni-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                {{-- Información del Pago --}}
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h3>Pago #{{ $pago->id }}</h3>
                        <p class="text-muted">Registrado el {{ $pago->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Monto Total</h6>
                                <h3>Bs. {{ number_format($pago->monto, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Información del Consumidor</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Nombre:</strong> {{ $pago->consumidor->nombre_completo }}</p>
                                <p><strong>CI:</strong> {{ $pago->consumidor->ci }}</p>
                                <p><strong>Código:</strong> <code>{{ $pago->consumidor->codigo_unico }}</code></p>
                                <p><strong>Segmento:</strong> {{ $pago->consumidor->fuerza->nombre ?? 'N/A' }}</p>
                                <p><strong>Institución:</strong> {{ $pago->consumidor->institucion->nombre ?? 'N/A' }}</p>
                                <p><strong>Grado:</strong> {{ $pago->consumidor->grado->nombre ?? 'N/A' }}</p>
                                <a href="{{ route('consumidores.show', $pago->consumidor_id) }}" class="btn btn-info btn-sm">
                                    <i class="lni lni-user"></i> Ver Consumidor
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Detalles del Pago</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Tipo de Pago:</strong>
                                    @if($pago->tipo_pago == 'consumo_especifico')
                                        <span class="badge bg-primary">Consumo Específico</span>
                                    @elseif($pago->tipo_pago == 'adelanto')
                                        <span class="badge bg-warning">Adelanto</span>
                                    @else
                                        <span class="badge bg-info">Cuenta Período</span>
                                    @endif
                                </p>
                                <p><strong>Método de Pago:</strong>
                                    @if($pago->metodo_pago == 'efectivo')
                                        <span class="badge bg-success">💰 Efectivo</span>
                                    @elseif($pago->metodo_pago == 'qr')
                                        <span class="badge bg-info">📱 QR</span>
                                    @else
                                        <span class="badge bg-secondary">🏦 Transferencia</span>
                                    @endif
                                </p>
                                <p><strong>Fecha de Pago:</strong> {{ $pago->fecha_pago->format('d/m/Y') }}</p>
                                <p><strong>Hora de Pago:</strong> {{ \Carbon\Carbon::parse($pago->hora_pago)->format('H:i') }}</p>
                                @if($pago->periodo_pagado)
                                    <p><strong>Período Pagado:</strong> {{ $pago->periodo_pagado }}</p>
                                @endif
                                @if($pago->referencia)
                                    <p><strong>Referencia:</strong> {{ $pago->referencia }}</p>
                                @endif
                                @if($pago->observaciones)
                                    <p><strong>Observaciones:</strong> {{ $pago->observaciones }}</p>
                                @endif
                                <p><strong>Registrado por:</strong> {{ $pago->usuarioRegistro->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

    @php
        // Consumos asociados: en algunos cobros descontados desde saldo adelantado
        // el pago "normal" puede no tener filas en `pagos_consumos`.
        // Para que el detalle no quede vacío, hacemos fallback visual.
        $consumosParaMostrar = $pago->consumos ?? collect();

        if ($consumosParaMostrar->count() === 0 && in_array($pago->tipo_pago, ['consumo_especifico', 'cuenta_periodo'], true)) {
            $periodo = $pago->periodo_pagado;

            $query = \App\Models\Consumo::query()
                ->where('consumidor_id', $pago->consumidor_id)
                ->whereIn('id', function ($q) use ($pago) {
                    $q->select('pagos_consumos.consumo_id')
                        ->from('pagos_consumos')
                        ->join('pagos', 'pagos_consumos.pago_id', '=', 'pagos.id')
                        ->where('pagos.consumidor_id', $pago->consumidor_id)
                        ->where('pagos.tipo_pago', 'adelanto');
                })
                ->with(['receta', 'tipoComida']);

            if ($periodo) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo)) {
                    $query->whereDate('fecha_consumo', $periodo);
                } elseif (preg_match('/^\d{4}-\d{2}$/', $periodo)) {
                    $query->whereYear('fecha_consumo', substr($periodo, 0, 4))
                        ->whereMonth('fecha_consumo', substr($periodo, 5, 2));
                } elseif (preg_match('/^\d{4}-\d{2}-semana\d+$/', $periodo)) {
                    preg_match('/^(\d{4})-(\d{2})-semana(\d+)$/', $periodo, $matches);
                    $anio = (int) $matches[1];
                    $mes = (int) $matches[2];
                    $numeroSemana = (int) $matches[3];

                    $primerDiaMes = \Carbon\Carbon::create($anio, $mes, 1);
                    $primerDiaSemana = $primerDiaMes->copy()->addWeeks($numeroSemana - 1)->startOfWeek();
                    $ultimoDiaSemana = $primerDiaSemana->copy()->endOfWeek();

                    $query->whereBetween('fecha_consumo', [
                        $primerDiaSemana->toDateString(),
                        $ultimoDiaSemana->toDateString()
                    ]);
                }
            }

            $consumosParaMostrar = $query->get();
        }
    @endphp

                {{-- Consumos asociados --}}
                @if($consumosParaMostrar->count() > 0)
                    @php
                        $usaSaldoAdelantadoVisual = ($pago->consumos->count() === 0)
                            && in_array($pago->tipo_pago, ['consumo_especifico', 'cuenta_periodo'], true);

                        $adelantoDescontadoTotal = 0;
                        $saldoAdelantadoActual = $pago->consumidor->saldoAdelantadoDisponible();
                        $idsConsumos = $consumosParaMostrar->pluck('id')->all();

                        if ($usaSaldoAdelantadoVisual && !empty($idsConsumos)) {
                            $adelantoDescontadoTotal = \DB::table('pagos_consumos')
                                ->join('pagos', 'pagos_consumos.pago_id', '=', 'pagos.id')
                                ->where('pagos.consumidor_id', $pago->consumidor_id)
                                ->where('pagos.tipo_pago', 'adelanto')
                                ->whereIn('pagos_consumos.consumo_id', $idsConsumos)
                                ->sum('pagos_consumos.monto_aplicado');
                        }

                        $saldoAdelantadoAnterior = (float) $saldoAdelantadoActual + (float) $adelantoDescontadoTotal;
                    @endphp

                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Consumos Pagados ({{ $consumosParaMostrar->count() }})</h6>
                        </div>
                        <div class="card-body">
                            @if($usaSaldoAdelantadoVisual && (float)$adelantoDescontadoTotal > 0)
                                <div class="alert alert-warning mb-3">
                                    <strong>Descontado de saldo adelantado (A favor):</strong>
                                    Se aplicó Bs. {{ number_format((float)$adelantoDescontadoTotal, 2) }} del saldo del cliente.
                                    Saldo antes: Bs. {{ number_format($saldoAdelantadoAnterior, 2) }},
                                    saldo actual: Bs. {{ number_format((float)$saldoAdelantadoActual, 2) }}.
                                </div>
                            @endif

                            <div class="table-wrapper table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Plato</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                            <th>Total</th>
                                            <th>Monto Aplicado</th>
                                            <th>Pagado Acumulado</th>
                                            <th>Restante</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($consumosParaMostrar as $consumo)
                                            <tr>
                                                <td>{{ $consumo->id }}</td>
                                                <td>{{ $consumo->fecha_consumo->format('d/m/Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($consumo->hora_consumo)->format('H:i') }}</td>
                                                <td>{{ $consumo->producto_nombre }}</td>
                                                <td>
                                                    @if($consumo->tipoComida)
                                                        <span class="badge bg-primary">{{ $consumo->tipoComida->nombre }}</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $consumo->cantidad }}</td>
                                                <td><strong>Bs. {{ number_format($consumo->total, 2) }}</strong></td>
                                                @php
                                                    $montoPivot = (float) (optional($consumo->pivot)->monto_aplicado ?? 0);
                                                    // Si el pago no tiene monto aplicado directo (porque fue cubierto con saldo adelantado),
                                                    // mostramos cuánto se aplicó desde pagos tipo 'adelanto'.
                                                    $montoAdelantoAplicado = 0;
                                                    if ($montoPivot <= 0) {
                                                        $montoAdelantoAplicado = \DB::table('pagos_consumos')
                                                            ->join('pagos', 'pagos_consumos.pago_id', '=', 'pagos.id')
                                                            ->where('pagos_consumos.consumo_id', $consumo->id)
                                                            ->where('pagos.tipo_pago', 'adelanto')
                                                            ->sum('pagos_consumos.monto_aplicado');
                                                    }
                                                    $montoParaMostrar = $montoPivot > 0 ? $montoPivot : $montoAdelantoAplicado;
                                                    $pagadoAcumulado = (float) $consumo->pagos()->sum('pagos_consumos.monto_aplicado');
                                                    $saldoConsumo = max(0, (float) $consumo->total - $pagadoAcumulado);
                                                @endphp
                                                <td>
                                                    <strong class="text-success">Bs. {{ number_format($montoParaMostrar, 2) }}</strong>
                                                    @if($montoPivot <= 0 && (float)$montoAdelantoAplicado > 0)
                                                        <span class="badge bg-warning text-dark ms-1">Del saldo adelantado</span>
                                                    @endif
                                                </td>
                                                <td class="text-success">Bs. {{ number_format($pagadoAcumulado, 2) }}</td>
                                                <td><strong>Bs. {{ number_format($saldoConsumo, 2) }}</strong></td>
                                                <td>
                                                    <a href="{{ route('consumos.show', $consumo->id) }}"
                                                       class="btn btn-info btn-sm">
                                                        <i class="lni lni-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="7" class="text-end">Monto de este pago:</th>
                                            <th class="text-success">Bs. {{ number_format($pago->monto, 2) }}</th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Si es adelanto, mostrar saldo disponible --}}
                @if($pago->tipo_pago == 'adelanto')
                    <div class="alert alert-info">
                        <h6><i class="lni lni-information"></i> Información de Adelanto</h6>
                        <p class="mb-0">
                            Este pago es un adelanto de Bs. {{ number_format($pago->monto, 2) }}.
                            @php
                                $saldoDisponible = $pago->consumidor->saldoAdelantadoDisponible();
                            @endphp
                            Saldo disponible actual: <strong>Bs. {{ number_format($saldoDisponible, 2) }}</strong>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
