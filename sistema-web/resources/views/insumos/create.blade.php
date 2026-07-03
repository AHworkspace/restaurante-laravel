@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Nuevo insumo</h2></div></div>
<div class="card-style-3 mb-30"><div class="card-content">
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('insumos.store') }}">@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nombre del insumo</label><input class="form-control" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Cebolla" required></div>
<div class="col-md-6"><label class="form-label">Unidad de medida general</label><select class="form-select" name="unidad_medida_id" required><option value="">Seleccionar</option>@foreach($unidades as $unidad)<option value="{{ $unidad->id }}" @selected(old('unidad_medida_id')==$unidad->id)>{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>@endforeach</select><small class="text-muted">Dato informativo general del insumo.</small></div>
<div class="col-12"><label class="form-label">Descripcion</label><textarea class="form-control" name="descripcion" rows="3" placeholder="Descripcion general del insumo">{{ old('descripcion') }}</textarea></div>
<div class="col-12"><button class="btn btn-primary">Continuar a presentaciones</button> <a class="btn btn-secondary" href="{{ route('insumos.index') }}">Cancelar</a></div>
</div></form></div></div>
@endsection
