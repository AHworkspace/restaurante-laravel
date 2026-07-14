@extends('layouts.app')
@section('content')
@php
$meta=[
'fuerzas'=>['Segmentos de cliente','fuerzas','Segmento'],
'instituciones'=>['Grupos e instituciones','instituciones','Grupo o institucion'],
'grados'=>['Grados y rangos','grados','Grado o rango'],
'tipos-comida'=>['Tipos de Comida','tipos-comida','Tipo de Comida'],
][$catalogo];
@endphp
<div class="title-wrapper pt-30"><div class="row align-items-center"><div class="col-md-6"><div class="title mb-30"><h2>{{ $meta[0] }}</h2></div></div><div class="col-md-6 text-end"><button class="main-btn primary-btn btn-hover" data-bs-toggle="modal" data-bs-target="#crear"><i class="lni lni-plus"></i> Nuevo {{ $meta[2] }}</button></div></div></div>
<div class="card-styles"><div class="card-style-3 mb-30"><div class="card-content">
<div class="table-wrapper table-responsive"><table class="table table-hover"><thead><tr><th>ID</th><th>Nombre</th>@if($catalogo==='instituciones')<th>Segmento</th>@endif @if($catalogo==='grados')<th>Grupo o institucion</th><th>Orden</th>@endif @if($catalogo==='tipos-comida')<th>Horario</th>@endif<th>Descripcion</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($registros as $registro)<tr><td>{{ $registro->id }}</td><td><strong>{{ $registro->nombre }}</strong></td>@if($catalogo==='instituciones')<td>{{ $registro->fuerza?->nombre?:'N/A' }}</td>@endif @if($catalogo==='grados')<td>{{ $registro->institucion?->nombre?:'N/A' }}</td><td>{{ $registro->orden }}</td>@endif @if($catalogo==='tipos-comida')<td>{{ substr((string)$registro->hora_inicio,0,5)?:'-' }} - {{ substr((string)$registro->hora_fin,0,5)?:'-' }}</td>@endif<td>{{ $registro->descripcion?:'N/A' }}</td><td><span class="badge {{ $registro->activo?'bg-success':'bg-secondary' }}">{{ $registro->activo?'Activo':'Inactivo' }}</span></td><td><button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editar{{ $registro->id }}"><i class="lni lni-pencil"></i></button><form class="d-inline" method="POST" action="{{ route($meta[1].'.destroy',$registro->id) }}" onsubmit="return confirm('Eliminar este registro?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="lni lni-trash-can"></i></button></form></td></tr>
@empty<tr><td colspan="8" class="text-center">No hay registros.</td></tr>@endforelse
</tbody></table></div></div></div></div>
@include('configuracion._modal',['id'=>'crear','registro'=>null,'accion'=>route($meta[1].'.store'),'titulo'=>'Nuevo '.$meta[2]])
@foreach($registros as $registro)@include('configuracion._modal',['id'=>'editar'.$registro->id,'registro'=>$registro,'accion'=>route($meta[1].'.update',$registro->id),'titulo'=>'Editar '.$meta[2]])@endforeach
@endsection

