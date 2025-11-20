<?php

use Livewire\Volt\Component;

?>

<div>
    <div class="dropdown notification-wrapper">
        <button class="dropdown-toggle bg-transparent border-0 position-relative" type="button" id="notifications"
            data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-bell-fill"
                viewBox="0 0 16 16">
                <path
                    d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901" />
            </svg>
            <span class="notification-badge"><?php echo e($user->unreadNotifications->count()); ?></span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notifications" style="width: 320px; max-height: 400px; overflow-y: auto;">
            <!--[if BLOCK]><![endif]--><?php if($notifications->isEmpty()): ?>
                <li class="dropdown-item text-center text-muted">No tienes notificaciones nuevas</li>
            <?php else: ?>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="dropdown-item <?php echo e(is_null($notification->read_at) ? 'bg-light fw-bold' : 'text-muted'); ?>">
                        <a href="#" class="d-block text-decoration-none text-dark" wire:click.prevent="markAsRead('<?php echo e($notification->id); ?>')">
                            <div style="white-space: normal; word-break: break-word;">
                                <?php echo e($notification->data['message'] ?? 'Sin mensaje'); ?>

                            </div>
                            <small class="text-muted d-block mt-1">
                                <?php echo e($notification->created_at->diffForHumans()); ?>

                                <!--[if BLOCK]><![endif]--><?php if(is_null($notification->read_at)): ?>
                                    · <span class="badge bg-primary">Nuevo</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </small>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </ul>
    </div>
</div><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views\livewire/notificaciones.blade.php ENDPATH**/ ?>