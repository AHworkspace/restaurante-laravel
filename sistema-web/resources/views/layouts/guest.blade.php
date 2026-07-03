<!DOCTYPE html>
<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>{{ \App\Models\ConfiguracionSistema::valor('nombre_restaurante','Las Brazas') }}</title>
    @if(\App\Models\ConfiguracionSistema::valor('favicon'))<link rel="icon" href="{{ Storage::url(\App\Models\ConfiguracionSistema::valor('favicon')) }}">@endif
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/lineicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/materialdesignicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}" />
    @vite('resources/sass/app.scss')
    @livewireStyles
</head>
<body>
<div class="row g-0 auth-row">
    @yield('content')
</div>

<!-- Scripts -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/main.js') }}"></script>
@livewireScripts
@vite('resources/js/app.js')
</body>
</html>
