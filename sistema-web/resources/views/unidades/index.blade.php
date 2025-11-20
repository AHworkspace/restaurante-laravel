@extends('layouts.app')

@section('title', 'Unidades de Medida')

@section('styles')
<!-- Agrega estilos adicionales aquí si los necesitas -->
<style>
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Card principal -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Unidades de Medida</h3>
                    @role('admin|cocinero')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="fas fa-plus"></i> Nueva Unidad
                    </button>
                    @endrole
                </div>

                <div class="card-body">
                    <!-- Mensajes de alerta -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Abreviatura</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unidades ?? [] as $unidad)
                                    <tr>
                                        <td>{{ $unidad->id }}</td>
                                        <td>{{ $unidad->nombre }}</td>
                                        <td>{{ $unidad->abreviatura }}</td>
                                        <td>{{ $unidad->descripcion ?? 'N/A' }}</td>
                                        <td>
                                            @role('admin|cocinero')
                                            <button type="button"
                                                    class="btn"
                                                    style="background-color: #6F4E37; color: white; border-radius: 5px; padding: 8px 20px; border: none; margin-right: 8px;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $unidad->id }}">
                                                EDITAR
                                            </button>
                                            <form action="{{ route('unidades.destroy', $unidad->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn"
                                                        style="background-color: #6F4E37; color: white; border-radius: 5px; padding: 8px 20px; border: none;"
                                                        onclick="return confirm('¿Está seguro de eliminar esta unidad de medida?')">
                                                    ELIMINAR
                                                </button>
                                            </form>
                                            @endrole
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay unidades de medida registradas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @role('admin|cocinero')
    <!-- Modal Crear -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">
                        <i class="fas fa-plus-circle"></i> Nueva Unidad de Medida
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('unidades.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre" required maxlength="50"
                                value="{{ old('nombre') }}">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="abreviatura" class="form-label">Abreviatura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('abreviatura') is-invalid @enderror"
                                id="abreviatura" name="abreviatura" required maxlength="10"
                                value="{{ old('abreviatura') }}">
                            @error('abreviatura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                id="descripcion" name="descripcion" maxlength="100"
                                rows="3">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modales de Edición -->
    @foreach($unidades ?? [] as $unidad)
        <div class="modal fade" id="editModal{{ $unidad->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Editar Unidad de Medida
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('unidades.update', $unidad->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre{{ $unidad->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    id="nombre{{ $unidad->id }}" name="nombre" required maxlength="50"
                                    value="{{ old('nombre', $unidad->nombre) }}">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="abreviatura{{ $unidad->id }}" class="form-label">Abreviatura <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('abreviatura') is-invalid @enderror"
                                    id="abreviatura{{ $unidad->id }}" name="abreviatura" required maxlength="10"
                                    value="{{ old('abreviatura', $unidad->abreviatura) }}">
                                @error('abreviatura')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="descripcion{{ $unidad->id }}" class="form-label">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                    id="descripcion{{ $unidad->id }}" name="descripcion" maxlength="100"
                                    rows="3">{{ old('descripcion', $unidad->descripcion) }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    @endrole
</div>
@endsection

@section('scripts')
<script>
    // Mostrar modal de edición si hay errores de validación
    @if($errors->any())
        @if(old('_method') == 'PUT')
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('editModal{{ old("id") }}'));
                modal.show();
            });
        @else
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('createModal'));
                modal.show();
            });
        @endif
    @endif

    // Auto-cerrar alertas después de 5 segundos
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endsection
