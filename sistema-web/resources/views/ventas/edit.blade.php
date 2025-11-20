@extends('layouts.app')

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Editar Venta') }}</h2>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- ========== title-wrapper end ========== -->

    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                @if ($errors->any())
                    <div class="alert-box danger-alert">
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li class="text-medium">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('ventas.update', $venta->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="select-style-1">
                        <label>¿Que plato vendiste?</label>
                        <div class="select-position">
                            <select name="receta_id">
                                <option value="">Selecciona una receta</option>
                                @foreach ($recetas as $receta)
                                    <option value="{{ $receta->id }}" {{ $venta->receta_id == $receta->id ? 'selected' : '' }}>{{ $receta->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="input-style-1">
                        <label>¿Cuantas unidades vendiste?</label>
                        <input type="number" name="cantidad" value="{{ $venta->cantidad }}" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>

                    <div class="input-style-1">
                        <label>¿A cuanto vendiste cada plato? (Bs.)</label>
                        <input type="number" name="precio" value="{{ $venta->precio }}" min="0" step="0.01" oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
                    </div>
                    <div class="input-style-1">
                        <label>Precio total (Bs.)</label>
                        <input type="number" name="total" value="{{ $venta->total }}">
                    </div>
                    <div class="input-style-1">
                        <label>Fecha de la venta</label>
                        <input type="date" name="fecha" value="{{ $venta->created_at->format('Y-m-d') }}" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Actualizar Venta</button>
                        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection 