<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraProveedorPago;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use App\Models\Marca;
use App\Services\CalculadoraCompra;
use App\Models\InsumoPresentacion;
use App\Models\FormatoEmpaque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $base=Compra::query()->when($request->filled('estado'),fn($q)=>$q->where('estado',$request->estado))
            ->when($request->filled('proveedor_id'),fn($q)=>$q->where('proveedor_id',$request->proveedor_id))
            ->when($request->filled('fecha_desde'),fn($q)=>$q->whereDate('fecha_compra','>=',$request->fecha_desde))
            ->when($request->filled('fecha_hasta'),fn($q)=>$q->whereDate('fecha_compra','<=',$request->fecha_hasta));
        $compras=(clone $base)->with(['proveedorRel','lineas.insumo','lineas.marca'])->latest('fecha_compra')->paginate(20)->withQueryString();
        \App\Helpers\HistorialHelper::registrar('Consultó compras', 'Visualizó órdenes de compra y saldos pendientes con proveedores.', 'Compras');
        return view('compras.index',['compras'=>$compras,'proveedores'=>Proveedor::orderBy('nombre')->get(),
            'totalSaldoPendiente'=>(clone $base)->where('estado','!=','anulada')->get()->sum->saldo_pago_proveedor,
            'totalAbonado'=>(clone $base)->sum('monto_pagado_proveedor'),'totalAnulado'=>(clone $base)->where('estado','anulada')->sum('costo_total'),'totalGeneral'=>(clone $base)->sum('costo_total')]);
    }
    public function create(){return view('compras.create',$this->catalogos());}
    public function store(Request $request)
    {
        $data=$this->validar($request);
        $request->validate(['lineas.*.formato_empaque_id'=>['nullable','exists:formatos_empaque,id'],'lineas.*.estructura_empaque'=>['nullable','array','max:3'],'lineas.*.estructura_empaque.*.cantidad'=>['required_with:lineas.*.estructura_empaque','numeric','gt:0'],'lineas.*.estructura_empaque.*.formato_empaque_id'=>['required_with:lineas.*.estructura_empaque','exists:formatos_empaque,id'],'lineas.*.estructura_empaque.*.contenido'=>['nullable','numeric','gt:0'],'lineas.*.estructura_empaque.*.unidad_medida_id'=>['nullable','exists:unidad_medidas,id','required_with:lineas.*.estructura_empaque.*.contenido']]);
        foreach($data['lineas'] as $indice=>&$linea){$linea['formato_empaque_id']=$request->input("lineas.$indice.formato_empaque_id")?:null;$linea['estructura_empaque']=array_values($request->input("lineas.$indice.estructura_empaque",[]));}
        unset($linea);
        $compra=DB::transaction(function()use($data){$proveedor=Proveedor::findOrFail($data['proveedor_id']);$compra=Compra::create(['proveedor_id'=>$proveedor->id,'proveedor'=>$proveedor->nombre,'fecha_compra'=>$data['fecha_compra'],'numero_documento'=>$data['numero_documento']??null,'descripcion'=>$data['descripcion']??null,'estado'=>'pendiente','costo_total'=>0,'monto_pagado_proveedor'=>0]);$this->guardarLineas($compra,$data['lineas']);return $compra;});
        $compra->load(['proveedorRel','lineas.insumo','lineas.marca']);
        \App\Helpers\HistorialHelper::registrar('Registró compra', 'Proveedor: '.($compra->proveedorRel?->nombre ?? $compra->proveedor).'. Insumos: '.$compra->lineas->map(fn($linea) => ($linea->insumo?->nombre ?? 'Insumo').' x'.$linea->cantidad_pedida)->implode(', ').'. Total: Bs '.number_format($compra->costo_total, 2).'.', 'Compras');
        return redirect()->route('compras.show',$compra)->with('success','Compra registrada.');
    }
    public function show(Compra $compra){$compra->load(['proveedorRel','lineas.insumo.unidad_medida','lineas.presentacion','lineas.formatoEmpaque','lineas.marca','lineas.unidadMedida','lineas.unidadPrecio','lineas.unidadContenido','lineas.unidadInventario','pagosProveedor']);return view('compras.show',compact('compra'));}
    public function registrarAbonoProveedor(Request $request,Compra $compra)
    {
        $data=$request->validate(['monto'=>['required','numeric','min:0.01','max:'.$compra->saldo_pago_proveedor]]);
        DB::transaction(function()use($compra,$data){CompraProveedorPago::create(['compra_id'=>$compra->id,'monto'=>$data['monto']]);$compra->update(['monto_pagado_proveedor'=>$compra->pagosProveedor()->sum('monto')]);});
        \App\Helpers\HistorialHelper::registrar('Registró abono a proveedor', 'Compra #'.$compra->id.'. Proveedor: '.($compra->proveedorRel?->nombre ?? $compra->proveedor).'. Abono: Bs '.number_format($data['monto'], 2).'. Saldo restante: Bs '.number_format($compra->fresh()->saldo_pago_proveedor, 2).'.', 'Compras');
        return back()->with('success','Abono registrado.');
    }
    private function validar(Request $request):array{return $request->validate(['proveedor_id'=>['required','exists:proveedores,id'],'fecha_compra'=>['required','date'],'numero_documento'=>['nullable','string','max:100'],'descripcion'=>['nullable','string'],'lineas'=>['required','array','min:1'],'lineas.*.insumo_id'=>['required','exists:insumos,id'],'lineas.*.presentacion_id'=>['required','exists:insumo_presentaciones,id'],'lineas.*.marca_id'=>['nullable','exists:marcas,id'],'lineas.*.unidad_medida_id'=>['required','exists:unidad_medidas,id'],'lineas.*.unidad_precio_id'=>['nullable','exists:unidad_medidas,id'],'lineas.*.cantidad_pedida'=>['required','numeric','min:0.0001'],'lineas.*.precio_unitario'=>['nullable','numeric','min:0'],'lineas.*.factor_compra_base'=>['nullable','numeric','gt:0'],'lineas.*.cantidad_contenido'=>['nullable','numeric','gt:0','required_with:lineas.*.unidad_contenido_id'],'lineas.*.cantidad_suelta'=>['nullable','numeric','min:0'],'lineas.*.unidad_contenido_id'=>['nullable','exists:unidad_medidas,id','required_with:lineas.*.cantidad_contenido'],'lineas.*.factor_precio_base'=>['nullable','numeric','gt:0'],'lineas.*.costo_linea'=>['nullable','numeric','min:0']]);}
    private function guardarLineas(Compra $compra,array $lineas):void
    {
        $total=0;$cantidad=0;
        foreach($lineas as $row){
            $insumo=Insumo::findOrFail($row['insumo_id']);
            $presentacion=InsumoPresentacion::findOrFail($row['presentacion_id']);
            if($presentacion->insumo_id!==$insumo->id)throw \Illuminate\Validation\ValidationException::withMessages(['lineas'=>'La presentación no pertenece al insumo seleccionado.']);
            if (array_key_exists('precio_unitario',$row) && $row['precio_unitario'] !== null) {
                $calculada=CalculadoraCompra::calcular($insumo,$row);
            } else {
                $calculada=[
                    'unidad_precio_id'=>$row['unidad_medida_id'] ?? $insumo->unidad_medida_id,
                    'precio_unitario'=>(float)($row['costo_linea'] ?? 0)/(float)$row['cantidad_pedida'],
                    'factor_compra_base'=>1,'factor_precio_base'=>1,
                    'cantidad_pedida_base'=>$row['cantidad_pedida'],
                    'costo_linea'=>$row['costo_linea'] ?? 0,
                ];
            }
            if (($calculada['cantidad_suelta'] ?? 0) > 0 && empty($calculada['cantidad_contenido'])) throw \Illuminate\Validation\ValidationException::withMessages(['lineas'=>'Para registrar unidades sueltas debes indicar cuÃ¡ntas unidades contiene cada empaque.']);
            $linea=$compra->lineas()->create(array_merge($row,$calculada,['cantidad_recibida'=>0,'cantidad_recibida_base'=>0]));
            $total+=(float)$linea->costo_linea;$cantidad+=(float)$linea->cantidad_pedida;
        }
        $compra->update(['costo_total'=>round($total,2),'cantidad_pedida'=>$cantidad,'cantidad_recibida'=>0,'insumo_id'=>$lineas[0]['insumo_id']]);
    }
    private function catalogos():array{$formatos=FormatoEmpaque::where('activo',true)->orderBy('nombre')->get();$idsEmpaque=$formatos->where('es_granel',false)->where('nombre','!=','Unidad')->pluck('unidad_medida_id')->filter();$unidades=UnidadMedida::whereNotIn('id',$idsEmpaque)->orderBy('nombre')->get();return ['proveedores'=>Proveedor::with('marcas')->orderBy('nombre')->get(),'insumos'=>Insumo::with(['unidad_medida','presentaciones'=>fn($q)=>$q->where('activa',true)->with('unidadStockRelacion')])->orderBy('nombre')->get(),'presentaciones'=>InsumoPresentacion::where('activa',true)->with('unidadStockRelacion')->orderBy('nombre')->get(),'unidades'=>$unidades,'formatos'=>$formatos,'marcas'=>Marca::where('activo',true)->with('proveedores')->orderBy('nombre')->get()];}
}
