@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="menu-background-wrapper">
        <!-- ========== title-wrapper start ========== -->
        <div class="title-wrapper pt-30 text-center">
            <h2 class="page-title">{{ __('Menu Principal') }}</h2>
        </div>
        <!-- ========== title-wrapper end ========== -->
        <div class="container mt-4">
        <div class="row">
          @foreach ($platos as $plato)
            <div class="col-md-4 mb-4">
              <div class="card h-100 shadow-sm">
                @php
                    $imagenUrl = asset('images/recetas.jpg');
                    if ($plato->imagen) {
                        $rutaArchivo = storage_path('app/public/' . $plato->imagen);
                        if (file_exists($rutaArchivo)) {
                            $imagenUrl = asset('storage/' . $plato->imagen);
                        }
                    }
                @endphp
                <img src="{{ $imagenUrl }}" alt="{{ $plato->nombre }}">
                <div class="card-body">
                  <h5 class="card-title" style="color:#4a1c1c;">{{ $plato->nombre }}</h5>
                  <p class="card-text">{{ $plato->descripcion ?: $plato->indicaciones }}</p>
                  <p class="text-muted fw-bold">Precio: ${{ number_format($plato->precio, 2) }}</p>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <style>
        .menu-background-wrapper {
            position: relative;
            min-height: calc(100vh - 100px);
            background: url('{{ asset('images/tablero menu.jpg') }}') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            padding: 20px 0;
        }

        .menu-background-wrapper > * {
            position: relative;
            z-index: 1;
        }

        .card {
            background: rgba(253, 251, 248, 0.98) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .page-title {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            color: #FFFFFF;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/inferencejs"></script>
    @vite(['resources/js/game.js'])
@endsection
