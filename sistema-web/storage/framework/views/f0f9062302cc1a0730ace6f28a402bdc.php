<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use App\Livewire\Forms\PostForm;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

?>

<div>
    <div class="table-wrapper table-responsive">
        <table class="table striped-table">
            <thead>
                <tr>
                    <th>
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
                    <th class="text-end">
                        <h6>Acciones</h6>
                    </th>
                </tr>
                <!-- end table row-->
            </thead>
            <tbody>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <p><?php echo e($user->nombre); ?></p>
                        </td>
                        <td>
                            <p><?php echo e($user->apellido_paterno); ?> <?php echo e($user->apellido_materno); ?></p>
                        </td>
                        <td>
                            <p><?php echo e($user->email); ?></p>
                        </td>
                        <td>
                            <p><?php echo e($user->roles->first()->name ?? 'Sin rol'); ?></p>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    ⋮
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalCambiarPassword"
                                            wire:click.prevent="prepararCambiarPassword(<?php echo e($user->id); ?>)">
                                            Cambiar Contraseña
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalAsignarRol"
                                            wire:click.prevent="seleccionarUsuario(<?php echo e($user->id); ?>)">
                                            Asignar Rol
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario"
                                            wire:click.prevent="prepararEditar(<?php echo e($user->id); ?>)">
                                            Editar Datos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalEliminar"
                                            wire:click.prevent="prepararEliminar(<?php echo e($user->id); ?>)">
                                            Eliminar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                <!-- end table row -->
            </tbody>
        </table>
        <!-- end table -->
        <?php echo e($users->links()); ?>

    </div>

    <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form wire:submit.prevent="cambiarContraseña">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" wire:model="newPassword" class="form-control" required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['newPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" wire:model="newPasswordConfirmation" class="form-control" required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['newPasswordConfirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
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


    <div class="modal fade" id="modalAsignarRol" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form wire:submit.prevent="asignarRol">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<!-- ID del usuario -->">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Rol</label>
                            <select class="form-select" wire:model="userRole">
                                <option value="">-- Selecciona un rol --</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->name); ?>"><?php echo e(ucfirst($role->name)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form wire:submit.prevent="editarUsuario">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" wire:model="editNombre" class="form-control" required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['editNombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" wire:model="editApellidoPaterno" class="form-control" required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['editApellidoPaterno'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" wire:model="editApellidoMaterno" class="form-control" required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['editApellidoMaterno'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="editEmail" class="form-control" required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['editEmail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Usuario -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">⚠️ Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>¿Estás seguro de eliminar este usuario?</strong></p>
                    <p class="text-danger mb-0"><i class="lni lni-warning"></i> Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click="eliminarUsuario" data-bs-dismiss="modal">
                        <i class="lni lni-trash-can"></i> Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views\livewire/usuario.blade.php ENDPATH**/ ?>