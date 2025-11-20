<?php

use Livewire\Volt\Component;
use App\Models\Receta;
use App\Models\Insumo;
use Illuminate\Support\Facades\Http;

?>

<div>
    <div class="card-styles">
        <div class="card-style-3 mb-30">
            <div class="card-content">
                <div class="alert-box primary-alert">
                    <div class="alert">
                        <p class="text-medium">
                            Predecir la cantidad de insumos que se necesitarán para producir [x] receta en [y] mes.
                        </p>
                    </div>
                </div>

                <div class="row">

                    <div class="col-6">
                        <div class="select-style-1">
                            <label>¿Que receta quieres producir?</label>
                            <div class="select-position">
                                <select wire:model="receta" wire:change="obtenerInsumos">
                                    <option value="">Seleccione una receta</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recetas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recetita): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($recetita->id); ?>"><?php echo e($recetita->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                            </div>
                        </div>

                        <div class="select-style-1">
                            <label>¿Qué insumo quieres predecir?</label>
                            <div class="select-position">
                                <select wire:model="insumo">
                                    <option value="">Seleccione un insumo</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($ins->id); ?>"><?php echo e($ins->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                            </div>
                        </div>

                        <div class="select-style-1">
                            <label>¿En que mes vas a producir?</label>
                            <div class="select-position">
                                <select wire:model="mes">
                                    <option value="">Seleccione un mes</option>
                                    <option value="Enero">Enero</option>
                                    <option value="Febrero">Febrero</option>
                                    <option value="Marzo">Marzo</option>
                                    <option value="Abril">Abril</option>
                                    <option value="Mayo">Mayo</option>
                                    <option value="Junio">Junio</option>
                                    <option value="Julio">Julio</option>
                                    <option value="Agosto">Agosto</option>
                                    <option value="Septiembre">Septiembre</option>
                                    <option value="Octubre">Octubre</option>
                                    <option value="Noviembre">Noviembre</option>
                                    <option value="Diciembre">Diciembre</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="col-6">
                        <div class="input-style-1">
                            <label>Resultado</label>
                            <textarea wire:model="resultado" rows="5" readonly></textarea>
                        </div>

                        <div class="button-group" style="text-align: center;">
                            <button wire:click="predecir" class="main-btn active-btn btn-hover"><i
                                    class="lni lni-android"></i> Predecir</button>

                        </div>

                        <p style="text-align: center;" class="mt-3">Ultimo entreno: 05/02/2025</p>

                    </div>
                </div>


            </div>
        </div>
    </div>
</div><?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views\livewire/prediccionuno.blade.php ENDPATH**/ ?>