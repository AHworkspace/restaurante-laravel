@extends('layouts.app')
@php use Illuminate\Support\Facades\Storage; @endphp
@section('content')
<div class="title-wrapper pt-30"><div class="row align-items-center"><div class="col-md-8"><div class="title mb-30"><h2>{{ $insumo->nombre }}</h2></div></div><div class="col-md-4 text-end"><a class="btn btn-secondary" href="{{ route('insumos.index') }}">Volver</a></div></div></div>
<div class="card-style-3 mb-30"><div class="card-content"><div class="row g-3"><div class="col-md-4"><strong>Unidad general</strong><div>{{ $insumo->unidad_medida?->nombre?:'-' }} @if($insumo->unidad_medida)({{ $insumo->unidad_medida->abreviatura }})@endif</div></div><div class="col-md-8"><strong>Descripcion</strong><div>{{ $insumo->descripcion?:'Sin descripcion' }}</div></div><div class="col-12"><strong>Presentaciones registradas:</strong> {{ $insumo->presentaciones->count() }}</div></div></div></div>

<div class="card-style-3 mb-30"><div class="card-content">
<div class="d-flex justify-content-between align-items-center mb-3"><h4>Presentaciones</h4>@can('insumos.editar')<a class="btn btn-primary" href="{{ route('insumos.presentaciones.create',$insumo) }}"><i class="lni lni-plus"></i> Nueva presentacion</a>@endcan</div>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Imagen</th><th>Presentacion</th><th>Clasificacion</th><th>Stock actual</th><th>Stock minimo</th><th>Costo estandar</th><th>Empaque<br><small class="text-muted fw-normal">Como viene el insumo</small></th><th>Contenido<br><small class="text-muted fw-normal">Cantidad que contiene</small></th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($insumo->presentaciones as $presentacion)
@php($imagen=$presentacion->imagen&&Storage::disk('public')->exists($presentacion->imagen)?asset('storage/'.$presentacion->imagen):asset('images/cereales.jpg'))
<tr>
<td><img src="{{ $imagen }}" alt="{{ $presentacion->nombre }}" style="width:54px;height:54px;object-fit:cover;border-radius:6px"></td>
<td><strong>{{ $presentacion->nombre }}</strong>@if($presentacion->predeterminada)<br><small class="text-muted">Predeterminada</small>@endif</td>
<td>{{ $presentacion->categoria?->nombre?:'-' }}<br><small>{{ ucfirst($presentacion->tipo_uso) }}</small>@if($presentacion->descripcion)<br><small class="text-muted">{{ $presentacion->descripcion }}</small>@endif</td>
<td><strong>{{ number_format($presentacion->stockDisponible(),2) }}</strong> {{ $presentacion->unidadStock()?->abreviatura }}</td>
<td>{{ number_format($presentacion->stock_minimo,2) }} {{ $presentacion->unidadStock()?->abreviatura }}</td>
<td>{{ $presentacion->costo_estandar!==null?'Bs '.number_format($presentacion->costo_estandar,2):'-' }}</td>
<td>{{ $presentacion->formatoEmpaque?->nombre?:($presentacion->tipo_envase?:'Sin empaque definido') }}</td>
<td>@if($presentacion->contenido){{ number_format($presentacion->contenido,2) }} {{ $presentacion->unidadContenido?->abreviatura }}@else<span class="text-muted">No aplica</span>@endif</td>
<td><span class="badge {{ !$presentacion->activa?'bg-secondary':($presentacion->stockBajo()?'bg-danger':'bg-success') }}">{{ !$presentacion->activa?'Inactiva':($presentacion->stockBajo()?'Stock bajo':'Disponible') }}</span></td>
<td><div class="d-flex gap-2">@can('insumos.editar')<a class="btn btn-warning btn-sm" href="{{ route('insumos.presentaciones.edit',[$insumo,$presentacion]) }}" title="Editar"><i class="lni lni-pencil"></i></a>@endcan @can('insumos.eliminar')<form method="POST" action="{{ route('insumos.presentaciones.destroy',[$insumo,$presentacion]) }}" onsubmit="return confirm('Eliminar esta presentacion?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" title="Eliminar"><i class="lni lni-trash-can"></i></button></form>@endcan</div></td>
</tr>
@empty<tr><td colspan="10" class="text-center">Este insumo no tiene presentaciones.</td></tr>@endforelse
</tbody></table></div></div></div>

<div class="card-style-3 mb-30"><div class="card-content"><h4 class="mb-3">Procedencia registrada en compras</h4><div class="table-responsive"><table class="table"><thead><tr><th>Fecha</th><th>Presentacion</th><th>Proveedor</th><th>Marca</th><th>Pedido</th><th>Costo</th></tr></thead><tbody>@forelse($insumo->lineasCompra->sortByDesc(fn($l)=>$l->compra?->fecha_compra) as $linea)<tr><td>{{ $linea->compra?->fecha_compra?->format('d/m/Y') }}</td><td>{{ $linea->presentacion?->nombre }}</td><td>{{ $linea->compra?->proveedorRel?->nombre?:$linea->compra?->proveedor }}</td><td>{{ $linea->marca?->nombre?:'Sin especificar' }}</td><td>{{ number_format($linea->cantidad_pedida,2) }} {{ $linea->unidadMedida?->abreviatura }}</td><td>Bs {{ number_format($linea->costo_linea,2) }}</td></tr>@empty<tr><td colspan="6" class="text-center">Sin compras vinculadas.</td></tr>@endforelse</tbody></table></div></div></div>
@endsection

