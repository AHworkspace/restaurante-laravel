<?php
namespace App\Livewire\Forms;
use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use App\Livewire\Forms\PostForm;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

new class extends Component {
    //
    use WithPagination;

    #[Validate('required|min:5')]
    public $password = '';
    public $password_confirmation = '';

    public $userRole = null;
    public $usuarioSeleccionado;

    // Propiedades para cambiar contraseña
    public $passwordUserId = null;
    public $newPassword = '';
    public $newPasswordConfirmation = '';

    // Propiedades para editar usuario
    public $editUserId = null;
    public $editNombre = '';
    public $editApellidoPaterno = '';
    public $editApellidoMaterno = '';
    public $editEmail = '';

    public function with(): array
    {
        return [
            'users' => User::paginate(),
            'roles' => Role::all(),
        ];
    }

    public function save()
    {
        $this->validate();
    }

    public function seleccionarUsuario($userId)
    {
        $this->usuarioSeleccionado = $userId;
    }

    public function prepararCambiarPassword($userId)
    {
        $this->passwordUserId = $userId;
        $this->reset(['newPassword', 'newPasswordConfirmation']);
    }

    public function cambiarContraseña()
    {
        // Validar que las contraseñas coincidan y tengan mínimo 8 caracteres
        $this->validate([
            'newPassword' => 'required|min:8',
            'newPasswordConfirmation' => 'required|same:newPassword',
        ], [
            'newPassword.required' => 'La contraseña es obligatoria.',
            'newPassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'newPasswordConfirmation.required' => 'Debe confirmar la contraseña.',
            'newPasswordConfirmation.same' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::find($this->passwordUserId);

        if (!$user) {
            session()->flash('error', 'Usuario no encontrado.');
            return;
        }

        // Actualizar la contraseña
        $user->password = bcrypt($this->newPassword);
        $user->save();

        // Registrar en historial
        \App\Helpers\HistorialHelper::registrar(
            'Cambió contraseña de usuario',
            'Usuario: ' . $user->email,
            'Usuarios'
        );

        session()->flash('message', 'Contraseña actualizada correctamente.');

        // Limpiar los campos
        $this->reset(['passwordUserId', 'newPassword', 'newPasswordConfirmation']);
    }

    public function asignarRol()
    {
        // Validación básica
        if (!$this->usuarioSeleccionado || !$this->userRole) {
            //$this->dispatchBrowserEvent('mostrar-error', ['message' => 'Debe seleccionar un usuario y un rol.']);
            return;
        }

        $user = User::find($this->usuarioSeleccionado);

        if (!$user) {
            //$this->dispatchBrowserEvent('mostrar-error', ['message' => 'Usuario no encontrado.']);
            return;
        }

        // Reemplazar rol existente por el nuevo
        $user->syncRoles([$this->userRole]);

        // Cerrar modal vía JS
        //$this->dispatchBrowserEvent('cerrar-modal');

        // Mensaje flash opcional
        session()->flash('message', 'Rol asignado correctamente.');

        // Limpiar datos
        $this->reset(['usuarioSeleccionado', 'userRole']);

        // Opcional: recargar usuarios si los tienes en una propiedad $users
        // $this->cargarUsuarios();
    }

    // ============================================
    // MÉTODOS PARA EDITAR USUARIO
    // ============================================
    public function prepararEditar($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $this->editUserId = $user->id;
            $this->editNombre = $user->nombre;
            $this->editApellidoPaterno = $user->apellido_paterno;
            $this->editApellidoMaterno = $user->apellido_materno;
            $this->editEmail = $user->email;
        }
    }

    public function editarUsuario()
    {
        $this->validate([
            'editNombre' => 'required|string|max:255',
            'editApellidoPaterno' => 'required|string|max:255',
            'editApellidoMaterno' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255|unique:users,email,' . $this->editUserId,
        ], [
            'editNombre.required' => 'El nombre es obligatorio.',
            'editApellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'editApellidoMaterno.required' => 'El apellido materno es obligatorio.',
            'editEmail.required' => 'El email es obligatorio.',
            'editEmail.email' => 'El email no es válido.',
            'editEmail.unique' => 'Este email ya está registrado.',
        ]);

        $user = User::find($this->editUserId);

        if (!$user) {
            session()->flash('error', 'Usuario no encontrado.');
            return;
        }

        $user->nombre = $this->editNombre;
        $user->apellido_paterno = $this->editApellidoPaterno;
        $user->apellido_materno = $this->editApellidoMaterno;
        $user->email = $this->editEmail;
        $user->save();

        \App\Helpers\HistorialHelper::registrar(
            'Actualizó datos de usuario',
            'Usuario: ' . $user->email,
            'Usuarios'
        );

        session()->flash('message', 'Usuario actualizado correctamente.');

        $this->reset(['editUserId', 'editNombre', 'editApellidoPaterno', 'editApellidoMaterno', 'editEmail']);
    }

    // ============================================
    // MÉTODOS PARA ELIMINAR USUARIO
    // ============================================
    public $usuarioAEliminar = null;

    public function prepararEliminar($userId)
    {
        $this->usuarioAEliminar = $userId;
    }

    public function eliminarUsuario()
    {
        $user = User::find($this->usuarioAEliminar);

        if (!$user) {
            session()->flash('error', 'Usuario no encontrado.');
            return;
        }

        $email = $user->email;

        // Eliminar el usuario
        $user->delete();

        \App\Helpers\HistorialHelper::registrar(
            'Eliminó usuario',
            'Usuario: ' . $email,
            'Usuarios'
        );

        session()->flash('message', 'Usuario eliminado correctamente.');
        $this->reset('usuarioAEliminar');
    }
};
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
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <p>{{ $user->nombre }}</p>
                        </td>
                        <td>
                            <p>{{ $user->apellido_paterno }} {{ $user->apellido_materno }}</p>
                        </td>
                        <td>
                            <p>{{ $user->email }}</p>
                        </td>
                        <td>
                            <p>{{ $user->roles->first()->name ?? 'Sin rol' }}</p>
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
                                            wire:click.prevent="prepararCambiarPassword({{ $user->id }})">
                                            Cambiar Contraseña
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalAsignarRol"
                                            wire:click.prevent="seleccionarUsuario({{ $user->id }})">
                                            Asignar Rol
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario"
                                            wire:click.prevent="prepararEditar({{ $user->id }})">
                                            Editar Datos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                            data-bs-target="#modalEliminar"
                                            wire:click.prevent="prepararEliminar({{ $user->id }})">
                                            Eliminar
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <!-- end table row -->
            </tbody>
        </table>
        <!-- end table -->
        {{ $users->links() }}
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
                            @error('newPassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" wire:model="newPasswordConfirmation" class="form-control" required>
                            @error('newPasswordConfirmation') <span class="text-danger">{{ $message }}</span> @enderror
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
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
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
                            @error('editNombre') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" wire:model="editApellidoPaterno" class="form-control" required>
                            @error('editApellidoPaterno') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" wire:model="editApellidoMaterno" class="form-control" required>
                            @error('editApellidoMaterno') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="editEmail" class="form-control" required>
                            @error('editEmail') <span class="text-danger">{{ $message }}</span> @enderror
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

</div>
