<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="<?php echo e(asset('css/lineicons.css')); ?>" />
    <script src="https://cdn.jsdelivr.net/npm/inferencejs@1.0.11"></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')('resources/sass/app.scss'); ?>

    <style>
        /* Estilos del Sidebar */
        .sidebar-nav-wrapper {
            background-color: #4a1c1c !important;
            color: white;
        }

        .sidebar-nav ul li a {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }

        .sidebar-nav ul li a:hover,
        .sidebar-nav ul li.active a {
            background: rgba(255, 255, 255, 0.1);
            color: #ff6633 !important;
            border-radius: 5px;
        }

        .sidebar-nav ul li a .icon i {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .sidebar-nav ul li a:hover .icon i,
        .sidebar-nav ul li.active a .icon i {
            color: #ff6633;
        }

        .sidebar-nav ul li a .text {
            font-weight: 500;
        }

        /* Estilos del Header */
        .header {
            background: #4a1c1c !important;
        }

        /* Estilos globales para botones */
        .btn,
        .btn-primary,
        button[type="submit"],
        .custom-button,
        .main-btn {
            background: #4a1c1c !important;
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
            background: #3a1515 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 28, 28, 0.2) !important;
        }

        /* Cambiar la franja azul por un color que combine */
        .page-title,
        .card-header,
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            border-color: #4a1c1c !important;
        }

        .nav-tabs .nav-link:hover {
            border-color: #3a1515 !important;
        }

        /* Estilo para enlaces y elementos activos */
        .active,
        .nav-link.active,
        .page-item.active .page-link {
            background-color: #4a1c1c !important;
            border-color: #4a1c1c !important;
            color: white !important;
        }

        /* Estilos para elementos de navegación */
        .nav-link:hover,
        .page-link:hover {
            color: #4a1c1c !important;
        }

        /* Estilos para elementos de acción */
        .action-button,
        .create-button,
        .edit-button,
        .delete-button {
            background: #4a1c1c !important;
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
            background: #3a1515 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 28, 28, 0.2) !important;
        }

        /* Estilos para badges y elementos de estado */
        .badge-primary,
        .status-badge {
            background: #4a1c1c !important;
            color: white !important;
        }

        /* Estilos para elementos de progreso */
        .progress-bar {
            background-color: #4a1c1c !important;
        }

        /* Estilos para elementos de selección */
        .form-check-input:checked {
            background-color: #4a1c1c !important;
            border-color: #4a1c1c !important;
        }

        /* Estilos para tooltips y popovers */
        .tooltip-inner,
        .popover-header {
            background-color: #4a1c1c !important;
            color: white !important;
        }

        /* Estilos para elementos de alerta y notificación */
        .alert-primary {
            background-color: rgba(255, 102, 51, 0.1) !important;
            border-color: #ff6633 !important;
            color: #ff6633 !important;
        }

        /* Estilos para elementos de tabla */
        .table .thead-primary th {
            background-color: #4a1c1c !important;
            color: white !important;
        }

        /* Estilos globales para todas las páginas */
        .section {
            background-color: #f8f9fa;
            min-height: calc(100vh - 70px);
            padding: 20px 0;
        }

        /* Títulos de página */
        h2 {
            color: #4a1c1c;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        /* Tarjetas y contenedores */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background: white !important;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
            font-weight: 600;
            color: #4a1c1c;
        }

        .card-body {
            padding: 20px;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: #f8f9fa;
            color: #4a1c1c;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }

        /* Formularios */
        .form-control {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #ff6633;
            box-shadow: 0 0 0 2px rgba(255, 102, 51, 0.2);
        }

        label {
            color: #4a1c1c;
            font-weight: 500;
            margin-bottom: 6px;
        }

        /* Mensajes de alerta */
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #ecfdf5;
            border-color: #059669;
            color: #059669;
        }

        .alert-danger {
            background-color: #fef2f2;
            border-color: #dc2626;
            color: #dc2626;
        }

        /* Paginación */
        .pagination {
            margin-top: 20px;
        }

        .page-link {
            color: #4a1c1c;
            border: 1px solid #e5e7eb;
            margin: 0 2px;
        }

        .page-item.active .page-link {
            background-color: #ff6633;
            border-color: #ff6633;
        }

        /* Dropdown del perfil */
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

        .navbar-logo .logo-icon {
            font-size: 24px;
            margin-right: 10px;
        }

        .navbar-logo .brand-text {
            color: #ff6633;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Arial', sans-serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .navbar-logo .logo-text:hover .brand-text {
            color: #ff8855;
            transform: scale(1.05);
        }

        .navbar-logo .logo-text:hover .logo-icon {
            transform: rotate(15deg);
        }

        .profile-box button {
            color: white !important;
        }

        .profile-box .info h6 {
            color: white !important;
        }

        .dropdown-menu {
            background: white;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu li a {
            color: #4a1c1c !important;
            transition: all 0.3s ease;
            padding: 8px 16px;
        }

        .dropdown-menu li a:hover {
            background: #ff6633;
            color: white !important;
        }

        .dropdown-menu li a i {
            color: #4a1c1c;
            margin-right: 8px;
        }

        .dropdown-menu li a:hover i {
            color: white;
        }

        /* Responsive */
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
            background-color: red !important;
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
            <a href="<?php echo e(route('home')); ?>" class="logo-text">
                <span class="logo-icon">🔥</span>
                <span class="brand-text">Las Brazas</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <?php echo $__env->make('layouts.navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
                                    <i class="lni lni-chevron-left me-2"></i> <?php echo e(__('Menu')); ?>

                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-7 col-6">
                        <div class="header-right">
                            <!-- Boton de notificaciones -->
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notificaciones');

$__html = app('livewire')->mount($__name, $__params, 'lw-1767963441-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

                            <!-- profile start -->
                            <div class="profile-box ml-15">
                                <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="profile-info">
                                        <div class="info">
                                            <h6><?php echo e(Auth::user()->name); ?></h6>
                                        </div>
                                    </div>
                                    <i class="lni lni-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
                                    <li>
                                        <a href="<?php echo e(route('profile.show')); ?>"> <i class="lni lni-user"></i>
                                            <?php echo e(__('My profile')); ?></a>
                                    </li>
                                    <li>
                                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <a href="<?php echo e(route('logout')); ?>"
                                                onclick="event.preventDefault(); this.closest('form').submit();"> <i
                                                    class="lni lni-exit"></i> <?php echo e(__('Logout')); ?></a>
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
                <?php echo $__env->yieldContent('content'); ?>
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
                                Sistema de Gestión - Las Brazas Restaurant © <?php echo e(date('Y')); ?>

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
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo app('Illuminate\Foundation\Vite')('resources/js/app.js'); ?>
    <script src="<?php echo e(asset('js/main.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views/layouts/app.blade.php ENDPATH**/ ?>