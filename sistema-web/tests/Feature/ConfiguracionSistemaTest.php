<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use App\Models\ConfiguracionSistema;
use Tests\TestCase;

class ConfiguracionSistemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_admin_can_open_configuration_pages(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $this->actingAs($admin)->get(route('configuraciones.roles'))->assertOk();
        $this->actingAs($admin)->get(route('configuraciones.permisos'))->assertOk()
            ->assertSee('Insumos')->assertSee('Categorias de insumos')
            ->assertSee('Unidades de medida')->assertSee('Alertas y notificaciones');
        $this->actingAs($admin)->get(route('configuraciones.permisos'))->assertDontSee('value="'.(Role::where('name', 'cliente')->value('id') ?? 0).'"', false);
        $this->actingAs($admin)->get(route('configuraciones.diseno'))->assertOk();

        $cajero = User::role('cajero')->firstOrFail();
        $this->actingAs($cajero)->get(route('configuraciones.roles'))->assertForbidden();
        $this->assertCount(0, Role::where('name', 'cliente')->firstOrFail()->permissions);
    }

    public function test_new_role_is_limited_by_its_assigned_permissions(): void
    {
        $role = Role::create(['name' => 'rol_prueba_permisos', 'guard_name' => 'web']);
        $role->givePermissionTo(['dashboard.ver', 'consumidores.ver']);
        $user = User::create([
            'nombre' => 'Usuario',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Permisos',
            'email' => 'permisos-'.uniqid().'@example.test',
            'password' => 'password-seguro',
        ]);
        $user->assignRole($role);

        $this->actingAs($user)->get(route('home'))->assertOk();
        $this->actingAs($user)->get(route('consumidores.index'))->assertOk();
        $this->actingAs($user)->get(route('consumidores.create'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos.index'))->assertForbidden();
    }

    public function test_product_subsections_have_independent_permissions(): void
    {
        $role = Role::create(['name' => 'rol_prueba_insumos', 'guard_name' => 'web']);
        $role->givePermissionTo(['dashboard.ver', 'insumos.ver']);
        $user = User::create([
            'nombre' => 'Solo', 'apellido_paterno' => 'Insumos', 'apellido_materno' => 'Prueba',
            'email' => 'insumos-'.uniqid().'@example.test', 'password' => 'password-seguro',
        ]);
        $user->assignRole($role);

        $this->actingAs($user)->get(route('insumos.index'))->assertOk();
        $this->actingAs($user)->get(route('categorias.insumos.index'))->assertForbidden();
        $this->actingAs($user)->get(route('categorias.unidades.index'))->assertForbidden();
        $this->actingAs($user)->get(route('insumos.create'))->assertForbidden();
    }

    public function test_admin_can_save_extended_design_settings(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $datos = [
            'nombre_restaurante' => 'Las Brazas', 'descripcion_restaurante' => 'Restaurante',
            'direccion_restaurante' => 'Direccion', 'telefono_restaurante' => '70000000',
            'color_primario' => '#7A5C58', 'color_secundario' => '#CDBEAC', 'color_fondo' => '#F5F1EC',
            'color_sidebar' => '#3F3B3A', 'color_superficie' => '#FFFFFF', 'color_encabezado' => '#3F3B3A',
            'color_texto' => '#2F2B27', 'color_texto_secundario' => '#8A8078', 'color_borde' => '#D8D0C8',
            'color_tabla_cabecera' => '#E7DED3', 'color_entrada' => '#FFFFFF', 'color_exito' => '#4F9D7A',
            'color_peligro' => '#D26A6A', 'tipografia' => 'Arial', 'tipografia_titulos' => 'Georgia',
            'tamano_texto' => 16, 'radio_bordes' => 8, 'densidad' => 'normal', 'sombra' => 'suave',
            'estilo_botones' => 'solido', 'tema' => 'personalizado', 'contraste' => 'normal',
            'posicion_login' => 'centro', 'opacidad_login' => 20,
        ];

        $this->actingAs($admin)->put(route('configuraciones.diseno.update'), $datos)
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('configuraciones_sistema', ['clave' => 'tipografia_titulos', 'valor' => 'Georgia']);
        $this->assertDatabaseHas('configuraciones_sistema', ['clave' => 'opacidad_login', 'valor' => '20']);
        foreach (array_keys($datos) as $clave) cache()->forget("configuracion_sistema.{$clave}");
    }
}
