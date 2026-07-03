<?php

namespace Tests\Feature;

use App\Models\MenuDia;
use App\Models\User;
use App\Models\TipoProduccion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MenuHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_menu_update_records_detailed_history(): void
    {
        $user = User::role(['admin', 'director', 'cocinero'])->firstOrFail();
        $menu = MenuDia::with('recetas')->where('activo', true)->has('recetas')->firstOrFail();
        $historialAntes = count($menu->historial ?? []);
        $cantidades = $menu->recetas->mapWithKeys(fn ($receta) => [
            $receta->id => (int) ($receta->pivot->cantidad_inicial ?? 1),
        ])->all();

        $response = $this->actingAs($user)->put(route('menus-dia.update', $menu), [
            'titulo' => $menu->titulo.' actualizado',
            'tipo_comida_id' => $menu->tipo_comida_id,
            'fecha' => $menu->fecha->toDateString(),
            'hora_inicio' => $menu->hora_inicio ? substr((string) $menu->hora_inicio, 0, 5) : null,
            'hora_fin' => $menu->hora_fin ? substr((string) $menu->hora_fin, 0, 5) : null,
            'descripcion' => $menu->descripcion,
            'visible_para_clientes' => $menu->visible_para_clientes ? '1' : null,
            'visible_en_horario' => $menu->visible_en_horario ? '1' : null,
            'recetas' => $menu->recetas->pluck('id')->all(),
            'cantidades' => $cantidades,
        ]);

        $response->assertRedirect(route('menus-dia.show', $menu));
        $historial = $menu->fresh()->historial;
        $this->assertCount($historialAntes + 1, $historial);
        $this->assertSame('Menú actualizado', end($historial)['accion']);
    }

    public function test_menu_items_are_saved_and_displayed_by_production_type(): void
    {
        $user=User::role(['admin','director','cocinero'])->firstOrFail();
        $menu=MenuDia::with('recetas')->where('activo',true)->has('recetas')->firstOrFail();
        $receta=$menu->recetas->first();$tipo=TipoProduccion::where('nombre','Sopas')->firstOrFail();

        $this->actingAs($user)->put(route('menus-dia.update',$menu),[
            'titulo'=>$menu->titulo,'tipo_comida_id'=>$menu->tipo_comida_id,'fecha'=>now()->toDateString(),'hora_inicio'=>null,'hora_fin'=>null,
            'visible_para_clientes'=>1,'recetas'=>[$receta->id],'cantidades'=>[$receta->id=>3],
            'tipos_produccion_recetas'=>[$receta->id=>$tipo->id],
        ])->assertRedirect(route('menus-dia.show',$menu));

        $this->assertDatabaseHas('menus_dia_recetas',['menu_dia_id'=>$menu->id,'receta_id'=>$receta->id,'tipo_produccion_id'=>$tipo->id]);
        $this->actingAs($user)->get(route('home'))->assertOk()->assertSee('Sopas')->assertSee($receta->nombre);
    }
}
