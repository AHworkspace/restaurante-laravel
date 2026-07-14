@extends('layouts.app')

@section('content')
<div class="title-wrapper pt-30 d-flex justify-content-between align-items-center">
    <div class="title mb-30"><h2>{{ $consumidor->nombre_completo }}</h2></div>
    <a class="btn btn-warning" href="{{ route('consumidores.edit', $consumidor) }}">Editar</a>
</div>
<div class="row">
    <div class="col-lg-7"><div class="card mb-3"><div class="card-body">
        <h5>Datos del consumidor</h5>
        <dl class="row mb-0">
            <dt class="col-sm-4">CI</dt><dd class="col-sm-8">{{ $consumidor->ci }}</dd>
            <dt class="col-sm-4">Codigo</dt><dd class="col-sm-8">{{ $consumidor->codigo_unico ?: 'Sin asignar' }}</dd>
            <dt class="col-sm-4">Segmento</dt><dd class="col-sm-8">{{ $consumidor->fuerza?->nombre ?: 'Sin asignar' }}</dd>
            <dt class="col-sm-4">Institucion</dt><dd class="col-sm-8">{{ $consumidor->institucion?->nombre ?: 'Sin asignar' }}</dd>
            <dt class="col-sm-4">Grado</dt><dd class="col-sm-8">{{ $consumidor->grado?->nombre ?: 'Sin asignar' }}</dd>
        </dl>
    </div></div></div>
    <div class="col-lg-5"><div class="card mb-3"><div class="card-body">
        <h5>Estado de cuenta</h5>
        <p class="mb-1">Saldo pendiente</p><h3>Bs {{ number_format($consumidor->saldoPendiente(), 2) }}</h3>
        <p class="mb-1 mt-3">Saldo adelantado</p><h4>Bs {{ number_format($consumidor->saldoAdelantadoDisponible(), 2) }}</h4>
    </div></div></div>
</div>
<div class="card"><div class="card-body">
    <h5>Ultimos consumos</h5>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Fecha</th><th>Receta</th><th>Total</th><th>Pagado</th><th>Restante</th><th>Estado</th></tr></thead>
        <tbody>@forelse($consumidor->consumos->sortByDesc('fecha_consumo')->take(10) as $consumo)
            @php($pagado=$consumo->montoPagado())
            @php($restante=$consumo->saldoPendiente())
            <tr><td>{{ $consumo->fecha_consumo->format('d/m/Y') }}</td><td>{{ $consumo->receta?->nombre }}</td><td>Bs {{ number_format($consumo->total, 2) }}</td><td class="text-success">Bs {{ number_format($pagado, 2) }}</td><td class="fw-bold">Bs {{ number_format($restante, 2) }}</td><td>{{ $restante <= 0 ? 'Pagado' : ($pagado > 0 ? 'Parcial' : 'Pendiente') }}</td></tr>
        @empty <tr><td colspan="6">Sin consumos registrados.</td></tr> @endforelse</tbody>
    </table></div>
</div></div>
@endsection

