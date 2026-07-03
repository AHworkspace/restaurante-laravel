@extends('layouts.app')

@section('content')
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title mb-30">
                    <h2>{{ __('Usuarios') }}</h2>
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

                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Gestiona Usuarios de tu sistema
                        </p>
                    </div>
                </div>

                <!-- Mensajes de alerta -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


                @can('usuarios.crear')
                <div class="mb-3">
                    <button class="main-btn dark-btn btn-hover mt-3" data-bs-toggle="modal"
                        data-bs-target="#crearUsuarioModal">
                        <i class="lni lni-circle-plus"></i>
                        Crear nuevo
                    </button>
                </div>
                @endcan

                @can('usuarios.editar')
                @livewire('usuario')
                @endcan

                @cannot('usuarios.editar')
                <!-- Vista solo lectura para director -->
                <div class="table-wrapper table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="lead-info">
                                    <h6>Nombre</h6>
                                </th>
                                <th>
                                    <h6>Apellidos</h6>
                                </th>
                                <th>
                                    <h6>Correo</h6>
                                </th>
                                <th>
                                    <h6>Rol</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td class="min-width">
                                        <p>{{ $user->nombre }}</p>
                                    </td>
                                    <td class="min-width">
                                        <p>{{ $user->apellido_paterno }} {{ $user->apellido_materno }}</p>
                                    </td>
                                    <td class="min-width">
                                        <p>{{ $user->email }}</p>
                                    </td>
                                    <td class="min-width">
                                        <p>{{ $user->roles->first()->name ?? 'Sin rol' }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No existen usuarios disponibles</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Paginación -->
                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
                @endcannot
            </div>
        </div>
    </div>

    @can('usuarios.crear')
    <div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearUsuarioLabel">Crear nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" value="{{ old('nombre') }}">
                            <div>
                                @error('nombre')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}">
                            <div>
                                @error('apellido_paterno')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}">
                            <div>
                                @error('apellido_materno')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}">
                            <div>
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" id="password">
                            <div>
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('crearUsuarioModal'));
                modal.show();
            });
        </script>
    @endif
@endsection
