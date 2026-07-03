@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30 d-flex justify-content-between"><div class="title"><h2>Consumo #{{ $consumo->id }}</h2></div><a class="btn btn-primary" href="{{ route('pagos.create',['consumidor_id'=>$consumo->consumidor_id]) }}">Registrar pago</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="row"><div class="col-lg-7"><div class="card"><div class="card-body"><dl class="row mb-0">
<dt class="col-4">Consumidor</dt><dd class="col-8"><a href="{{ route('consumidores.show',$consumo->consumidor) }}">{{ $consumo->consumidor->nombre_completo }}</a></dd>
<dt class="col-4">Producto</dt><dd class="col-8">{{ $consumo->producto_nombre }}</dd><dt class="col-4">Cantidad</dt><dd class="col-8">{{ $consumo->cantidad }}</dd>
<dt class="col-4">Fecha</dt><dd class="col-8">{{ $consumo->fecha_consumo->format('d/m/Y') }} {{ substr($consumo->hora_consumo,0,5) }}</dd>
<dt class="col-4">Estado</dt><dd class="col-8">{{ ucfirst($consumo->estado_pago) }}</dd></dl></div></div></div>
<div class="col-lg-5"><div class="card"><div class="card-body"><small>Total</small><h2>Bs {{ number_format($consumo->total,2) }}</h2><small>Pagado</small><h4>Bs {{ number_format($consumo->pagos->sum('pivot.monto_aplicado'),2) }}</h4></div></div></div></div>
@endsection
