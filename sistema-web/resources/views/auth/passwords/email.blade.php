@extends('layouts.guest')

@section('content')
<div class="login-container">
    <div class="col-lg-6 logo-section">
        <div class="auth-cover-wrapper" style="background: url('{{ asset('images/logo01.jpg') }}') no-repeat center center; background-size: contain; min-height: 100vh;">
        </div>
    </div>
    <div class="col-lg-6 form-section">
        <div class="form-container">
            <h2 class="reset-title">Restablecer<br>Contraseña</h2>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                    Se ha enviado el enlace de recuperación a tu correo electrónico.
                    </div>
                @endif

            <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                <div class="input-group">
                    <input id="email" 
                           type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="email" 
                           autofocus
                           placeholder="Correo Electrónico">

                                @error('email')
                        <span class="error-message">
                            No pudimos encontrar un usuario con esa dirección de correo electrónico.
                                    </span>
                                @enderror
                            </div>

                <button type="submit" class="reset-button">
                    ENVIAR ENLACE DE RECUPERACIÓN
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
        max-width: 450px;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .reset-title {
        color: #4a1c1c;
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 2rem;
        text-align: center;
        line-height: 1.2;
    }

    .input-group {
        width: 100%;
        margin-bottom: 1.25rem;
    }

    .input-group input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: white;
        color: #4a1c1c;
    }

    .input-group input:focus {
        outline: none;
        border-color: #ff9933;
        box-shadow: 0 0 0 2px rgba(255, 153, 51, 0.2);
    }

    .input-group input::placeholder {
        color: #6B7280;
    }

    .reset-button {
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
        margin-top: 0.5rem;
    }

    .reset-button:hover {
        background: linear-gradient(45deg, #ff5522 0%, #ff8822 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 102, 51, 0.2);
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .alert-success {
        background-color: #f0fdf4;
        color: #16a34a;
        border: 1px solid #dcfce7;
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

        .reset-title {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }
    }
</style>
@endsection