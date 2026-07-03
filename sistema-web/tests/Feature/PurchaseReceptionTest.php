<?php

namespace Tests\Feature;

use App\Models\CompraLinea;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\User;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\FormatoEmpaque;
use App\Services\CalculadoraCompra;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseReceptionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_purchase_form_lists_ingredients_grouped_with_their_presentations():void
    {
        $user=User::role(['admin','director'])->firstOrFail();
        $insumo=Insumo::with('presentaciones')->whereHas('presentaciones')->firstOrFail();
        $caja=UnidadMedida::where('nombre','Caja')->firstOrFail();
        $response=$this->actingAs($user)->get(route('compras.create'));
        $response->assertOk()->assertSee('Insumo y presentación')->assertSee('Formato exterior')->assertSee('Añadir contenido interno')->assertSee('Formato interior')->assertSee('Unidad de medida interna')->assertDontSee('Contenido por empaque')->assertDontSee('<option value="'.$caja->id.'">Caja (',false)->assertSee($insumo->nombre)->assertSee($insumo->presentaciones->first()->nombre);
    }

    public function test_migrated_default_presentation_remains_editable_even_with_history():void
    {
        $user=User::role(['admin','director'])->firstOrFail();
        $insumo=Insumo::where('nombre','Papa')->firstOrFail();
        $presentacion=$insumo->presentacionPredeterminada()->firstOrFail();
        $this->actingAs($user)->get(route('insumos.presentaciones.edit',[$insumo,$presentacion]))->assertOk()->assertSee('Guardar cambios');
        $this->actingAs($user)->put(route('insumos.presentaciones.update',[$insumo,$presentacion]),[
            'nombre'=>'Papa por peso','unidad_contenido_id'=>$insumo->unidad_medida_id,
            'predeterminada'=>1,'activa'=>1,
        ])->assertRedirect();
        $this->assertSame('Papa por peso',$presentacion->fresh()->nombre);
    }

    public function test_purchase_line_can_be_received_partially_without_exceeding_missing_quantity(): void
    {
        $user = User::role(['admin', 'director'])->firstOrFail();
        $linea = CompraLinea::with(['compra', 'insumo'])->get()->first(fn ($item) => $item->cantidad_faltante >= 1);
        $this->assertNotNull($linea);
        $movimientosAntes = MovimientoInventario::count();

        $payload = [
            'compra_linea_id' => $linea->id,
            'insumo_id' => $linea->insumo_id,
            'unidad_medida_id' => $linea->unidad_medida_id ?: $linea->insumo->unidad_medida_id,
            'cantidad' => 0.5,
            'tipo' => 'entrada',
            'motivo' => 'Recepción de compra',
            'fecha' => now()->toDateString(),
        ];

        $this->actingAs($user)->post(route('movimientos.store'), $payload)
            ->assertRedirect(route('movimientos.index'));

        $linea = $linea->fresh();
        $this->assertEqualsWithDelta(0.5, (float) $linea->cantidad_recibida, 0.0001);
        $this->assertSame('parcial', $linea->compra->fresh()->estado);
        $this->assertDatabaseHas('movimiento_inventarios', [
            'compra_id' => $linea->compra_id,
            'compra_linea_id' => $linea->id,
            'tipo' => 'entrada',
        ]);
        $movimiento = MovimientoInventario::where('compra_linea_id', $linea->id)->latest('id')->firstOrFail();

        $payload['cantidad'] = $linea->cantidad_faltante + 0.01;
        $this->actingAs($user)->from(route('movimientos.create'))->post(route('movimientos.store'), $payload)
            ->assertRedirect(route('movimientos.create'))->assertSessionHasErrors('cantidad');
        $this->assertSame($movimientosAntes + 1, MovimientoInventario::count());

        $payload['cantidad'] = 0.75;
        $this->actingAs($user)->put(route('movimientos.update', $movimiento), $payload)
            ->assertRedirect(route('movimientos.index'));
        $this->assertEqualsWithDelta(0.75, (float) $linea->fresh()->cantidad_recibida, 0.0001);

        $this->actingAs($user)->delete(route('movimientos.destroy', $movimiento))
            ->assertRedirect(route('movimientos.index'));
        $this->assertEqualsWithDelta(0, (float) $linea->fresh()->cantidad_recibida, 0.0001);
        $this->assertSame('pendiente', $linea->compra->fresh()->estado);
    }

    public function test_optional_brand_is_preserved_from_purchase_to_reception(): void
    {
        $user = User::role(['admin', 'director'])->firstOrFail();
        $proveedor = Proveedor::firstOrFail();
        $insumo = Insumo::with('unidad_medida')->firstOrFail();
        $presentacion=$insumo->presentacionPredeterminada()->firstOrFail();
        $marca = Marca::create(['nombre' => 'Marca prueba '.uniqid(), 'empresa_fabricante' => 'Empresa prueba']);
        $marca->proveedores()->attach($proveedor->id);

        $response = $this->actingAs($user)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id, 'fecha_compra' => now()->toDateString(),
            'lineas' => [[
                'insumo_id' => $insumo->id, 'presentacion_id'=>$presentacion->id, 'marca_id' => $marca->id,
                'unidad_medida_id' => $insumo->unidad_medida_id,
                'cantidad_pedida' => 2, 'costo_linea' => 20,
            ]],
        ]);

        $linea = CompraLinea::where('marca_id', $marca->id)->latest('id')->firstOrFail();
        $response->assertRedirect(route('compras.show', $linea->compra_id));
        $this->assertSame($marca->id, $linea->marca_id);
        $this->assertSame($presentacion->id, $linea->presentacion_id);

        $this->actingAs($user)->post(route('movimientos.store'), [
            'compra_linea_id' => $linea->id, 'insumo_id' => $insumo->id,
            'unidad_medida_id' => $insumo->unidad_medida_id, 'cantidad' => 1,
            'tipo' => 'entrada', 'motivo' => 'Recepcion con marca', 'fecha' => now()->toDateString(),
        ])->assertRedirect(route('movimientos.index'));

        $movimiento = MovimientoInventario::where('compra_linea_id', $linea->id)->firstOrFail();
        $this->assertSame($marca->id, $movimiento->compraLinea->marca_id);
        $this->assertSame($presentacion->id, $movimiento->presentacion_id);
        $this->actingAs($user)->get(route('insumos.show', $insumo))->assertOk()->assertSee($marca->nombre);
    }

    public function test_used_ingredient_cannot_be_deleted_and_keeps_purchase_history(): void
    {
        $user = User::role(['admin', 'director'])->firstOrFail();
        $linea = CompraLinea::with('insumo')->firstOrFail();

        $this->actingAs($user)->delete(route('insumos.destroy', $linea->insumo))
            ->assertRedirect(route('insumos.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('insumos', ['id' => $linea->insumo_id]);
        $this->assertDatabaseHas('compra_lineas', ['id' => $linea->id]);
    }

    public function test_price_is_converted_from_arroba_to_purchase_unit(): void
    {
        $kg = UnidadMedida::where('nombre', 'Kilogramo')->firstOrFail();
        $arroba = UnidadMedida::where('nombre', 'Arroba')->firstOrFail();
        $cuartilla = UnidadMedida::where('nombre', 'Cuartilla')->firstOrFail();
        $insumo = Insumo::create(['nombre'=>'Papa cálculo '.uniqid(),'stock_minimo'=>0,'costo_estandar'=>0,'categoria_id'=>Categoria::firstOrFail()->id,'unidad_medida_id'=>$kg->id]);

        $dosKilos = CalculadoraCompra::calcular($insumo, [
            'cantidad_pedida' => 2, 'unidad_medida_id' => $kg->id,
            'precio_unitario' => 45, 'unidad_precio_id' => $arroba->id,
        ]);
        $this->assertEqualsWithDelta(7.50, $dosKilos['costo_linea'], 0.01);
        $this->assertEqualsWithDelta(2, $dosKilos['cantidad_pedida_base'], 0.0001);

        $dosArrobas = CalculadoraCompra::calcular($insumo, [
            'cantidad_pedida' => 2, 'unidad_medida_id' => $arroba->id,
            'precio_unitario' => 45, 'unidad_precio_id' => $arroba->id,
        ]);
        $this->assertEqualsWithDelta(90, $dosArrobas['costo_linea'], 0.01);
        $this->assertEqualsWithDelta(24, $dosArrobas['cantidad_pedida_base'], 0.001);

        $unaCuartilla = CalculadoraCompra::calcular($insumo, [
            'cantidad_pedida' => 1, 'unidad_medida_id' => $cuartilla->id,
            'precio_unitario' => 45, 'unidad_precio_id' => $arroba->id,
        ]);
        $this->assertEqualsWithDelta(11.25, $unaCuartilla['costo_linea'], 0.01);
    }

    public function test_decimal_weight_is_not_split_as_packaging(): void
    {
        $kg=UnidadMedida::where('nombre','Kilogramo')->firstOrFail();
        $arroba=UnidadMedida::where('nombre','Arroba')->firstOrFail();
        $linea=new CompraLinea(['cantidad_pedida'=>0.5,'cantidad_pedida_base'=>5.6699,'cantidad_recibida_base'=>0,'factor_compra_base'=>11.3398,'cantidad_contenido'=>null,'unidad_medida_id'=>$arroba->id,'unidad_inventario_id'=>$kg->id]);

        $this->assertFalse($linea->faltante_desglosado['es_empaque']);
        $this->assertEqualsWithDelta(0.5,$linea->faltante_desglosado['empaques'],0.0001);
        $this->assertSame(0,$linea->faltante_desglosado['sueltas']);
    }

    public function test_package_equivalence_controls_total_and_inventory_entry(): void
    {
        $botella = UnidadMedida::where('nombre', 'Unidad')->firstOrFail();
        $caja = UnidadMedida::where('nombre', 'Caja')->firstOrFail();
        $insumo = Insumo::create(['nombre'=>'Bebida cálculo '.uniqid(),'stock_minimo'=>0,'costo_estandar'=>0,'categoria_id'=>Categoria::firstOrFail()->id,'unidad_medida_id'=>$botella->id]);

        $resultado = CalculadoraCompra::calcular($insumo, [
            'cantidad_pedida' => 2, 'unidad_medida_id' => $caja->id,
            'factor_compra_base' => 6, 'precio_unitario' => 60,
            'unidad_precio_id' => $caja->id,
        ]);

        $this->assertEqualsWithDelta(120, $resultado['costo_linea'], 0.01);
        $this->assertEqualsWithDelta(12, $resultado['cantidad_pedida_base'], 0.0001);
        $this->assertEqualsWithDelta(6, $resultado['factor_precio_base'], 0.0001);
    }

    public function test_package_without_known_content_is_not_forced_to_invent_an_equivalence(): void
    {
        $kg = UnidadMedida::where('nombre', 'Kilogramo')->firstOrFail();
        $bolsa = UnidadMedida::where('nombre', 'Bolsa')->firstOrFail();
        $insumo = Insumo::create(['nombre'=>'Pimienta en bolsa '.uniqid(),'stock_minimo'=>0,'costo_estandar'=>0,'categoria_id'=>Categoria::firstOrFail()->id,'unidad_medida_id'=>$kg->id]);

        $resultado = CalculadoraCompra::calcular($insumo, [
            'cantidad_pedida' => 3, 'unidad_medida_id' => $bolsa->id,
            'precio_unitario' => 1, 'unidad_precio_id' => $bolsa->id,
        ]);

        $this->assertEqualsWithDelta(3, $resultado['costo_linea'], 0.01);
        $this->assertEqualsWithDelta(3, $resultado['cantidad_pedida_base'], 0.0001);
        $this->assertEqualsWithDelta(1, $resultado['factor_compra_base'], 0.0001);
    }

    public function test_purchase_detail_opens_after_saving(): void
    {
        $user = User::role(['admin', 'director'])->firstOrFail();
        $linea = CompraLinea::latest('id')->firstOrFail();

        $this->actingAs($user)->get(route('compras.show', $linea->compra_id))
            ->assertOk()
            ->assertDontSee('Registrar abono al proveedor');
    }

    public function test_box_content_keeps_the_inner_inventory_unit(): void
    {
        $kg = UnidadMedida::where('nombre', 'Kilogramo')->firstOrFail();
        $caja = UnidadMedida::where('nombre', 'Caja')->firstOrFail();
        $bolsa = UnidadMedida::where('nombre', 'Bolsa')->firstOrFail();
        $insumo = Insumo::create(['nombre'=>'Condimento por caja '.uniqid(),'stock_minimo'=>0,'costo_estandar'=>0,'categoria_id'=>Categoria::firstOrFail()->id,'unidad_medida_id'=>$kg->id]);

        $resultado = CalculadoraCompra::calcular($insumo, [
            'cantidad_pedida' => 2, 'unidad_medida_id' => $caja->id,
            'cantidad_contenido' => 12, 'cantidad_suelta'=>6, 'unidad_contenido_id' => $bolsa->id,
            'precio_unitario' => 12, 'unidad_precio_id' => $caja->id,
        ]);

        $this->assertEqualsWithDelta(30, $resultado['costo_linea'], 0.01);
        $this->assertEqualsWithDelta(30, $resultado['cantidad_pedida_base'], 0.0001);
        $this->assertEqualsWithDelta(6, $resultado['cantidad_suelta'], 0.0001);
        $this->assertSame($bolsa->id, $resultado['unidad_inventario_id']);
    }

    public function test_two_boxes_and_six_loose_units_are_received_as_thirty_units(): void
    {
        $user=User::role(['admin','director'])->firstOrFail();
        $proveedor=Proveedor::firstOrFail();$kg=UnidadMedida::where('nombre','Kilogramo')->firstOrFail();
        $caja=UnidadMedida::where('nombre','Caja')->firstOrFail();$bolsa=UnidadMedida::where('nombre','Bolsa')->firstOrFail();
        $insumo=Insumo::create(['nombre'=>'Empaque mixto '.uniqid(),'stock_minimo'=>0,'costo_estandar'=>0,'categoria_id'=>Categoria::firstOrFail()->id,'unidad_medida_id'=>$kg->id]);
        $presentacion=$insumo->presentaciones()->create(['nombre'=>'Caja de bolsas','activa'=>true,'predeterminada'=>true,'unidad_stock_id'=>$bolsa->id,'unidad_empaque_id'=>$caja->id,'unidades_por_empaque'=>12]);

        $this->actingAs($user)->post(route('compras.store'),['proveedor_id'=>$proveedor->id,'fecha_compra'=>now()->toDateString(),'lineas'=>[		  ['insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'unidad_medida_id'=>$caja->id,'cantidad_pedida'=>2,'cantidad_contenido'=>12,'cantidad_suelta'=>6,'unidad_contenido_id'=>$bolsa->id,'precio_unitario'=>12,'unidad_precio_id'=>$caja->id]
        ]])->assertRedirect();
        $linea=CompraLinea::where('insumo_id',$insumo->id)->latest('id')->firstOrFail();

        $this->actingAs($user)->post(route('movimientos.store'),['compra_linea_id'=>$linea->id,'insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'unidad_medida_id'=>$caja->id,'cantidad'=>2,'cantidad_suelta'=>6,'tipo'=>'entrada','motivo'=>'Recepcion completa','fecha'=>now()->toDateString()])->assertRedirect(route('movimientos.index'));

        $movimiento=MovimientoInventario::where('compra_linea_id',$linea->id)->latest('id')->firstOrFail();
        $this->assertEqualsWithDelta(30,(float)$movimiento->cantidad_convertida,0.0001);
        $this->assertSame($bolsa->id,$movimiento->unidad_inventario_id);
        $this->assertSame('recibida',$linea->compra->fresh()->estado);
        $this->assertEqualsWithDelta(1,(float)$presentacion->fresh()->costo_estandar,0.0001);
    }

    public function test_ingredient_presentation_can_be_created_edited_and_deleted_with_image(): void
    {
        Storage::fake('public');
        $user=User::role(['admin','director'])->firstOrFail();
        $insumo=Insumo::firstOrFail();$unidad=UnidadMedida::firstOrFail();
        $this->actingAs($user)->post(route('insumos.presentaciones.store',$insumo),[
            'nombre'=>'Presentación prueba','contenido'=>2,'unidad_contenido_id'=>$unidad->id,
            'tipo_envase'=>'Paquete','activa'=>1,'imagen'=>UploadedFile::fake()->createWithContent('presentacion.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')),
        ])->assertRedirect();
        $presentacion=$insumo->presentaciones()->where('nombre','Presentación prueba')->firstOrFail();
        Storage::disk('public')->assertExists($presentacion->imagen);
        $this->actingAs($user)->put(route('insumos.presentaciones.update',[$insumo,$presentacion]),[
            'nombre'=>'Presentación editada','contenido'=>2,'unidad_contenido_id'=>$unidad->id,'tipo_envase'=>'Caja','activa'=>1,
        ])->assertRedirect();
        $this->assertSame('Presentación editada',$presentacion->fresh()->nombre);
        $rutaImagen=$presentacion->fresh()->imagen;
        $this->actingAs($user)->delete(route('insumos.presentaciones.destroy',[$insumo,$presentacion]))->assertRedirect();
        $this->assertDatabaseMissing('insumo_presentaciones',['id'=>$presentacion->id]);
        Storage::disk('public')->assertMissing($rutaImagen);
    }

    public function test_minimum_stock_belongs_to_each_presentation_and_general_form_has_no_image(): void
    {
        $user=User::role(['admin','director'])->firstOrFail();
        $insumo=Insumo::firstOrFail();$unidad=UnidadMedida::firstOrFail();
        $presentacion=$insumo->presentaciones()->create(['nombre'=>'Stock independiente '.uniqid(),'unidad_stock_id'=>$unidad->id,'stock_minimo'=>5,'costo_estandar'=>7.5,'activa'=>true]);

        $this->assertEqualsWithDelta(5,(float)$presentacion->stock_minimo,0.0001);
        $this->assertTrue($presentacion->stockBajo());
        $this->actingAs($user)->get(route('insumos.create'))->assertOk()->assertDontSee('name="imagen"',false)->assertDontSee('name="stock_minimo"',false)->assertDontSee('name="costo_estandar"',false);
        $this->actingAs($user)->get(route('insumos.presentaciones.edit',[$insumo,$presentacion]))->assertOk()->assertSee('Stock minimo')->assertSee('Costo estandar');
        $this->actingAs($user)->get(route('insumos.show',$insumo))->assertOk()->assertSee('Stock minimo')->assertSee('Costo estandar')->assertSee('Bs 7.50');
    }

    public function test_configured_packaging_is_selected_in_purchase_and_converted_to_base_stock():void
    {
        $user=User::role(['admin','director'])->firstOrFail();$proveedor=Proveedor::firstOrFail();
        $unidad=UnidadMedida::where('nombre','Unidad')->firstOrFail();$cajaUnidad=UnidadMedida::where('nombre','Caja')->firstOrFail();
        $insumo=Insumo::create(['nombre'=>'Bebida empaquetada '.uniqid(),'stock_minimo'=>0,'categoria_id'=>Categoria::firstOrFail()->id,'unidad_medida_id'=>$unidad->id]);
        $presentacion=$insumo->presentaciones()->create(['nombre'=>'Botella 2.5 L','unidad_stock_id'=>$unidad->id,'activa'=>true]);
        $formato=FormatoEmpaque::where('nombre','Caja')->firstOrFail();$formato->update(['unidad_medida_id'=>$cajaUnidad->id]);
        $this->actingAs($user)->post(route('compras.store'),['proveedor_id'=>$proveedor->id,'fecha_compra'=>now()->toDateString(),'lineas'=>[[
            'insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'formato_empaque_id'=>$formato->id,
            'estructura_empaque'=>[['cantidad'=>6,'formato_empaque_id'=>FormatoEmpaque::where('nombre','Botella')->firstOrFail()->id,'contenido'=>2.5,'unidad_medida_id'=>UnidadMedida::where('nombre','Litro')->firstOrFail()->id]],
            'unidad_medida_id'=>$cajaUnidad->id,'cantidad_pedida'=>2,'precio_unitario'=>60,'unidad_precio_id'=>$cajaUnidad->id,
        ]]])->assertRedirect();

        $linea=CompraLinea::where('insumo_id',$insumo->id)->latest('id')->firstOrFail();
        $this->assertSame($formato->id,$linea->formato_empaque_id);
        $this->assertSame(6.0,(float)$linea->estructura_empaque[0]['cantidad']);
        $this->assertSame(2.5,(float)$linea->estructura_empaque[0]['contenido']);
        $this->assertEqualsWithDelta(12,(float)$linea->cantidad_pedida_base,0.0001);
        $this->assertEqualsWithDelta(120,(float)$linea->costo_linea,0.0001);

        $this->actingAs($user)->post(route('movimientos.store'),['compra_linea_id'=>$linea->id,'insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'unidad_medida_id'=>$cajaUnidad->id,'cantidad'=>2,'tipo'=>'entrada','motivo'=>'Recepción por formato de compra','fecha'=>now()->toDateString()])->assertRedirect(route('movimientos.index'));
        $movimiento=MovimientoInventario::where('compra_linea_id',$linea->id)->latest('id')->firstOrFail();
        $this->assertEqualsWithDelta(12,(float)$movimiento->cantidad_convertida,0.0001);
    }
}
