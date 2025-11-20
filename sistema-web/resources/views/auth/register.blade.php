@extends('layouts.guest')

@section('content')
<div class="login-container">
    @if ($errors->any())
    <div class="alert alert-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <div id="successMessage" class="alert alert-success" style="display: none;">
        ¡Registro exitoso! Tu cuenta ha sido creada correctamente.
    </div>

    <div class="col-lg-6 logo-section">
        <div class="auth-cover-wrapper" style="background: url('{{ asset('images/logo01.jpg') }}') no-repeat center center; background-size: contain; min-height: 100vh;">
        </div>
    </div>
    <div class="col-lg-6 form-section">
        <div class="form-container">
            <h2 class="register-title">Registro Usuario</h2>
            <form action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" 
                                class="form-input @error('nombre') is-invalid @enderror"
                                name="nombre" 
                                value="{{ old('nombre') }}" 
                                placeholder="Nombre"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" 
                                class="form-input @error('apellido_paterno') is-invalid @enderror"
                                name="apellido_paterno" 
                                value="{{ old('apellido_paterno') }}" 
                                placeholder="Apellido Paterno"
                                required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" 
                                class="form-input @error('apellido_materno') is-invalid @enderror"
                                name="apellido_materno" 
                                value="{{ old('apellido_materno') }}" 
                                placeholder="Apellido Materno"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="email" 
                                class="form-input @error('email') is-invalid @enderror"
                                name="email" 
                                value="{{ old('email') }}" 
                                placeholder="Correo Electrónico"
                                required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="password" 
                                class="form-input @error('password') is-invalid @enderror"
                                name="password" 
                                placeholder="Contraseña"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="password" 
                                class="form-input"
                                name="password_confirmation" 
                                placeholder="Confirmar Contraseña"
                                required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group">
                            <select name="role" id="roleSelect" class="form-input @error('role') is-invalid @enderror" required>
                                <option value="">Seleccione un rol</option>
                                <option value="administrador" {{ old('role') == 'administrador' ? 'selected' : '' }}>Administrador</option>
                                <option value="cajero" {{ old('role') == 'cajero' ? 'selected' : '' }}>Cajero</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="register-button" id="registerButton">
                    REGISTRARSE
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .login-container {
        display: flex;
        min-height: 100vh;
        background: #4a1c1c;
        position: relative;
    }

    .alert {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        z-index: 1000;
        animation: slideDown 0.5s ease-out;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .alert-success {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #45a049;
    }

    .alert-error {
        background-color: #f44336;
        color: white;
        border: 1px solid #da190b;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }

    @keyframes slideDown {
        from {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: white;
        color: #4a1c1c;
    }

    .form-input:focus {
        outline: none;
        border-color: #ff9933;
        box-shadow: 0 0 0 2px rgba(255, 153, 51, 0.2);
    }

    .form-input.is-invalid {
        border-color: #f44336;
        background-color: #fff5f5;
    }

    .register-button {
        width: 100%;
        padding: 0.875rem;
        background: linear-gradient(45deg, #ff6633 0%, #ff9933 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        margin-top: 1rem;
    }

    .register-button:hover {
        background: linear-gradient(45deg, #ff5522 0%, #ff8822 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 102, 51, 0.2);
    }

    .logo-section {
        position: relative;
        background-color: #4a1c1c;
    }

    .form-section {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: #4a1c1c;
    }

    .form-container {
        width: 100%;
        max-width: 800px;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .register-title {
        color: #4a1c1c;
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 2rem;
        text-align: center;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: -0.5rem;
    }

    .col-md-6 {
        flex: 0 0 50%;
        padding: 0.5rem;
    }

    .col-12 {
        flex: 0 0 100%;
        padding: 0.5rem;
    }

    .input-group {
        margin-bottom: 1rem;
    }

    .input-group input,
    .input-group select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: white;
        color: #4a1c1c;
    }

    .input-group input:focus,
    .input-group select:focus {
        outline: none;
        border-color: #ff9933;
        box-shadow: 0 0 0 2px rgba(255, 153, 51, 0.2);
    }

    .input-group input::placeholder {
        color: #6B7280;
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    @media (max-width: 768px) {
        .login-container {
            flex-direction: column;
        }

        .logo-section {
            min-height: 30vh;
        }

        .form-container {
            padding: 1.5rem;
            margin: 1rem;
        }

        .col-md-6 {
            flex: 0 0 100%;
        }

        .register-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const successMessage = document.getElementById('successMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Crear el nombre completo combinando los campos
        const nombre = form.querySelector('[name="nombre"]').value;
        const apellidoPaterno = form.querySelector('[name="apellido_paterno"]').value;
        const apellidoMaterno = form.querySelector('[name="apellido_materno"]').value;
        
        // Crear un campo oculto para el nombre completo
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'name';
        nameInput.value = `${nombre} ${apellidoPaterno} ${apellidoMaterno}`;
        form.appendChild(nameInput);

        // Mostrar mensaje de éxito
        successMessage.style.display = 'block';
        
        // Esperar un momento antes de enviar el formulario
        setTimeout(() => {
            form.submit();
        }, 1500);
    });
});
</script>
@endsection
