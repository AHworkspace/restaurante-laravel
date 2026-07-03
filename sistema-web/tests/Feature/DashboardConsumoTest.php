<?php

namespace Tests\Feature;

use App\Models\Consumidor;
use App\Models\MenuDia;
use App\Models\User;
use App\Models\Insumo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use App\Models\MovimientoInventario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardConsumoTest extends TestCase
{
    use DatabaseTransactions;

    public function test_consumption_form_loads_successfully(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();

        $this->actingAs($user)->get(route('consumos.create'))
            ->assertOk()
            ->assertSee('Registrar consumo')
            ->assertSee('mostrar todos los platos');
    }

    public function test_dashboard_registers_public_sale_without_consumption(): void
    {
        [$user, $menu, $receta] = $this->dashboardData();
        $consumosAntes = \App\Models\Consumo::count();

        $response = $this->actingAs($user)->post(route('consumos.store'), $this->payload($menu, $receta));

        $response->assertRedirect(route('ventas.index'));
        $this->assertDatabaseHas('ventas', ['receta_id' => $receta->id, 'consumidor_id' => null]);
        $this->assertSame($consumosAntes, \App\Models\Consumo::count());
    }

    public function test_dashboard_registers_sale_and_pending_consumption_for_customer(): void
    {
        [$user, $menu, $receta] = $this->dashboardData();
        $consumidor = Consumidor::where('activo', true)->firstOrFail();
        $payload = $this->payload($menu, $receta) + ['consumidor_id' => $consumidor->id];

        $response = $this->actingAs($user)->post(route('consumos.store'), $payload);

        $response->assertRedirect(route('consumos.index'));
        $this->assertDatabaseHas('ventas', ['receta_id' => $receta->id, 'consumidor_id' => $consumidor->id]);
        $this->assertDatabaseHas('consumos', ['receta_id' => $receta->id, 'consumidor_id' => $consumidor->id, 'estado_pago' => 'pendiente']);
        $registro = \Illuminate\Support\Facades\DB::table('historial')
            ->where('seccion', 'Consumos')->where('accion', 'Registró consumo y venta')->latest('id')->first();
        $this->assertNotNull($registro);
        $this->assertStringContainsString($consumidor->nombre_completo, $registro->detalles);
        $this->assertStringContainsString($receta->nombre, $registro->detalles);
    }

    public function test_dashboard_rejects_quantities_above_menu_stock(): void
    {
        [$user, $menu, $receta] = $this->dashboardData();
        $disponible = (int) $receta->pivot->cantidad;
        $ventasAntes = \App\Models\Venta::count();
        $payload = $this->payload($menu, $receta);
        $payload['items'] = json_encode([[
            'receta_id' => $receta->id,
            'cantidad' => $disponible + 1,
            'menu_dia_id' => $menu->id,
        ]]);

        $this->actingAs($user)->post(route('consumos.store'), $payload)->assertStatus(422);

        $this->assertSame($ventasAntes, \App\Models\Venta::count());
        $this->assertSame($disponible, (int) $menu->fresh()->recetas()->where('receta_id', $receta->id)->first()->pivot->cantidad);
    }

    public function test_consumption_screen_requires_customer_and_registers_each_dish_separately(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumidor = Consumidor::where('activo', true)->firstOrFail();
        $recetas = \App\Models\Receta::orderBy('id')->limit(2)->get();
        $this->assertCount(2, $recetas);
        $payload = [
            'origen' => 'consumos',
            'fecha_consumo' => now()->toDateString(),
            'hora_consumo' => now()->format('H:i'),
            'items' => $recetas->map(fn ($receta) => [
                'receta_id' => $receta->id,
                'cantidad' => 1,
                'menu_dia_id' => null,
            ])->toJson(),
        ];

        $this->actingAs($user)->post(route('consumos.store'), $payload)
            ->assertSessionHasErrors('consumidor_id');

        $response = $this->actingAs($user)->post(route('consumos.store'), $payload + [
            'consumidor_id' => $consumidor->id,
        ]);

        $response->assertRedirect(route('consumos.index'));
        foreach ($recetas as $receta) {
            $this->assertDatabaseHas('consumos', ['consumidor_id' => $consumidor->id, 'receta_id' => $receta->id]);
            $this->assertDatabaseHas('ventas', ['consumidor_id' => $consumidor->id, 'receta_id' => $receta->id]);
        }
    }

    public function test_direct_ingredient_is_sold_from_menu_and_discounted_from_inventory(): void
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $consumidor = Consumidor::where('activo', true)->firstOrFail();
        $unidad = UnidadMedida::where('nombre', 'Unidad')->firstOrFail();
        $insumo = Insumo::create([
            'nombre' => 'Bebida directa '.uniqid(), 'stock_minimo' => 0,
            'costo_estandar' => 10, 'categoria_id' => Categoria::firstOrFail()->id,
            'unidad_medida_id' => $unidad->id, 'tipo_uso' => 'directo', 'cantidad_base_por_venta' => 1,
        ]);
        $presentacion=$insumo->presentacionPredeterminada()->firstOrFail();
        MovimientoInventario::create(['insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'tipo'=>'entrada','cantidad'=>10,'cantidad_original'=>10,'cantidad_convertida'=>10,'unidad_medida_id'=>$unidad->id,'motivo'=>'Stock prueba']);
        $menu=MenuDia::where('activo',true)->firstOrFail();
        $menu->update(['fecha'=>now()->toDateString(),'visible_para_clientes'=>true,'hora_inicio'=>null,'hora_fin'=>null]);
        $menu->presentacionesDirectas()->attach($presentacion->id,['cantidad_inicial'=>5,'cantidad'=>5,'precio_venta'=>15]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()->assertSee($insumo->nombre)->assertSee('presentacion:'.$presentacion->id, false);

        $response=$this->actingAs($user)->post(route('consumos.store'),[
            'consumidor_id'=>$consumidor->id,'fecha_consumo'=>$menu->fecha->toDateString(),
            'items'=>json_encode([['receta_id'=>'presentacion:'.$presentacion->id,'cantidad'=>2,'menu_dia_id'=>$menu->id]]),
        ]);

        $response->assertRedirect(route('consumos.index'));
        $this->assertDatabaseHas('ventas',['insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'receta_id'=>null,'cantidad'=>2,'total'=>30]);
        $this->assertDatabaseHas('consumos',['insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'receta_id'=>null,'consumidor_id'=>$consumidor->id,'total'=>30]);
        $this->assertEqualsWithDelta(8,$presentacion->fresh()->stockDisponible(),0.0001);
        $this->assertSame(3,(int)$menu->fresh()->presentacionesDirectas()->findOrFail($presentacion->id)->pivot->cantidad);
    }

    private function dashboardData(): array
    {
        $user = User::role(['admin', 'cajero', 'director'])->firstOrFail();
        $menu = MenuDia::with(['recetas' => fn ($q) => $q->wherePivot('cantidad', '>', 1)])
            ->where('activo', true)->whereHas('recetas', fn ($q) => $q->where('cantidad', '>', 1))->firstOrFail();

        return [$user, $menu, $menu->recetas->firstOrFail()];
    }

    private function payload(MenuDia $menu, $receta): array
    {
        return [
            'tipo_comida_id' => $menu->tipo_comida_id,
            'fecha_consumo' => $menu->fecha->toDateString(),
            'items' => json_encode([['receta_id' => $receta->id, 'cantidad' => 1, 'menu_dia_id' => $menu->id]]),
        ];
    }
}
