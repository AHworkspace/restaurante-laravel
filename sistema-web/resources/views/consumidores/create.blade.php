@extends('layouts.app')

@section('content')
<div class="title-wrapper pt-30"><div class="title mb-30"><h2>Nuevo consumidor</h2></div></div>
<div class="card-styles"><div class="card-style-3 mb-30"><div class="card-content">
    <form method="POST" action="{{ route('consumidores.store') }}">
        @include('consumidores._form')
    </form>
</div></div></div>
@endsection
