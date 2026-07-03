@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-dark text-white rounded-top-4" style="font-weight: bold; font-size: 1.3rem;">Historial de Acciones</div>
                <div class="card-body bg-light rounded-bottom-4">
                    <form method="GET" class="mb-3 row g-2 align-items-end filtros-historial">
                        <div class="col-md-2">
                            <select name="usuario" class="form-control">
                                <option value="">Todos los usuarios</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario }}" {{ request('usuario') == $usuario ? 'selected' : '' }}>{{ $usuario }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="rol" class="form-control">
                                <option value="">Todos los roles</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol }}" {{ request('rol') == $rol ? 'selected' : '' }}>{{ $rol }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="accion" class="form-control" placeholder="Acción" value="{{ request('accion') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="seccion" class="form-control">
                                <option value="">Todas las secciones</option>
                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion }}" {{ request('seccion') == $seccion ? 'selected' : '' }}>{{ $seccion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="col-md-1">
                            <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn w-100">FILTRAR</button>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('historial.index') }}" class="btn btn-secondary w-100">LIMPIAR</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table historial-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Sector</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Acción</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $item)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td>{{ $item->fecha ? \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') : '' }}</td>
                                        <td>{{ $item->hora }}</td>
                                        <td>{{ $item->seccion }}</td>
                                        <td>{{ $item->usuario }}</td>
                                        <td>{{ $item->rol }}</td>
                                        <td><strong>{{ $item->accion }}</strong></td>
                                        <td style="min-width:320px; white-space:normal;">{{ $item->detalles }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No hay acciones registradas en el historial</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $historial->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .historial-table {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
        background: #fff;
    }
    .historial-table th, .historial-table td {
        border: none !important;
        border-bottom: 1.5px solid #e0e0e0 !important;
        padding: 1.1rem 1.5rem !important;
        font-size: 1.13rem;
        vertical-align: middle !important;
        background: #fff !important;
    }
    .historial-table th {
        background: #f3eee6 !important;
        color: #7A5C58 !important;
        font-weight: 600;
    }
    .historial-table tr {
        height: 60px;
    }
    .historial-table tbody tr:nth-child(even) td {
        background: #faf6f3 !important;
    }
    .historial-table tbody tr:hover td {
        background: #ede3d9 !important;
    }
    .filtros-historial input, .filtros-historial button, .filtros-historial select {
        border-radius: 0.6rem !important;
        font-size: 1.05rem;
    }
    .filtros-historial button {
        background: #6F4E37;
        color: #fff;
        font-weight: 600;
        border: none;
        padding: 0.5rem 1.2rem;
        transition: background 0.2s;
    }
    .filtros-historial button:hover {
        background: #5D403D;
    }
    .filtros-historial .btn-secondary {
        background: #6c757d;
        color: #fff;
        font-weight: 600;
        border: none;
        padding: 0.5rem 1.2rem;
        transition: background 0.2s;
    }
    .filtros-historial .btn-secondary:hover {
        background: #5a6268;
    }
    .filtros-historial select {
        background-color: #fff;
        border: 1px solid #ced4da;
        color: #495057;
    }
    .filtros-historial select:focus {
        border-color: #7A5C58;
        box-shadow: 0 0 0 0.2rem rgba(122, 92, 88, 0.25);
    }
</style>
@endpush
