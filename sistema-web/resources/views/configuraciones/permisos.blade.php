@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Configuraciones - Permisos</h2></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card-style-3 mb-30"><div class="card-content">
<form method="GET" action="{{ route('configuraciones.permisos') }}"><label>Rol a configurar</label><select class="form-select" name="role_id" onchange="this.form.submit()">@foreach($roles as $item)<option value="{{ $item->id }}" @selected($role?->id===$item->id)>{{ $item->name }}</option>@endforeach</select></form>
</div></div>

@if($role)
<form method="POST" action="{{ route('configuraciones.permisos.update',$role) }}">@csrf @method('PUT')
@if($role->name==='admin')<div class="alert alert-info">El administrador tiene acceso total. Sus permisos están protegidos.</div>@endif
@foreach($catalogo as $sector=>$subsectores)
@php $sectorId=Str::slug($sector); @endphp
<div class="card-style-3 mb-20"><div class="card-content">
<div class="d-flex justify-content-between align-items-center mb-3"><h4>{{ $sector }}</h4><label class="mb-0"><input type="checkbox" class="selector-grupo" data-target="sector-{{ $sectorId }}" @disabled($role->name==='admin')> Todo el sector</label></div>
@foreach($subsectores as $subsector=>$permisos)
@php $subsectorId=$sectorId.'-'.Str::slug($subsector); @endphp
<section class="border-top py-3">
<div class="d-flex justify-content-between align-items-center mb-3"><h5>{{ $subsector }}</h5><label class="mb-0"><input type="checkbox" class="selector-grupo sector-{{ $sectorId }}" data-target="subsector-{{ $subsectorId }}" @disabled($role->name==='admin')> Todo el subsector</label></div>
<div class="row">@foreach($permisos as $nombre=>$etiqueta)<div class="col-lg-3 col-md-6 mb-3"><label class="d-flex gap-2 align-items-start"><input class="form-check-input sector-{{ $sectorId }} subsector-{{ $subsectorId }}" type="checkbox" name="permisos[]" value="{{ $nombre }}" @checked(in_array($nombre,$asignados)||$role->name==='admin') @disabled($role->name==='admin')><span><strong>{{ $etiqueta }}</strong><small class="text-muted d-block">{{ $nombre }}</small></span></label></div>@endforeach</div>
</section>
@endforeach
</div></div>
@endforeach
@if($role->name!=='admin')<button class="main-btn primary-btn btn-hover">Guardar permisos</button>@endif
</form>
@endif
<script>document.querySelectorAll('.selector-grupo').forEach(function(control){control.addEventListener('change',function(){document.querySelectorAll('.'+this.dataset.target).forEach(function(item){item.checked=control.checked;});});});</script>
@endsection
