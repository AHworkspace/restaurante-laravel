<?php $__env->startSection('content'); ?>
<div class="login-container">
    <?php if(Session::has('success')): ?>
    <div class="alert alert-success">
        <?php echo e(Session::get('success')); ?>

    </div>
    <?php endif; ?>
    <?php if(Session::has('error')): ?>
    <div class="alert alert-error">
        <?php echo e(Session::get('error')); ?>

    </div>
    <?php endif; ?>
    <div class="form-section">
        <div class="form-container">
            <form action="<?php echo e(route('login')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="input-group">
                    <input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> class="is-invalid" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Correo Electrónico"
                        required
                        autocomplete="email"
                        autofocus>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error-message"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                <div class="input-group">
                    <input type="password"
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> class="is-invalid" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        name="password"
                        id="password"
                        placeholder="Contraseña"
                        required
                        autocomplete="current-password">
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="error-message"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                <div class="options-group">
                    <div class="remember-check">
                        <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                        <label for="remember">Recordarme</label>
                    </div>
                    <?php if(Route::has('password.request')): ?>
                        <a href="<?php echo e(route('password.request')); ?>" class="forgot-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    <?php endif; ?>
                </div>

                <button type="submit" class="login-button">
                    INICIAR SESIÓN
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .login-container {
        position: relative;
        min-height: 100vh;
        background: url('<?php echo e(asset('images/secion iniciada.jpg')); ?>') no-repeat center center;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

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
        background: linear-gradient(135deg, #6F4E37 0%, #CDBEAC 100%);
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\para software2\proyecto-tecnoweb_restaurante\sistema-web\resources\views/auth/login.blade.php ENDPATH**/ ?>