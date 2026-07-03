<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarcaController extends Controller
{
    public function index(){return view('marcas.index',['marcas'=>Marca::with('proveedores')->orderBy('nombre')->get(),'proveedores'=>Proveedor::orderBy('nombre')->get()]);}
    public function store(Request $request){$marca=Marca::create($this->validar($request));$marca->proveedores()->sync($request->input('proveedores_ids',[]));return back()->with('success','Marca o empresa creada.');}
    public function update(Request $request,Marca $marca){$marca->update($this->validar($request,$marca));$marca->proveedores()->sync($request->input('proveedores_ids',[]));return back()->with('success','Marca o empresa actualizada.');}
    public function destroy(Marca $marca){if($marca->lineasCompra()->exists())return back()->with('error','No se puede eliminar porque ya aparece en compras. Puedes desactivarla.');$marca->delete();return back()->with('success','Marca eliminada.');}
    private function validar(Request $request,?Marca $marca=null):array{return $request->validate(['nombre'=>['required','string','max:120',Rule::unique('marcas')->ignore($marca)],'empresa_fabricante'=>['nullable','string','max:150'],'descripcion'=>['nullable','string','max:500'],'activo'=>['nullable','boolean'],'proveedores_ids'=>['nullable','array'],'proveedores_ids.*'=>['exists:proveedores,id']]);}
}
