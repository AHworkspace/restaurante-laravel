<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\HistorialHelper;
use App\Models\InsumoPresentacion;
use Illuminate\Support\Facades\DB;
use App\Models\FormatoEmpaque;

class InsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Insumo::with(['presentaciones', 'unidad_medida']);
        $busqueda = null;
        $detalles = '';
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
            $busqueda = $request->buscar;
            $detalles = 'Término de búsqueda: ' . $busqueda;
        } else {
            $detalles = 'Se mostró la lista completa de insumos registrados.';
        }
        if ($request->filled('categoria')) {
            $query->whereHas('presentaciones',fn($q)=>$q->where('categoria_id',$request->categoria));
        }
        $insumos = $query->get();
        $categorias = \App\Models\Categoria::all();
        // Registrar en historial SIEMPRE
        \App\Helpers\HistorialHelper::registrar(
            $busqueda ? 'Buscó insumos' : 'Consultó listado de insumos',
            $detalles,
            'Insumos'
        );
        return view('insumos.index', compact('insumos', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        $unidades = UnidadMedida::all();
        return view('insumos.create', compact('categorias', 'unidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:insumos,nombre',
            'descripcion' => 'nullable|string|max:500',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
        ]);
        $categoria = Categoria::firstOrFail();

        $data = [
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'stock_minimo' => 0,
            'costo_estandar' => null,
            'categoria_id' => $categoria->id,
            'unidad_medida_id' => $validated['unidad_medida_id'],
            'tipo_uso' => 'indirecto',
            'cantidad_base_por_venta' => 1
        ];

        $insumo = Insumo::create($data);
        HistorialHelper::registrar('Creó insumo', 'Nombre: ' . $insumo->nombre, 'Insumos');
        return redirect()->route('insumos.presentaciones.create',$insumo)->with('success','Insumo creado. Ahora registra su primera presentacion.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Insumo $insumo)
    {
        $insumo->load(['unidad_medida','presentaciones.categoria','presentaciones.formatoEmpaque','presentaciones.unidadContenido','presentaciones.unidadStockRelacion','presentaciones.unidadEmpaque','presentaciones.movimientos','lineasCompra.compra.proveedorRel','lineasCompra.marca','lineasCompra.unidadMedida','lineasCompra.presentacion']);
        $unidades=UnidadMedida::orderBy('nombre')->get();
        return view('insumos.show',compact('insumo','unidades'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Insumo $insumo)
    {
        $categorias = Categoria::all();
        $unidades = UnidadMedida::all();
        return view('insumos.edit', compact('insumo', 'categorias', 'unidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Insumo $insumo)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:insumos,nombre,'.$insumo->id,
            'descripcion' => 'nullable|string|max:500',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
        ]);

        $insumo->update($data);
        HistorialHelper::registrar('Actualizó insumo', 'Nombre: ' . $insumo->nombre, 'Insumos');
        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Insumo $insumo)
    {
        $usos = [];

        if ($insumo->lineasCompra()->exists()) {
            $usos[] = 'compras';
        }
        if ($insumo->movimiento_inventarios()->exists()) {
            $usos[] = 'movimientos de inventario';
        }
        if ($insumo->recetas()->exists()) {
            $usos[] = 'recetas';
        }

        if ($usos !== []) {
            return redirect()->route('insumos.index')->with(
                'error',
                'No se puede eliminar "'.$insumo->nombre.'" porque tiene registros en '.implode(', ', $usos).'. Puedes editarlo sin perder su historial.'
            );
        }

        if ($insumo->imagen) {
            Storage::disk('public')->delete($insumo->imagen);
        }
        $nombre = $insumo->nombre;
        $insumo->delete();
        HistorialHelper::registrar('Eliminó insumo', 'Nombre: ' . $nombre, 'Insumos');
        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado correctamente');
    }

    public function storePresentacion(Request $request,Insumo $insumo)
    {
        $data=$this->validarPresentacion($request);$data['categoria_id']=$data['categoria_id']??$insumo->categoria_id;$data['tipo_uso']=$data['tipo_uso']??$insumo->tipo_uso;$data['unidad_stock_id']=$data['unidad_stock_id']??$insumo->unidad_medida_id;
        if($request->hasFile('imagen'))$data['imagen']=$request->file('imagen')->store('presentaciones','public');
        $creada=DB::transaction(function()use($insumo,$data){if($data['predeterminada'])$insumo->presentaciones()->update(['predeterminada'=>false]);return $insumo->presentaciones()->create($data);});
        return redirect()->route('insumos.show',$insumo)->with('success','Presentación creada.');
    }

    public function createPresentacion(Insumo $insumo)
    {
        $insumo->load('categoria');$presentacion=null;$unidades=UnidadMedida::orderBy('nombre')->get();$categorias=Categoria::orderBy('nombre')->get();$formatos=FormatoEmpaque::where('activo',true)->orderBy('nombre')->get();return view('insumos.presentacion',compact('insumo','presentacion','unidades','categorias','formatos'));
    }

    public function editPresentacion(Insumo $insumo,InsumoPresentacion $presentacion)
    {
        abort_unless($presentacion->insumo_id===$insumo->id,404);$insumo->load('categoria');$unidades=UnidadMedida::orderBy('nombre')->get();$categorias=Categoria::orderBy('nombre')->get();$formatos=FormatoEmpaque::where('activo',true)->orderBy('nombre')->get();return view('insumos.presentacion',compact('insumo','presentacion','unidades','categorias','formatos'));
    }

    public function updatePresentacion(Request $request,Insumo $insumo,InsumoPresentacion $presentacion)
    {
        abort_unless($presentacion->insumo_id===$insumo->id,404);$data=$this->validarPresentacion($request,$presentacion);
        if($request->hasFile('imagen')){if($presentacion->imagen)Storage::disk('public')->delete($presentacion->imagen);$data['imagen']=$request->file('imagen')->store('presentaciones','public');}
        DB::transaction(function()use($insumo,$presentacion,$data){if($data['predeterminada'])$insumo->presentaciones()->where('id','!=',$presentacion->id)->update(['predeterminada'=>false]);$presentacion->update($data);});
        return back()->with('success','Presentación actualizada.');
    }

    public function destroyPresentacion(Insumo $insumo,InsumoPresentacion $presentacion)
    {
        abort_unless($presentacion->insumo_id===$insumo->id,404);
        $tieneHistorial=$presentacion->movimientos()->exists()||$presentacion->menusDia()->exists()||DB::table('compra_lineas')->where('presentacion_id',$presentacion->id)->exists()||DB::table('ventas')->where('presentacion_id',$presentacion->id)->exists()||DB::table('consumos')->where('presentacion_id',$presentacion->id)->exists();
        if($tieneHistorial)return back()->with('error','Esta presentación tiene compras, movimientos o ventas. Puedes desactivarla para conservar el historial.');
        if($insumo->presentaciones()->count()===1)return back()->with('error','El insumo debe conservar al menos una presentación.');
        if($presentacion->predeterminada)$insumo->presentaciones()->where('id','!=',$presentacion->id)->first()?->update(['predeterminada'=>true]);
        if($presentacion->imagen)Storage::disk('public')->delete($presentacion->imagen);
        $presentacion->delete();return back()->with('success','Presentación eliminada.');
    }

    private function validarPresentacion(Request $request,?InsumoPresentacion $presentacion=null):array
    {
        $data=$request->validate(['nombre'=>['required','string','max:100'],'descripcion'=>['nullable','string','max:255'],'categoria_id'=>['nullable','exists:categorias,id'],'tipo_uso'=>['nullable','in:indirecto,directo,mixto'],'contenido'=>['nullable','numeric','gt:0'],'unidad_contenido_id'=>['nullable','exists:unidad_medidas,id'],'unidad_stock_id'=>['nullable','exists:unidad_medidas,id'],'stock_minimo'=>['nullable','numeric','min:0'],'formato_empaque_id'=>['nullable','exists:formatos_empaque,id'],'codigo_barras'=>['nullable','string','max:100','unique:insumo_presentaciones,codigo_barras'.($presentacion?->id?','.$presentacion->id:'')],'imagen'=>['nullable','image','max:2048'],'retornable'=>['nullable','boolean'],'predeterminada'=>['nullable','boolean'],'activa'=>['nullable','boolean']]);
        $data['tipo_envase']=!empty($data['formato_empaque_id'])?FormatoEmpaque::find($data['formato_empaque_id'])?->nombre:null;
        $data['stock_minimo']=$data['stock_minimo']??0;$data['retornable']=$request->boolean('retornable');$data['predeterminada']=$request->boolean('predeterminada');$data['activa']=$request->boolean('activa');return $data;
    }
}
