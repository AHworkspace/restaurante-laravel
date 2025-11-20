@extends('layouts.app')

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Predicciones') }}</h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 mb-30">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Predicción de Desperdicios</h3>
                    </div>
                    <div class="card-body">
                        @livewire('predicciondesperdicio')
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-30">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Predicción de Insumo Mensual</h3>
                    </div>
                    <div class="card-body">
                        @livewire('predicciondos')
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-30">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Predicción de Insumo por Receta</h3>
                    </div>
                    <div class="card-body">
                        @livewire('prediccionuno')
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-30">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cálculo de Insumos Necesarios</h3>
                    </div>
                    <div class="card-body">
                        @livewire('calculoinsumo')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Borde negro para todos los cuadros de predicción */
        .container-fluid .card {
            border: 3px solid #000000 !important;
            border-radius: 8px;
        }

        /* Borde negro para todos los cuadros de predicción (card-style-3) */
        .card-styles .card-style-3 {
            border: 3px solid #000000 !important;
            border-radius: 8px;
        }

        /* Asegurar que el contenido interno tenga padding adecuado */
        .card-style-3 .card-content {
            padding: 20px;
        }

        /* Cambiar los cuadros celestes (headers) a plomo oscuro con texto blanco y borde negro */
        .alert-box.primary-alert {
            background-color: #4A4A4A !important; /* Plomo oscuro */
            border: 2px solid #000000 !important; /* Borde negro */
            border-radius: 6px;
        }

        .alert-box.primary-alert .alert {
            background-color: #4A4A4A !important; /* Plomo oscuro */
            color: #FFFFFF !important; /* Texto blanco */
            border: none !important;
        }

        .alert-box.primary-alert .alert p,
        .alert-box.primary-alert .alert .text-medium {
            color: #FFFFFF !important; /* Texto blanco */
        }
    </style>
@endsection
