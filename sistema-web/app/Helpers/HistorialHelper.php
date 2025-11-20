<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class HistorialHelper
{
    public static function registrar($accion, $detalles = null, $seccion = null)
    {
        $user = Auth::user();
        $rol = '';
        if ($user) {
            if (in_array(HasRoles::class, class_uses($user)) && method_exists($user, 'getRoleNames')) {
                $rol = $user->getRoleNames()->first() ?? '';
            } elseif (property_exists($user, 'rol')) {
                $rol = $user->rol;
            }
        }
        DB::table('historial')->insert([
            'user_id' => $user?->id,
            'usuario' => $user->nombre ?? 'Usuario',
            'rol' => $rol,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
            'seccion' => $seccion,
            'accion' => $accion,
            'detalles' => $detalles,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function registrarPrediccion($usuario, $accion, $detalles = null, $seccion = 'Predicciones')
    {
        try {
            $rol = '';
            if (in_array(HasRoles::class, class_uses($usuario)) && method_exists($usuario, 'getRoleNames')) {
                $rol = $usuario->getRoleNames()->first() ?? 'Sin rol';
            } elseif (property_exists($usuario, 'rol')) {
                $rol = $usuario->rol;
            }

            \App\Models\Historial::create([
                'usuario' => $usuario->name ?? $usuario->nombre ?? 'Usuario',
                'rol' => $rol,
                'accion' => $accion,
                'detalles' => $detalles,
                'fecha' => now()->format('Y-m-d'),
                'hora' => now()->format('H:i:s'),
                'seccion' => $seccion
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al registrar historial de predicción: ' . $e->getMessage());
        }
    }
}
