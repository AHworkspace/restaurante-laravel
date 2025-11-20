@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Recetas') }}</h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="card-style settings-card-1 mb-30 mx-auto" style="max-width: 800px;">
        <div class="title mb-30 d-flex justify-content-between align-items-center">
            <h6>{{$receta->nombre}}</h6>
            <button class="border-0 bg-transparent">
                <i class="lni lni-pencil-alt"></i>
            </button>
        </div>
        <div class="profile-info">
            <div class="d-flex align-items-center mb-30">
                @php
                    $imagenUrl = asset('images/recetas.jpg');
                    if ($receta->imagen) {
                        $rutaArchivo = storage_path('app/public/' . $receta->imagen);
                        if (file_exists($rutaArchivo)) {
                            $imagenUrl = asset('storage/' . $receta->imagen);
                        }
                    }
                @endphp
                <div class="profile-image">
                    <img src="{{ $imagenUrl }}" alt="" width="80" height="80">
                </div>
                <div class="profile-meta">
                    <h2 class="text-bold text-dark mb-10">{{$receta->nombre}}</h2>
                    <p>Esta receta tiene insumos calculado para una sola porción</p>
                </div>
            </div>
            <div class="input-style-1">
                <label>Tiempo de Preparación</label>
                <input type="text" value="{{$receta->tiempo_preparacion}}" readonly>
            </div>
            <div class="input-style-1">
                <label>Indicaciones</label>
                <textarea rows="4" readonly>{{$receta->indicaciones}}</textarea>
            </div>

            <div class="input-style-1">
                <label>Insumos</label>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Cantidad</th>
                            <th scope="col">Unidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receta->insumos as $insumo)
                            <tr>
                                <td>{{ $insumo->nombre }}</td>
                                <td>{{ $insumo->pivot->cantidad }}</td>
                                <td>{{ $insumo->unidad_medida->abreviatura }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
