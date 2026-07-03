<?php

namespace App\Http\Controllers;

use App\Models\FormatoEmpaque;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormatoEmpaqueController extends Controller
{
    public function index(){return view('formatos-empaque.index',['formatos'=>FormatoEmpaque::with('unidadMedida')->orderBy('nombre')->get(),'unidades'=>UnidadMedida::orderBy('nombre')->get()]);}
    public function store(Request $request){FormatoEmpaque::create($this->validar($request));return back()->with('success','Formato de empaque creado.');}
    public function update(Request $request,FormatoEmpaque $formatoEmpaque){$formatoEmpaque->update($this->validar($request,$formatoEmpaque));return back()->with('success','Formato de empaque actualizado.');}
    public function destroy(FormatoEmpaque $formatoEmpaque){if($formatoEmpaque->presentaciones()->exists()||$formatoEmpaque->lineasCompra()->exists())return back()->with('error','No se puede eliminar porque ya se utiliza. Puedes desactivarlo.');$formatoEmpaque->delete();return back()->with('success','Formato eliminado.');}
    private function validar(Request $request,?FormatoEmpaque $formato=null):array{return $request->validate(['nombre'=>['required','string','max:80',Rule::unique('formatos_empaque')->ignore($formato)],'descripcion'=>['nullable','string','max:255'],'unidad_medida_id'=>['nullable','exists:unidad_medidas,id'],'es_granel'=>['nullable','boolean'],'activo'=>['nullable','boolean']])+['es_granel'=>$request->boolean('es_granel'),'activo'=>$request->boolean('activo')];}
}
