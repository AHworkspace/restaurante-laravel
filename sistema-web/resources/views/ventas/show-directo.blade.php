@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Venta de {{ $venta->producto_nombre }}</h2></div></div>
<div class="card-style-3 mb-30"><div class="card-content"><div class="row g-3">
<div class="col-md-3"><strong>Producto</strong><p>{{ $venta->producto_nombre }}</p></div><div class="col-md-2"><strong>Cantidad</strong><p>{{ $venta->cantidad }}</p></div><div class="col-md-2"><strong>Precio unitario</strong><p>Bs {{ number_format($venta->precio,2) }}</p></div><div class="col-md-2"><strong>Total</strong><p>Bs {{ number_format($venta->total,2) }}</p></div><div class="col-md-3"><strong>Cliente</strong><p>{{ $venta->consumidor?->nombre_completo ?: 'Venta pública' }}</p></div>
</div><p class="text-muted mt-3">Descuento de inventario: {{ number_format($venta->cantidad*(float)$venta->insumo->cantidad_base_por_venta,4) }} {{ $venta->insumo->unidad_medida?->abreviatura }}.</p></div></div>
@endsection
