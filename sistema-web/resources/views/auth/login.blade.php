@extends('layouts.guest')

@section('content')
@php
use App\Models\ConfiguracionSistema as ConfigSistema;
$nombreRestaurante=ConfigSistema::valor('nombre_restaurante','Las Brazas');
$imagenLogin=ConfigSistema::valor('imagen_login');
$colorPrimario=ConfigSistema::valor('color_primario','#7A5C58');
$colorSecundario=ConfigSistema::valor('color_secundario','#CDBEAC');
$tipografia=ConfigSistema::valor('tipografia','Arial');
$tipografiaTitulos=ConfigSistema::valor('tipografia_titulos','Arial');
$posicionLogin=ConfigSistema::valor('posicion_login','centro');
$opacidadLogin=(int)ConfigSistema::valor('opacidad_login',0);
$faviconSistema=ConfigSistema::valor('favicon');
@endphp
<div class="login-container">
    @if(Session::has('success'))
    <div class="alert alert-success">
        {{ Session::get('success') }}
    </div>
    @endif
    @if(Session::has('error'))
    <div class="alert alert-error">
        {{ Session::get('error') }}
    </div>
    @endif
    <div class="form-section">
        <div class="form-container">
            <h1 style="font-size:1.75rem;text-align:center;margin-bottom:1.5rem;color:{{ $colorPrimario }}">{{ $nombreRestaurante }}</h1>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input @error('email') class="is-invalid" @enderror
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Correo Electrónico"
                        required
                        autocomplete="email"
                        autofocus>
                                @error('email')
                        <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                <div class="input-group">
                    <input type="password"
                        @error('password') class="is-invalid" @enderror
                        name="password"
                        id="password"
                        placeholder="Contraseña"
                        required
                        autocomplete="current-password">
                                @error('password')
                        <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                <div class="options-group">
                    <div class="remember-check">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Recordarme</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="login-button">
                    INICIAR SESIÓN
                </button>
            </form>
            <div style="text-align:center;margin-top:1.25rem"><a href="{{ route('cliente.register') }}" class="forgot-link">Registrarse como cliente</a></div>
        </div>
    </div>
</div>

<style>
    .login-container {
        position: relative;
        min-height: 100vh;
        background: url('{{ $imagenLogin ? Storage::url($imagenLogin) : asset('images/secion iniciada.jpg') }}') no-repeat center center;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: {{ ['izquierda'=>'flex-start','centro'=>'center','derecha'=>'flex-end'][$posicionLogin]??'center' }};
        padding: 2rem;
        font-family: '{{ $tipografia }}', sans-serif;
    }

    .login-container::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,{{ $opacidadLogin/100 }}); }

    .form-container h1 { font-family:'{{ $tipografiaTitulos }}',sans-serif; }

    .form-section {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .form-container {
        width: 100%;
        max-width: 400px;
        padding: 2rem;
        background: rgba(253, 251, 248, 0.95);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
    }

    .input-group {
        margin-bottom: 1.25rem;
    }

    .input-group input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
        background: #FDFBF8;
        color: #3F3B3A;
    }

    .input-group input:focus {
        outline: none;
        border-color: #7A5C58;
        box-shadow: 0 0 0 2px rgba(122, 92, 88, 0.2);
    }

    .input-group input::placeholder {
        color: #6B7280;
    }

    .options-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .remember-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .remember-check input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        border: 1px solid rgba(122, 92, 88, 0.4);
        border-radius: 4px;
    }

    .remember-check label {
        color: #4B403B;
        font-size: 0.9rem;
    }

    .forgot-link {
        color: #7A5C58;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s ease;
    }

    .forgot-link:hover {
        color: #5D403D;
        text-decoration: underline;
    }

    .login-button {
        width: 100%;
        padding: 0.875rem;
        background: linear-gradient(135deg, {{ $colorPrimario }} 0%, {{ $colorSecundario }} 100%);
        border: none;
        border-radius: 8px;
        color: #2F2B27;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
    }

    .login-button:hover {
        background: linear-gradient(135deg, #5D403D 0%, #B59D90 100%);
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(47, 43, 39, 0.25);
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    @media (max-width: 768px) {
        .login-container {
            justify-content: center;
            padding: 1rem;
        }

        .form-container {
            padding: 1.5rem;
            margin: 1rem;
        }
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
</style>
@endsection
