<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $nombre = $user
            ? trim(collect([$user->nombre ?? $user->name ?? null, $user->apellido_paterno ?? null])->filter()->implode(' '))
            : 'Sistema';
        try {
            DB::table('historial')->insert([
                'user_id' => $user?->id,
                'usuario' => $nombre ?: ($user?->email ?? 'Sistema'),
                'rol' => $rol,
                'fecha' => now()->toDateString(),
                'hora' => now()->toTimeString(),
                'seccion' => $seccion ?: 'Sistema',
                'accion' => trim((string) $accion),
                'detalles' => $detalles ? trim((string) $detalles) : 'Sin detalles adicionales.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar historial: ' . $e->getMessage());
        }
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
