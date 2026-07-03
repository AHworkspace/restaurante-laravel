@extends('layouts.app-cliente')
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Mis consumos</h2></div></div>
<div class="card-style-3 mb-30"><div class="card-content"><div class="table-responsive"><table class="table">
<thead><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Total</th><th>Estado</th></tr></thead>
<tbody>@forelse($consumos as $c)<tr><td>{{ $c->fecha_consumo->format('d/m/Y') }}</td><td>{{ $c->producto_nombre }}</td><td>{{ $c->cantidad }}</td><td>Bs {{ number_format($c->total,2) }}</td><td><span class="badge {{ $c->estado_pago==='pagado'?'bg-success':($c->estado_pago==='parcial'?'bg-warning text-dark':'bg-secondary') }}">{{ ucfirst($c->estado_pago) }}</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No tienes consumos registrados.</td></tr>@endforelse</tbody>
</table></div></div></div>
@endsection
