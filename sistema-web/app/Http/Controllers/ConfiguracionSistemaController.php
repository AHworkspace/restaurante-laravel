<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ConfiguracionSistemaController extends Controller
{
    public function roles()
    {
        return view('configuraciones.roles', ['roles' => Role::withCount('users')->where('name', '!=', 'cliente')->orderBy('name')->get()]);
    }

    public function guardarRol(Request $request)
    {
        $datos = $request->validate(['name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_ -]+$/i', Rule::notIn(['admin', 'cliente']), Rule::unique('roles', 'name')]]);
        Role::create(['name' => trim($datos['name']), 'guard_name' => 'web']);
        return back()->with('success', 'Rol creado correctamente.');
    }

    public function actualizarRol(Request $request, Role $role)
    {
        abort_if(in_array($role->name, ['admin', 'cliente'], true), 422, 'Este rol esta protegido.');
        $datos = $request->validate(['name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_ -]+$/i', Rule::unique('roles', 'name')->ignore($role->id)]]);
        $role->update(['name' => trim($datos['name'])]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return back()->with('success', 'Nombre del rol actualizado.');
    }

    public function permisos(Request $request)
    {
        $roles = Role::where('name', '!=', 'cliente')->orderBy('name')->get();
        $role = $roles->firstWhere('id', $request->integer('role_id')) ?? $roles->first();
        return view('configuraciones.permisos', [
            'roles' => $roles,
            'role' => $role,
            'catalogo' => config('system_permissions'),
            'asignados' => $role ? $role->permissions->pluck('name')->all() : [],
        ]);
    }

    public function guardarPermisos(Request $request, Role $role)
    {
        abort_if($role->name === 'admin', 422, 'Los permisos del administrador estan protegidos.');
        $validos = $this->nombresPermisos();
        $datos = $request->validate(['permisos' => ['nullable', 'array'], 'permisos.*' => ['string', Rule::in($validos)]]);
        $role->syncPermissions($datos['permisos'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('configuraciones.permisos', ['role_id' => $role->id])->with('success', 'Permisos actualizados.');
    }

    public function diseno()
    {
        return view('configuraciones.diseno');
    }

    public function guardarDiseno(Request $request)
    {
        $datos = $request->validate([
            'nombre_restaurante' => ['required', 'string', 'max:100'],
            'descripcion_restaurante' => ['nullable', 'string', 'max:600'],
            'direccion_restaurante' => ['nullable', 'string', 'max:200'],
            'telefono_restaurante' => ['nullable', 'string', 'max:40'],
            'color_primario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_secundario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_fondo' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_sidebar' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_superficie' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_encabezado' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_texto' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_texto_secundario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_borde' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_tabla_cabecera' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_entrada' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_exito' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_peligro' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tipografia' => ['required', Rule::in(['Arial', 'Verdana', 'Tahoma', 'Georgia', 'Trebuchet MS'])],
            'tipografia_titulos' => ['required', Rule::in(['Arial', 'Verdana', 'Tahoma', 'Georgia', 'Trebuchet MS'])],
            'tamano_texto' => ['required', 'integer', 'min:14', 'max:18'],
            'radio_bordes' => ['required', 'integer', 'min:0', 'max:16'],
            'densidad' => ['required', Rule::in(['compacta', 'normal', 'comoda'])],
            'sombra' => ['required', Rule::in(['ninguna', 'suave', 'media'])],
            'estilo_botones' => ['required', Rule::in(['solido', 'contorno'])],
            'tema' => ['required', Rule::in(['claro', 'oscuro', 'personalizado'])],
            'contraste' => ['required', Rule::in(['normal', 'alto'])],
            'posicion_login' => ['required', Rule::in(['izquierda', 'centro', 'derecha'])],
            'opacidad_login' => ['required', 'integer', 'min:0', 'max:90'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'imagen_login' => ['nullable', 'image', 'max:8192'],
            'imagen_portada' => ['nullable', 'image', 'max:8192'],
            'imagen_portal_cliente' => ['nullable', 'image', 'max:8192'],
        ]);

        $imagenes = ['logo', 'favicon', 'imagen_login', 'imagen_portada', 'imagen_portal_cliente'];
        foreach (array_diff(array_keys($datos), $imagenes) as $clave) ConfiguracionSistema::guardar($clave, $datos[$clave]);
        foreach ($imagenes as $campo) {
            if ($request->hasFile($campo)) ConfiguracionSistema::guardar($campo, $request->file($campo)->store('configuraciones', 'public'));
        }
        return back()->with('success', 'Diseno e informacion actualizados.');
    }

    public function restablecerDiseno()
    {
        $claves = [
            'logo','favicon','imagen_login','imagen_portada','imagen_portal_cliente',
            'color_primario','color_secundario','color_fondo','color_sidebar','color_superficie','color_encabezado',
            'color_texto','color_texto_secundario','color_borde','color_tabla_cabecera','color_entrada',
            'color_exito','color_peligro','tipografia','tipografia_titulos','tamano_texto','radio_bordes',
            'densidad','sombra','estilo_botones','tema','contraste','posicion_login','opacidad_login',
        ];
        ConfiguracionSistema::whereIn('clave', $claves)->delete();
        foreach ($claves as $clave) cache()->forget("configuracion_sistema.{$clave}");
        return back()->with('success', 'El diseno original fue restablecido.');
    }

    private function nombresPermisos(): array
    {
        return collect(config('system_permissions'))
            ->flatMap(fn ($subsectores) => collect($subsectores)->flatMap(fn ($permisos) => array_keys($permisos)))
            ->values()->all();
    }
}
