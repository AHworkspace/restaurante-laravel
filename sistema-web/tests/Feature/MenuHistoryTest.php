<?php

namespace Tests\Feature;

use App\Models\MenuDia;
use App\Models\User;
use App\Models\TipoProduccion;
use App\Models\Categoria;
use App\Models\Insumo;
use App\Models\MovimientoInventario;
use App\Models\UnidadMedida;
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

    public function test_menu_index_filters_by_publication_state_without_visibility_filter(): void
    {
        $user=User::role(['admin','director','cocinero'])->firstOrFail();

        $this->actingAs($user)->get(route('menus-dia.index',['estado'=>'todos']))
            ->assertOk()
            ->assertSee('Publicados ahora')
            ->assertSee('Ocultos por horario')
            ->assertDontSee('<label class="small">Visibilidad</label>', false);
    }

    public function test_direct_products_show_stock_and_can_create_menu(): void
    {
        $user=User::role(['admin','director','cocinero'])->firstOrFail();
        $unidad=UnidadMedida::where('abreviatura','Ud')->first() ?? UnidadMedida::firstOrFail();
        $insumo=Insumo::create([
            'nombre'=>'Producto directo menu '.uniqid(),
            'stock_minimo'=>0,
            'costo_estandar'=>0,
            'categoria_id'=>Categoria::firstOrFail()->id,
            'unidad_medida_id'=>$unidad->id,
            'tipo_uso'=>'directo',
        ]);
        $presentacion=$insumo->presentaciones()->firstOrFail();
        MovimientoInventario::create([
            'insumo_id'=>$insumo->id,
            'presentacion_id'=>$presentacion->id,
            'tipo'=>'entrada',
            'cantidad'=>5,
            'cantidad_original'=>5,
            'cantidad_convertida'=>5,
            'unidad_medida_id'=>$unidad->id,
            'unidad_inventario_id'=>$unidad->id,
            'motivo'=>'Stock prueba menu',
        ]);

        $this->actingAs($user)->get(route('menus-dia.create'))
            ->assertOk()
            ->assertSee('Stock actual')
            ->assertSee('5.00');

        $response=$this->actingAs($user)->post(route('menus-dia.store'),[
            'titulo'=>'Menu directo '.uniqid(),
            'fecha'=>now()->toDateString(),
            'presentaciones_directas'=>[$presentacion->id],
            'cantidades_presentaciones'=>[$presentacion->id=>3],
            'precios_presentaciones'=>[$presentacion->id=>12],
        ]);

        $response->assertRedirect();
        $this->actingAs($user)->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($insumo->nombre);
    }

    public function test_menu_update_preserves_sold_items_and_records_additions(): void
    {
        $user=User::role(['admin','director','cocinero'])->firstOrFail();
        $menu=MenuDia::with('recetas')->where('activo',true)->has('recetas')->firstOrFail();
        $receta=$menu->recetas->first();
        $menu->recetas()->updateExistingPivot($receta->id,[
            'cantidad_inicial'=>2,
            'cantidad'=>0,
            'adiciones'=>null,
        ]);

        $this->actingAs($user)->get(route('menus-dia.edit',$menu))
            ->assertOk()
            ->assertSee('Ya publicados en este menu')
            ->assertSee('Adicionar ahora')
            ->assertSee('Quitar del menu')
            ->assertSee('Agregar al menu');

        $this->actingAs($user)->put(route('menus-dia.update',$menu),[
            'titulo'=>$menu->titulo,
            'tipo_comida_id'=>$menu->tipo_comida_id,
            'fecha'=>$menu->fecha->toDateString(),
            'hora_inicio'=>$menu->hora_inicio ? substr((string)$menu->hora_inicio,0,5) : null,
            'hora_fin'=>$menu->hora_fin ? substr((string)$menu->hora_fin,0,5) : null,
            'descripcion'=>$menu->descripcion,
            'visible_para_clientes'=>$menu->visible_para_clientes ? '1' : null,
            'visible_en_horario'=>$menu->visible_en_horario ? '1' : null,
            'recetas'=>[$receta->id],
            'adiciones'=>[$receta->id=>3],
        ])->assertRedirect(route('menus-dia.show',$menu));

        $actualizado=$menu->fresh()->recetas()->where('receta_id',$receta->id)->firstOrFail();
        $this->assertSame(2,(int)$actualizado->pivot->cantidad_inicial);
        $this->assertSame(3,(int)$actualizado->pivot->cantidad);
        $this->assertSame([3],json_decode($actualizado->pivot->adiciones,true));

        $this->actingAs($user)->put(route('menus-dia.update',$menu),[
            'titulo'=>$menu->titulo,
            'tipo_comida_id'=>$menu->tipo_comida_id,
            'fecha'=>$menu->fecha->toDateString(),
            'hora_inicio'=>$menu->hora_inicio ? substr((string)$menu->hora_inicio,0,5) : null,
            'hora_fin'=>$menu->hora_fin ? substr((string)$menu->hora_fin,0,5) : null,
            'descripcion'=>$menu->descripcion,
            'visible_para_clientes'=>$menu->visible_para_clientes ? '1' : null,
            'visible_en_horario'=>$menu->visible_en_horario ? '1' : null,
            'recetas'=>[$receta->id],
            'adiciones'=>[$receta->id=>1],
        ])->assertRedirect(route('menus-dia.show',$menu));

        $actualizado=$menu->fresh()->recetas()->where('receta_id',$receta->id)->firstOrFail();
        $this->assertSame(4,(int)$actualizado->pivot->cantidad);
        $this->assertSame([3,1],json_decode($actualizado->pivot->adiciones,true));

        $this->actingAs($user)->get(route('menus-dia.show',$menu))
            ->assertOk()
            ->assertSee('Produccion total')
            ->assertSee('+3')
            ->assertSee('+1')
            ->assertSee('6');
    }

    public function test_menu_edit_can_remove_a_published_item(): void
    {
        $user=User::role(['admin','director','cocinero'])->firstOrFail();
        $menu=MenuDia::with('recetas')->where('activo',true)->has('recetas')->firstOrFail();
        $receta=$menu->recetas->first();
        $otraReceta=\App\Models\Receta::where('id','!=',$receta->id)->firstOrFail();
        $menu->recetas()->syncWithoutDetaching([
            $receta->id=>['cantidad'=>1,'cantidad_inicial'=>1],
            $otraReceta->id=>['cantidad'=>1,'cantidad_inicial'=>1],
        ]);

        $this->actingAs($user)->put(route('menus-dia.update',$menu),[
            'titulo'=>$menu->titulo,
            'tipo_comida_id'=>$menu->tipo_comida_id,
            'fecha'=>$menu->fecha->toDateString(),
            'hora_inicio'=>$menu->hora_inicio ? substr((string)$menu->hora_inicio,0,5) : null,
            'hora_fin'=>$menu->hora_fin ? substr((string)$menu->hora_fin,0,5) : null,
            'descripcion'=>$menu->descripcion,
            'visible_para_clientes'=>$menu->visible_para_clientes ? '1' : null,
            'visible_en_horario'=>$menu->visible_en_horario ? '1' : null,
            'recetas'=>[$receta->id,$otraReceta->id],
            'quitar_recetas'=>[$receta->id],
        ])->assertRedirect(route('menus-dia.show',$menu));

        $this->assertDatabaseMissing('menus_dia_recetas',['menu_dia_id'=>$menu->id,'receta_id'=>$receta->id]);
        $this->assertDatabaseHas('menus_dia_recetas',['menu_dia_id'=>$menu->id,'receta_id'=>$otraReceta->id]);
    }
}
