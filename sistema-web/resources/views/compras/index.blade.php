@extends('layouts.app')

@section('content')
@php
    $hayFiltroReporte = collect(request()->except('page'))
        ->filter(fn ($valor) => $valor !== null && $valor !== '' && $valor !== 'todos')
        ->isNotEmpty();
@endphp
<div class="title-wrapper pt-30">
    <div class="row">
        <div class="col-md-6"><div class="title mb-30"><h2>Compras</h2></div></div>
        <div class="col-md-6 text-end">
            <a class="main-btn primary-btn btn-hover" href="{{ route('compras.create') }}"><i class="lni lni-plus"></i> Nueva Compra</a>
        </div>
    </div>
</div>

<div class="card-styles">
    <div class="card-style-3 mb-30">
        <div class="card-content">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-danger text-white"><div class="card-body text-center"><h6>Total anulado</h6><h4>Bs. {{ number_format($totalAnulado,2) }}</h4></div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-primary text-white"><div class="card-body text-center"><h6>Total de compras</h6><h4>Bs. {{ number_format($totalGeneral,2) }}</h4></div></div>
                </div>
            </div>

            <form class="card bg-light p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Proveedor</label>
                        <select class="form-select" name="proveedor_id">
                            <option value="">Todos</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->id }}" @selected(request('proveedor_id')==$p->id)>{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Estado</label>
                        <select class="form-select" name="estado">
                            <option value="">Todos</option>
                            @foreach(['pendiente','parcial','cerrada','anulada'] as $e)
                                <option value="{{ $e }}" @selected(request('estado')===$e)>{{ ucfirst($e) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><label>Desde</label><input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}"></div>
                    <div class="col-md-2"><label>Hasta</label><input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}"></div>
                    <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary"><i class="lni lni-search-alt"></i></button></div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            @if($hayFiltroReporte)
                                <th><button type="button" class="btn btn-sm btn-outline-primary reporte-select-all-top" data-target-sector="compras">Seleccionar todos</button></th>
                            @endif
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Proveedor</th>
                            <th>Lineas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compras as $compra)
                            <tr>
                                @if($hayFiltroReporte)
                                    <td><input type="checkbox" class="form-check-input reporte-row-checkbox" data-sector="compras" value="{{ $compra->id }}" data-total="{{ $compra->costo_total }}"></td>
                                @endif
                                <td>{{ $compra->fecha_compra?->format('d/m/Y') }}</td>
                                <td>{{ $compra->numero_documento ?: 'Sin numero' }}</td>
                                <td>{{ $compra->proveedorRel?->nombre ?: $compra->proveedor }}</td>
                                <td>{{ $compra->lineas->count() }}</td>
                                <td>Bs. {{ number_format($compra->costo_total,2) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($compra->estado) }}</span></td>
                                <td><a class="btn btn-info btn-sm" href="{{ route('compras.show',$compra) }}"><i class="lni lni-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $hayFiltroReporte ? 8 : 7 }}">No hay compras registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $compras->links() }}
            @if($hayFiltroReporte)
                @include('reportes._guardar-filtrado', ['sector' => 'compras', 'titulo' => 'Guardar reporte de compras'])
            @endif
        </div>
    </div>
</div>
@endsection
