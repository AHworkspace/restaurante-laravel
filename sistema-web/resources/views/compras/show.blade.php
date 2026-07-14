@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Compra {{ $compra->numero_documento ?: '#'.$compra->id }}</h2></div></div>
<div class="card-style-3 mb-30"><div class="card-content">
    <div class="row mb-4">
        <div class="col-md-3"><strong>Proveedor</strong><p>{{ $compra->proveedorRel?->nombre ?: $compra->proveedor }}</p></div>
        <div class="col-md-2"><strong>Fecha</strong><p>{{ $compra->fecha_compra?->format('d/m/Y') }}</p></div>
        <div class="col-md-2"><strong>Recepción</strong><p><span class="badge {{ $compra->estado==='recibida'?'bg-success':($compra->estado==='parcial'?'bg-warning text-dark':'bg-secondary') }}">{{ ucfirst($compra->estado) }}</span></p></div>
        <div class="col-md-3"><strong>Total</strong><p>Bs {{ number_format($compra->costo_total,2) }}</p></div>
    </div>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Insumo</th><th>Presentación</th><th>Marca</th><th>Compra</th><th>Precio</th><th>Entrada al inventario</th><th>Recibido</th><th>Total</th><th>Almacén</th></tr></thead>
        <tbody>@foreach($compra->lineas as $linea)<tr>
            <td>{{ $linea->insumo->nombre }}</td>
            <td>{{ $linea->presentacion?->nombre }}@if($linea->formatoEmpaque)<br><small class="text-muted">Compra en {{ $linea->formatoEmpaque->nombre }}</small>@endif @foreach($linea->estructura_empaque??[] as $nivel)@php($formatoNivel=\App\Models\FormatoEmpaque::find($nivel['formato_empaque_id']??null)) @php($unidadNivel=\App\Models\UnidadMedida::find($nivel['unidad_medida_id']??null))<br><small>{{ number_format((float)($nivel['cantidad']??0),2) }} {{ $formatoNivel?->nombre }}@if(!empty($nivel['contenido'])) de {{ number_format((float)$nivel['contenido'],2) }} {{ $unidadNivel?->abreviatura }} cada uno @endif</small>@endforeach</td>
            <td>{{ $linea->marca?->nombre ?: 'Sin especificar' }}</td>
            <td>
                <strong>{{ $linea->cantidadCompraTexto() }}</strong>
                <br><small class="text-muted">Cantidad comprada y su equivalencia para inventario.</small>
            </td>
            <td>Bs {{ number_format($linea->precio_unitario,4) }} por {{ $linea->formatoEmpaque?->nombre?:$linea->unidadMedida?->abreviatura }}</td>
            <td>
                <strong>{{ $linea->entradaInventarioTexto() }}</strong>
                @if($linea->formatoEmpaque&&$linea->cantidad_contenido)<br><small class="text-muted">Cada {{ $linea->formatoEmpaque->nombre }} aporta {{ number_format($linea->cantidad_contenido,4) }} {{ $linea->unidadContenido?->abreviatura }}</small>@elseif($linea->cantidad_contenido)<br><small class="text-muted">Cada {{ $linea->unidadMedida?->abreviatura }} equivale a {{ number_format($linea->cantidad_contenido,4) }} {{ $linea->unidadContenido?->abreviatura }}</small>@endif
            </td>
            <td><strong>{{ $linea->entradaInventarioTexto((float) $linea->cantidad_recibida_base) }} de {{ $linea->entradaInventarioTexto() }}</strong><br><small class="text-muted">Pendiente: {{ $linea->faltanteTexto() }}</small></td>
            <td><strong>Bs {{ number_format($linea->costo_linea,2) }}</strong></td>
            <td>@if($linea->cantidad_faltante_base>0)<a class="btn btn-sm btn-primary" href="{{ route('movimientos.create',['compra_linea_id'=>$linea->id]) }}">Recibir</a>@else<span class="badge bg-success">Completo</span>@endif</td>
        </tr>@endforeach</tbody>
    </table></div>
</div></div>

@endsection

