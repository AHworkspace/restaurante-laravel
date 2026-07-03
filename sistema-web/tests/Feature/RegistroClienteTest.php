<?php

namespace Tests\Feature;

use App\Models\Consumidor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistroClienteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_create_account_without_becoming_internal_user(): void
    {
        $usuariosAntes = User::count();
        $response = $this->post(route('cliente.register.store'), [
            'nombre_completo' => 'Cliente Registro Web',
            'ci' => 'WEB-CI-'.uniqid(),
            'email' => 'cliente-'.uniqid().'@example.test',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ]);

        $consumidor = Consumidor::where('nombre_completo', 'Cliente Registro Web')->firstOrFail();
        $response->assertRedirect(route('cliente.inicio'));
        $this->assertAuthenticatedAs($consumidor, 'cliente');
        $this->assertTrue($consumidor->activo);
        $this->assertTrue(Hash::check('clave-segura-123', $consumidor->password));
        $this->assertSame($usuariosAntes, User::count());
    }

    public function test_customer_registration_rejects_duplicate_identity(): void
    {
        $existente = Consumidor::whereNotNull('email')->firstOrFail();
        $this->post(route('cliente.register.store'), [
            'nombre_completo' => 'Duplicado', 'ci' => $existente->ci, 'email' => $existente->email,
            'password' => 'clave-segura-123', 'password_confirmation' => 'clave-segura-123',
        ])->assertSessionHasErrors(['ci', 'email']);
    }

    public function test_customer_can_use_the_same_login_form_as_staff(): void
    {
        $consumidor = Consumidor::create([
            'nombre_completo' => 'Cliente Login Unificado', 'ci' => 'LOGIN-'.uniqid(),
            'email' => 'login-'.uniqid().'@example.test', 'password' => 'clave-segura-123',
            'codigo_unico' => 'LOGIN-'.uniqid(), 'activo' => true,
        ]);

        $this->post('/login', ['email' => $consumidor->email, 'password' => 'clave-segura-123'])
            ->assertRedirect(route('cliente.inicio'));
        $this->assertAuthenticatedAs($consumidor, 'cliente');
        $this->assertGuest('web');

        $this->get(route('cliente.inicio'))->assertOk()
            ->assertSee('Menú del día')
            ->assertDontSee('Registrar Consumo')
            ->assertDontSee('class="plato-data-disponible"', false)
            ->assertDontSee('Quedan ');
        $this->get(route('cliente.consumos'))->assertOk()->assertSee('Mis consumos');
        $this->get(route('cliente.pagos'))->assertOk()->assertSee('Mis pagos');
    }
}
