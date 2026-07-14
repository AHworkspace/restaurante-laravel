@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30"><div class="row align-items-center">
<div class="col-md-6"><div class="title mb-30"><h2>Consumidores</h2></div></div>
<div class="col-md-6 text-end"><a href="{{ route('consumidores.create') }}" class="main-btn primary-btn btn-hover"><i class="lni lni-plus"></i> Nuevo Consumidor</a></div>
</div></div>
<div class="card-styles"><div class="card-style-3 mb-30"><div class="card-content">
<form method="GET" class="mb-4"><div class="card bg-light p-3 mb-3">
<h6 class="mb-3"><i class="lni lni-search-alt"></i> Filtros de Busqueda</h6><div class="row g-3">
<div class="col-md-3"><label class="form-label small">Buscar por CI o nombre</label><input name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="CI o nombre completo"></div>
<div class="col-md-2"><label class="form-label small">Segmento</label><select name="fuerza_id" class="form-select"><option value="">Todos</option>@foreach($fuerzas as $item)<option value="{{ $item->id }}" @selected(request('fuerza_id')==$item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label small">Institucion</label><select name="institucion_id" id="institucion_filter" class="form-select"><option value="">Todas</option>@foreach($instituciones as $item)<option value="{{ $item->id }}" @selected(request('institucion_id')==$item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label small">Grado</label><select name="grado_id" id="grado_filter" class="form-select"><option value="">Todos</option>@foreach($grados as $item)<option value="{{ $item->id }}" data-institucion="{{ $item->institucion_id }}" @selected(request('grado_id')==$item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
<div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100" title="Buscar"><i class="lni lni-search-alt"></i></button></div>
<div class="col-md-2 d-flex align-items-end"><a href="{{ route('consumidores.index') }}" class="btn btn-secondary w-100"><i class="lni lni-reload"></i> Limpiar</a></div>
</div></div></form>
@php
$totalPendiente=collect($consumidores->items())->sum(fn($c)=>$c->saldoPendiente());
$totalAdelantado=collect($consumidores->items())->sum(fn($c)=>$c->saldoAdelantadoDisponible());
@endphp
<div class="row mb-4">
<div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center"><h6>Total Consumidores</h6><h4>{{ $consumidores->total() }}</h4></div></div></div>
<div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center"><h6>Total Pendiente</h6><h4>Bs. {{ number_format($totalPendiente,2) }}</h4></div></div></div>
<div class="col-md-3"><div class="card bg-success text-white"><div class="card-body text-center"><h6>Total Adelantado</h6><h4>Bs. {{ number_format($totalAdelantado,2) }}</h4></div></div></div>
<div class="col-md-3"><div class="card bg-info text-white"><div class="card-body text-center"><h6>Saldo Neto</h6><h4>Bs. {{ number_format($totalPendiente-$totalAdelantado,2) }}</h4></div></div></div>
</div>
<div class="table-wrapper table-responsive"><table class="table table-hover"><thead><tr><th>ID</th><th>Nombre Completo</th><th>CI</th><th>Segmento</th><th>Institucion</th><th>Grado</th><th>Saldos<br><small>Debe / A favor</small></th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($consumidores as $consumidor)
@php($debe=$consumidor->saldoPendiente()) @php($favor=$consumidor->saldoAdelantadoDisponible())
<tr><td>{{ $consumidor->id }}</td><td><strong>{{ $consumidor->nombre_completo }}</strong><br><small class="text-muted">{{ $consumidor->codigo_unico ?: 'N/A' }}</small></td><td>{{ $consumidor->ci }}</td><td>{{ $consumidor->fuerza?->nombre ?: 'N/A' }}</td><td>{{ $consumidor->institucion?->nombre ?: 'N/A' }}</td><td>{{ $consumidor->grado?->nombre ?: 'N/A' }}</td>
<td><small><span class="{{ $debe>0?'text-danger':'text-muted' }}"><strong>Debe:</strong> Bs. {{ number_format($debe,2) }}</span><br><span class="{{ $favor>0?'text-success':'text-muted' }}"><strong>A favor:</strong> Bs. {{ number_format($favor,2) }}</span></small></td>
<td><span class="badge {{ $consumidor->activo?'bg-success':'bg-secondary' }}">{{ $consumidor->activo?'Activo':'Inactivo' }}</span></td>
<td><div class="action d-flex gap-2"><a href="{{ route('consumidores.show',$consumidor) }}" class="btn btn-info btn-sm" title="Ver detalles"><i class="lni lni-eye"></i></a><a href="{{ route('consumidores.edit',$consumidor) }}" class="btn btn-warning btn-sm" title="Editar"><i class="lni lni-pencil"></i></a><form method="POST" action="{{ route('consumidores.destroy',$consumidor) }}" onsubmit="return confirm('Eliminar este consumidor?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" title="Eliminar"><i class="lni lni-trash-can"></i></button></form></div></td></tr>
@empty<tr><td colspan="9" class="text-center">No se encontraron consumidores.</td></tr>@endforelse
</tbody></table></div><div class="mt-3">{{ $consumidores->links() }}</div>
</div></div></div>
<script>document.getElementById('institucion_filter')?.addEventListener('change',function(){const id=this.value;document.querySelectorAll('#grado_filter option[data-institucion]').forEach(function(option){option.hidden=id!==''&&option.dataset.institucion!==id;if(option.hidden&&option.selected)document.getElementById('grado_filter').value='';});});</script>
@endsection

