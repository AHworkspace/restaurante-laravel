<?php
use Illuminate\Support\Facades\Storage;
?>

<?php $__env->startSection('content'); ?>
    <div class="menu-background-wrapper">
        <!-- ========== title-wrapper start ========== -->
        <div class="title-wrapper pt-30 text-center">
            <h2 class="page-title"><?php echo e(__('Menu Principal')); ?></h2>
        </div>
        <!-- ========== title-wrapper end ========== -->
        <div class="container mt-4">
        <div class="row">
          <?php $__currentLoopData = $platos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plato): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4 mb-4">
              <div class="card h-100 shadow-sm">
                <?php
                    $imagenUrl = asset('images/recetas.jpg');
                    if ($plato->imagen) {
                        $rutaArchivo = storage_path('app/public/' . $plato->imagen);
                        if (file_exists($rutaArchivo)) {
                            $imagenUrl = asset('storage/' . $plato->imagen);
                        }
                    }
                ?>
                <img src="<?php echo e($imagenUrl); ?>" alt="<?php echo e($plato->nombre); ?>">
                <div class="card-body">
                  <h5 class="card-title" style="color:#4a1c1c;"><?php echo e($plato->nombre); ?></h5>
                  <p class="card-text"><?php echo e($plato->descripcion ?: $plato->indicaciones); ?></p>
                  <p class="text-muted fw-bold">Precio: $<?php echo e(number_format($plato->precio, 2)); ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    </div>

    <style>
        .menu-background-wrapper {
            position: relative;
            min-height: calc(100vh - 100px);
            background: url('<?php echo e(asset('images/tablero menu.jpg')); ?>') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            padding: 20px 0;
        }

        .menu-background-wrapper > * {
            position: relative;
            z-index: 1;
        }

        .card {
            background: rgba(253, 251, 248, 0.98) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .page-title {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            color: #FFFFFF;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/inferencejs"></script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/game.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/home.blade.php ENDPATH**/ ?>