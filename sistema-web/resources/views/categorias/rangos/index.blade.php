@extends('layouts.app')
@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Clasificacion de clientes</h2><p class="text-muted">Segmentos, grupos y rangos utilizados para organizar consumidores.</p></div></div>
<div class="card-styles"><div class="row">
<div class="col-lg-4 mb-30"><a class="text-decoration-none" href="{{ route('fuerzas.index') }}"><div class="card-style-3 h-100"><div class="card-content text-center"><i class="lni lni-users" style="font-size:40px"></i><h4 class="mt-3">Segmentos de cliente</h4><p class="text-muted">Clasificacion general de consumidores.</p></div></div></a></div>
<div class="col-lg-4 mb-30"><a class="text-decoration-none" href="{{ route('instituciones.index') }}"><div class="card-style-3 h-100"><div class="card-content text-center"><i class="lni lni-apartment" style="font-size:40px"></i><h4 class="mt-3">Grupos e instituciones</h4><p class="text-muted">Organizaciones y convenios asociados.</p></div></div></a></div>
<div class="col-lg-4 mb-30"><a class="text-decoration-none" href="{{ route('grados.index') }}"><div class="card-style-3 h-100"><div class="card-content text-center"><i class="lni lni-star" style="font-size:40px"></i><h4 class="mt-3">Grados y rangos</h4><p class="text-muted">Subniveles dentro de cada grupo.</p></div></div></a></div>
</div></div>
@endsection
