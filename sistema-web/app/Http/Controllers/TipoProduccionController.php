<?php
namespace App\Http\Controllers;
use App\Models\TipoProduccion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class TipoProduccionController extends Controller
{
    public function index(){return view('tipos-produccion.index',['tipos'=>TipoProduccion::orderBy('orden')->orderBy('nombre')->get()]);}
    public function store(Request $request){TipoProduccion::create($this->validar($request));return back()->with('success','Tipo de produccion creado.');}
    public function update(Request $request,TipoProduccion $tipoProduccion){$tipoProduccion->update($this->validar($request,$tipoProduccion));return back()->with('success','Tipo de produccion actualizado.');}
    public function destroy(TipoProduccion $tipoProduccion){if(DB::table('menus_dia_recetas')->where('tipo_produccion_id',$tipoProduccion->id)->exists()||DB::table('menus_dia_presentaciones')->where('tipo_produccion_id',$tipoProduccion->id)->exists())return back()->with('error','Este tipo esta usado en menus. Puedes desactivarlo.');$tipoProduccion->delete();return back()->with('success','Tipo eliminado.');}
    private function validar(Request $request,?TipoProduccion $tipo=null):array{$data=$request->validate(['nombre'=>['required','string','max:80',Rule::unique('tipos_produccion')->ignore($tipo?->id)],'descripcion'=>['nullable','string','max:255'],'orden'=>['required','integer','min:0'],'activo'=>['nullable','boolean']]);$data['activo']=$request->boolean('activo');return $data;}
}
