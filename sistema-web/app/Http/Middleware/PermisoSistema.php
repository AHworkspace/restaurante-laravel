<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermisoSistema
{
    public function handle(Request $request, Closure $next)
    {
        $ruta = $request->route()?->getName();
        if (! $ruta || str_starts_with($ruta, 'cliente.') || str_starts_with($ruta, 'configuraciones.')) return $next($request);
        $permiso = $this->permisoPara($ruta);
        if (! $permiso) return $next($request);
        if (! auth()->check()) return redirect()->guest(route('login'));
        abort_unless(auth()->user()->can($permiso), 403, 'No tienes permiso para realizar esta accion.');
        return $next($request);
    }

    private function permisoPara(string $ruta): ?string
    {
        if ($ruta === 'home') return 'dashboard.ver';
        if (str_starts_with($ruta, 'users.')) return $this->crud($ruta, 'usuarios');
        if (str_starts_with($ruta, 'consumidores.')) return $this->crud($ruta, 'consumidores');
        if ($ruta === 'categorias.rangos.index') return 'clasificacion_clientes.ver';
        if ($this->empiezaCon($ruta, ['fuerzas.', 'instituciones.', 'grados.'])) return $this->crud($ruta, 'clasificacion_clientes');
        if (str_starts_with($ruta, 'proveedores.')) return $this->crud($ruta, 'proveedores');

        if (str_starts_with($ruta, 'insumos.presentaciones.')) return str_ends_with($ruta,'destroy')?'insumos.eliminar':'insumos.editar';
        if (str_starts_with($ruta, 'insumos.')) return $this->crud($ruta, 'insumos');
        if (str_starts_with($ruta, 'marcas.')) return $this->crud($ruta, 'marcas');
        if (str_starts_with($ruta, 'formatos-empaque.')) return $this->crud($ruta, 'formatos_empaque');
        if (str_starts_with($ruta, 'categorias.unidades.') || str_starts_with($ruta, 'unidades.')) return $this->crud($ruta, 'unidades');
        if (str_starts_with($ruta, 'categorias.insumos.') || str_starts_with($ruta, 'categorias.')) return $this->crud($ruta, 'categorias_insumos');
        if (str_starts_with($ruta, 'notificaciones.')) return 'alertas.ver';

        if (str_starts_with($ruta, 'compras.')) {
            if ($ruta === 'compras.abono-proveedor') return 'compras.abonar';
            return in_array($this->accion($ruta), ['crear'], true) ? 'compras.crear' : 'compras.ver';
        }
        if (str_starts_with($ruta, 'movimientos.')) return $ruta === 'movimientos.guardarSeleccion' ? 'movimientos.crear' : $this->crud($ruta, 'movimientos');

        if (str_starts_with($ruta, 'recetas.')) return $this->crud($ruta, 'recetas');
        if (str_starts_with($ruta, 'tipos-comida.')) return $this->crud($ruta, 'tipos_comida');
        if (str_starts_with($ruta, 'tipos-produccion.')) return $this->crud($ruta, 'tipos_comida');
        if (str_starts_with($ruta, 'menus-dia.')) {
            if ($ruta === 'menus-dia.toggle-visible') return 'menus.publicar';
            if (in_array($ruta, ['menus-dia.update-titulo', 'menus-dia.eliminar-titulo'], true)) return 'menus.editar';
            return $this->crud($ruta, 'menus');
        }
        if (str_starts_with($ruta, 'predicciones.')) return 'predicciones.ver';

        if (str_starts_with($ruta, 'ventas.')) return $this->crud($ruta, 'ventas');
        if (str_starts_with($ruta, 'consumos.')) return $this->crud($ruta, 'consumos');
        if ($ruta === 'pagos.consumos-periodo') return 'pagos.crear';
        if (str_starts_with($ruta, 'pagos.')) return in_array($this->accion($ruta), ['crear'], true) ? 'pagos.crear' : 'pagos.ver';

        if (str_starts_with($ruta, 'reportes.')) {
            if (str_contains($ruta, 'eliminar')) return 'reportes.eliminar';
            if (str_contains($ruta, 'actualizar')) return 'reportes.editar';
            if (str_contains($ruta, 'guardar')) return 'reportes.crear';
            if (str_contains($ruta, 'pdf')) return 'reportes.exportar';
            return 'reportes.ver';
        }
        if (str_starts_with($ruta, 'historial.')) return 'historial.ver';
        return null;
    }

    private function crud(string $ruta, string $base): string
    {
        return $base.'.'.$this->accion($ruta);
    }

    private function accion(string $ruta): string
    {
        $accion = str($ruta)->afterLast('.')->toString();
        return match ($accion) {
            'create', 'store' => 'crear',
            'edit', 'update' => 'editar',
            'destroy' => 'eliminar',
            default => 'ver',
        };
    }

    private function empiezaCon(string $ruta, array $prefijos): bool
    {
        foreach ($prefijos as $prefijo) if (str_starts_with($ruta, $prefijo)) return true;
        return false;
    }
}
