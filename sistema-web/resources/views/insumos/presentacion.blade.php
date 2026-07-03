@extends('layouts.app')
@php
$categoria=mb_strtolower($insumo->categoria?->nombre??'otros');
$perfil=match(true){str_contains($categoria,'bebida')=>['ejemplo'=>'Ej: 2 L - Botella plastica','contenido'=>'Volumen','formatos'=>['Botella plastica','Botella de vidrio','Lata','Carton','Bidon']],str_contains($categoria,'verdura')||str_contains($categoria,'fruta')=>['ejemplo'=>'Ej: Bolsa de 5 kg','contenido'=>'Peso','formatos'=>['A granel','Bolsa','Malla','Caja','Bandeja']],str_contains($categoria,'condimento')=>['ejemplo'=>'Ej: Frasco de 250 g','contenido'=>'Peso o volumen','formatos'=>['Sachet','Frasco','Bolsa','Botella','Paquete']],default=>['ejemplo'=>'Ej: Unidad, paquete o presentacion general','contenido'=>'Contenido','formatos'=>['Unidad','Bolsa','Paquete','Caja','A granel']]};
@endphp
@section('content')
<div class="title-wrapper pt-30"><div class="row align-items-center"><div class="col-md-8"><div class="title mb-30"><h2>{{ $presentacion?'Editar presentacion':'Nueva presentacion' }} de {{ $insumo->nombre }}</h2></div></div><div class="col-md-4 text-end"><a class="btn btn-secondary mb-30" href="{{ route('insumos.show',$insumo) }}"><i class="lni lni-arrow-left"></i> Volver</a></div></div></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card-style-3 mb-30"><div class="card-content"><form method="POST" enctype="multipart/form-data" action="{{ $presentacion?route('insumos.presentaciones.update',[$insumo,$presentacion]):route('insumos.presentaciones.store',$insumo) }}">@csrf @if($presentacion)@method('PUT')@endif
@include('insumos._presentacion-form',['presentacion'=>$presentacion,'perfil'=>$perfil,'unidades'=>$unidades,'categorias'=>$categorias])
<div class="mt-4"><button class="btn btn-primary">{{ $presentacion?'Guardar cambios':'Crear presentacion' }}</button> <a class="btn btn-secondary" href="{{ route('insumos.show',$insumo) }}">Cancelar</a></div></form></div></div>
@endsection
