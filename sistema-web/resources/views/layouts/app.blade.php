<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="{{ asset('css/lineicons.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/inferencejs@1.0.11"></script>
    @livewireStyles
    @vite('resources/sass/app.scss')

    <style>
        :root {
            --color-bg: #F5F1EC;
            --color-surface: #FFFFFF;
            --color-sidebar: #3F3B3A;
            --color-sidebar-accent: #6F4E37;
            --color-primary: #7A5C58;
            --color-primary-dark: #5D403D;
            --color-secondary: #CDBEAC;
            --color-soft-beige: #E7DED3;
            --color-text: #2F2B27;
            --color-muted: #8A8078;
            --color-highlight: #9F7A73;
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text);
        }

        /* Estilos del Sidebar */
        .sidebar-nav-wrapper {
            background: linear-gradient(180deg, var(--color-sidebar), #4F4A47) !important;
            color: white;
        }

        .sidebar-nav ul li a {
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 10px 16px;
            transition: all 0.3s ease;
        }

        .sidebar-nav ul li a:hover,
        .sidebar-nav ul li.active a {
            background: rgba(255, 255, 255, 0.12);
            color: var(--color-secondary) !important;
            border-radius: 6px;
        }

        .sidebar-nav ul li a .icon i {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .sidebar-nav ul li a:hover .icon i,
        .sidebar-nav ul li.active a .icon i {
            color: var(--color-secondary);
        }

        .sidebar-nav ul li a .text {
            font-weight: 500;
        }

        /* Estilos del Header */
        .header {
            background: var(--color-sidebar) !important;
            box-shadow: 0 2px 6px rgba(47, 43, 39, 0.25);
        }

        /* Estilos globales para botones */
        .btn,
        .btn-primary,
        button[type="submit"],
        .custom-button,
        .main-btn {
            background: var(--color-primary) !important;
            border: none !important;
            color: white !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover,
        .btn-primary:hover,
        button[type="submit"]:hover,
        .custom-button:hover,
        .main-btn:hover {
            background: var(--color-primary-dark) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(101, 67, 62, 0.25) !important;
        }

        /* Bordes y tabs */
        .page-title,
        .card-header,
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            border-color: var(--color-primary) !important;
        }

        .nav-tabs .nav-link:hover {
            border-color: var(--color-primary-dark) !important;
        }

        /* Enlaces activos */
        .active,
        .nav-link.active,
        .page-item.active .page-link {
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
            color: white !important;
        }

        .nav-link:hover,
        .page-link:hover {
            color: var(--color-primary) !important;
        }

        /* Botones de acción */
        .action-button,
        .create-button,
        .edit-button,
        .delete-button {
            background: var(--color-primary) !important;
            color: white !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 6px !important;
            transition: all 0.3s ease !important;
        }

        .action-button:hover,
        .create-button:hover,
        .edit-button:hover,
        .delete-button:hover {
            background: var(--color-primary-dark) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(101, 67, 62, 0.25) !important;
        }

        /* Badges */
        .badge-primary,
        .status-badge {
            background: var(--color-highlight) !important;
            color: white !important;
        }

        /* Progress */
        .progress-bar {
            background-color: var(--color-primary) !important;
        }

        /* Inputs seleccionados */
        .form-check-input:checked {
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }

        /* Tooltips */
        .tooltip-inner,
        .popover-header {
            background-color: var(--color-primary) !important;
            color: white !important;
        }

        /* Alertas */
        .alert-primary {
            background-color: rgba(154, 121, 114, 0.1) !important;
            border-color: var(--color-highlight) !important;
            color: var(--color-primary) !important;
        }

        /* Tablas */
        .table .thead-primary th {
            background-color: var(--color-primary) !important;
            color: white !important;
        }

        .section {
            background-color: var(--color-bg);
            min-height: calc(100vh - 70px);
            padding: 20px 0;
        }

        h2 {
            color: var(--color-primary);
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .card {
            background: var(--color-surface);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(47, 43, 39, 0.08);
            margin-bottom: 20px;
            border: 1px solid rgba(138, 128, 120, 0.15);
        }

        .card-header {
            background: var(--color-surface) !important;
            border-bottom: 1px solid rgba(138, 128, 120, 0.25);
            padding: 15px 20px;
            font-weight: 600;
            color: var(--color-primary);
        }

        .card-body {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: var(--color-soft-beige);
            color: var(--color-primary);
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid rgba(138, 128, 120, 0.25);
        }

        td {
            padding: 12px;
            border-bottom: 1px solid rgba(138, 128, 120, 0.15);
            color: var(--color-text);
        }

        .form-control {
            border: 1px solid rgba(138, 128, 120, 0.5);
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.3s ease;
            background-color: var(--color-surface);
        }

        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(122, 92, 88, 0.2);
        }

        label {
            color: var(--color-muted);
            font-weight: 500;
            margin-bottom: 6px;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #edf8f2;
            border-color: #4f9d7a;
            color: #306b4f;
        }

        .alert-danger {
            background-color: #fdecec;
            border-color: #d26a6a;
            color: #a83e3e;
        }

        .pagination {
            margin-top: 20px;
        }

        .page-link {
            color: var(--color-primary);
            border: 1px solid rgba(138, 128, 120, 0.5);
            margin: 0 2px;
        }

        .page-item.active .page-link {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }

        .navbar-logo {
            padding: 20px;
            text-align: center;
        }

        .navbar-logo .logo-text {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .navbar-logo .brand-text {
            color: transparent !important;
            -webkit-text-stroke: 1px #FFFFFF;
            text-stroke: 1px #FFFFFF;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Arial', sans-serif;
            -webkit-text-fill-color: transparent;
        }

        .navbar-logo .logo-text:hover .brand-text {
            -webkit-text-stroke: 1px #FFFFFF;
            text-stroke: 1px #FFFFFF;
            transform: scale(1.05);
        }

        .profile-box button {
            color: white !important;
        }

        .profile-box .info h6 {
            color: white !important;
        }

        .dropdown-menu {
            background: var(--color-surface);
            border: none;
            box-shadow: 0 8px 24px rgba(47, 43, 39, 0.12);
        }

        .dropdown-menu li a {
            color: var(--color-primary) !important;
            transition: all 0.3s ease;
            padding: 8px 16px;
        }

        .dropdown-menu li a:hover {
            background: var(--color-soft-beige);
            color: var(--color-primary-dark) !important;
        }

        .dropdown-menu li a i {
            color: var(--color-primary);
            margin-right: 8px;
        }

        .dropdown-menu li a:hover i {
            color: var(--color-primary-dark);
        }

        @media (max-width: 768px) {
            .section {
                padding: 15px;
            }

            .card {
                margin-bottom: 15px;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }

        .notification-wrapper {
            position: relative;
            display: inline-block;
        }

        .notification-badge {
            position: absolute;
            top: 16px !important;
            right: 5px !important;
            background-color: #d9534f !important;
            color: white;
            font-size: 0.6rem;
            padding: 2px 5px;
            border-radius: 999px;
            font-weight: bold;
            line-height: 1;
            min-width: 16px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
        <div class="navbar-logo">
            <a href="{{ route('home') }}" class="logo-text">
                <span class="brand-text">LAS BRAZAS</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            @include('layouts.navigation')
        </nav>
    </aside>
    <div class="overlay"></div>
    <!-- ======== sidebar-nav end =========== -->

    <!-- ======== main-wrapper start =========== -->
    <main class="main-wrapper">
        <!-- ========== header start ========== -->
        <header class="header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-5 col-md-5 col-6">
                        <div class="header-left d-flex align-items-center">
                            <div class="menu-toggle-btn mr-20">
                                <button id="menu-toggle" class="main-btn primary-btn btn-hover">
                                    <i class="lni lni-chevron-left me-2"></i> {{ __('Menu') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-7 col-6">
                        <div class="header-right">
                            <!-- Boton de notificaciones -->
                            @livewire('notificaciones')

                            <!-- profile start -->
                            <div class="profile-box ml-15">
                                <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="profile-info">
                                        <div class="info">
                                            <h6>{{ Auth::user()->name }}</h6>
                                        </div>
                                    </div>
                                    <i class="lni lni-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
                                    <li>
                                        <a href="{{ route('profile.show') }}"> <i class="lni lni-user"></i>
                                            {{ __('My profile') }}</a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <a href="{{ route('logout') }}"
                                                onclick="event.preventDefault(); this.closest('form').submit();"> <i
                                                    class="lni lni-exit"></i> {{ __('Logout') }}</a>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <!-- profile end -->
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- ========== header end ========== -->

        <!-- ========== section start ========== -->
        <section class="section">
            <div class="container-fluid">
                @yield('content')
            </div>
            <!-- end container -->
        </section>
        <!-- ========== section end ========== -->

        <!-- ========== footer start =========== -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 order-last order-md-first">
                        <div class="copyright text-md-start">
                            <p class="text-sm">
                                Sistema de Gestión - Las Brazas Restaurant © {{ date('Y') }}
                            </p>
                        </div>
                    </div>
                    <!-- end col-->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </footer>
        <!-- ========== footer end =========== -->
    </main>
    <!-- ======== main-wrapper end =========== -->

    <!-- ========= All Javascript files linkup ======== -->
    @livewireScripts
    @vite('resources/js/app.js')
    <script src="{{ asset('js/main.js') }}"></script>
    @stack('modals')
    @yield('scripts')
</body>

</html>
