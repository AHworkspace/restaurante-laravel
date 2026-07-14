@extends('layouts.app')

@section('content')
@php
    $hayFiltroReporte = collect(request()->except('page'))
        ->filter(fn ($valor) => $valor !== null && $valor !== '' && $valor !== 'todos')
        ->isNotEmpty();
@endphp
<div class="title-wrapper pt-30">
    <div class="row align-items-center">
        <div class="col-md-6"><div class="title mb-30"><h2>Consumos</h2></div></div>
        <div class="col-md-6 text-end"><a href="{{ route('consumos.create') }}" class="main-btn primary-btn btn-hover"><i class="lni lni-plus"></i> Registrar Consumo</a></div>
    </div>
</div>

<div class="card-styles">
    <div class="card-style-3 mb-30">
        <div class="card-content">
            @php($pendiente=($resumen->get('pendiente')?->total??0)+($resumen->get('parcial')?->total??0))
            <div class="row mb-4">
                <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center"><h6>Total Pendiente</h6><h4>Bs. {{ number_format($pendiente,2) }}</h4></div></div></div>
                <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center"><h6>Total Pagado</h6><h4>Bs. {{ number_format($resumen->get('pagado')?->total??0,2) }}</h4></div></div></div>
                <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body text-center"><h6>Total Cancelado</h6><h4>Bs. {{ number_format($resumen->get('cancelado')?->total??0,2) }}</h4></div></div></div>
                <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center"><h6>Total General</h6><h4>Bs. {{ number_format($resumen->sum('total'),2) }}</h4></div></div></div>
            </div>

            @if($estadisticasPorTipo->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">Estadisticas por Tipo de Comida</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($tiposComida as $tipo)
                                @if($stats=$estadisticasPorTipo->get($tipo->id))
                                    <div class="col-md-3 mb-2"><div class="border rounded p-2"><strong>{{ $tipo->nombre }}</strong><br><small>Cantidad: {{ $stats->cantidad }}</small><br><small>Total: Bs. {{ number_format($stats->total,2) }}</small></div></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form method="GET" class="mb-4">
                <div class="card bg-light p-3">
                    <h6 class="mb-3"><i class="lni lni-search-alt"></i> Filtros de Busqueda</h6>
                    <div class="row g-3">
                        <div class="col-md-3 position-relative">
                            <label class="form-label small">Buscar Consumidor</label>
                            <input id="buscar_consumidor" class="form-control" value="{{ request('buscar') }}" placeholder="CI o nombre">
                            <input type="hidden" name="consumidor_id" id="consumidor_id" value="{{ request('consumidor_id') }}">
                            <div id="resultados_consumidor" class="list-group position-absolute w-100" style="z-index:20"></div>
                        </div>
                        <div class="col-md-2"><label class="form-label small">Tipo de Comida</label><select class="form-select" name="tipo_comida_id"><option value="">Todos</option>@foreach($tiposComida as $tipo)<option value="{{ $tipo->id }}" @selected(request('tipo_comida_id')==$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select></div>
                        <div class="col-md-2"><label class="form-label small">Receta</label><select class="form-select" name="receta_id"><option value="">Todas</option>@foreach($recetas as $receta)<option value="{{ $receta->id }}" @selected(request('receta_id')==$receta->id)>{{ $receta->nombre }}</option>@endforeach</select></div>
                        <div class="col-md-2"><label class="form-label small">Estado</label><select class="form-select" name="estado_pago"><option value="">Todos</option>@foreach(['pendiente','parcial','pagado','cancelado'] as $estado)<option value="{{ $estado }}" @selected(request('estado_pago')===$estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
                        <div class="col-md-1"><label class="form-label small">Desde</label><input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}"></div>
                        <div class="col-md-1"><label class="form-label small">Hasta</label><input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}"></div>
                        <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100" title="Buscar"><i class="lni lni-search-alt"></i></button></div>
                    </div>
                    <a class="btn btn-secondary btn-sm mt-3" href="{{ route('consumos.index') }}"><i class="lni lni-reload"></i> Limpiar Filtros</a>
                </div>
            </form>

            <div class="table-wrapper table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            @if($hayFiltroReporte)
                                <th><button type="button" class="btn btn-sm btn-outline-primary reporte-select-all-top" data-target-sector="consumos">Seleccionar todos</button></th>
                            @endif
                            <th>ID</th><th>Consumidor</th><th>Receta</th><th>Tipo</th><th>Cantidad</th><th>Total</th><th>Fecha y hora</th><th>Estado</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consumos as $consumo)
                            <tr>
                                @if($hayFiltroReporte)
                                    <td><input type="checkbox" class="form-check-input reporte-row-checkbox" data-sector="consumos" value="{{ $consumo->id }}" data-total="{{ $consumo->total }}"></td>
                                @endif
                                <td>{{ $consumo->id }}</td>
                                <td><strong>{{ $consumo->consumidor->nombre_completo }}</strong><br><small>{{ $consumo->consumidor->ci }}</small></td>
                                <td>{{ $consumo->producto_nombre }}</td>
                                <td>{{ $consumo->tipoComida?->nombre ?: 'N/A' }}</td>
                                <td>{{ $consumo->cantidad }}</td>
                                <td><strong>Bs. {{ number_format($consumo->total,2) }}</strong></td>
                                <td>{{ $consumo->fecha_consumo->format('d/m/Y') }}<br><small>{{ substr($consumo->hora_consumo,0,5) }}</small></td>
                                <td><span class="badge {{ $consumo->estado_pago==='pagado'?'bg-success':($consumo->estado_pago==='cancelado'?'bg-danger':($consumo->estado_pago==='pendiente'?'bg-warning':'')) }}" @if($consumo->estado_pago==='parcial') style="background-color:#fd7e14;color:#fff;" @endif>{{ ucfirst($consumo->estado_pago) }}</span></td>
                                <td><a class="btn btn-info btn-sm" href="{{ route('consumos.show',$consumo) }}" title="Ver"><i class="lni lni-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $hayFiltroReporte ? 10 : 9 }}" class="text-center">No se encontraron consumos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $consumos->links() }}</div>
            @if($hayFiltroReporte)
                @include('reportes._guardar-filtrado', ['sector' => 'consumos', 'titulo' => 'Guardar reporte de consumos'])
            @endif
        </div>
    </div>
</div>

<script>
var campo=document.getElementById('buscar_consumidor'),resultados=document.getElementById('resultados_consumidor'),idCampo=document.getElementById('consumidor_id');
campo?.addEventListener('input',async function(){if(this.value.length<2){resultados.innerHTML='';idCampo.value='';return;}var datos=await fetch('{{ route('consumidores.buscar') }}?q='+encodeURIComponent(this.value)).then(function(r){return r.json();});resultados.innerHTML='';datos.forEach(function(c){var item=document.createElement('button');item.type='button';item.className='list-group-item list-group-item-action';item.dataset.id=c.id;item.dataset.nombre=c.nombre_completo;item.innerHTML='<strong>'+c.nombre_completo+'</strong><br><small>CI: '+c.ci+'</small>';resultados.appendChild(item);});});
resultados?.addEventListener('click',function(e){var item=e.target.closest('[data-id]');if(!item)return;idCampo.value=item.dataset.id;campo.value=item.dataset.nombre;resultados.innerHTML='';});
</script>
@endsection
