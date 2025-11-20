<?php $__env->startSection('content'); ?>
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
                <img src="<?php echo e($plato->imagen ? asset('storage/' . $plato->imagen) : asset('images/recetas.jpg')); ?>" alt="<?php echo e($plato->nombre); ?>">
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

    <script src="https://cdn.jsdelivr.net/npm/inferencejs"></script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/game.js']); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views/home.blade.php ENDPATH**/ ?>