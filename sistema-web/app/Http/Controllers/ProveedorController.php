<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Helpers\HistorialHelper;

class ProveedorController extends Controller
{
    /**
     * Mostrar lista de proveedores.
     */
    public function index()
    {
        $proveedores = Proveedor::all();
        \App\Helpers\HistorialHelper::registrar('Consultó listado de proveedores', 'Se mostró la lista completa de proveedores.', 'Proveedores');
        return view('proveedores.index', compact('proveedores'));
    }

    /**
     * Mostrar formulario para crear un nuevo proveedor.
     */
    public function create()
    {
        return view('proveedores.create'); // Corregido: ahora retorna la vista
    }

    /**
     * Guardar un nuevo proveedor.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $proveedor = Proveedor::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'descripcion' => $request->descripcion,
        ]);
        HistorialHelper::registrar('Creó proveedor', 'Proveedor: ' . $proveedor->nombre, 'Proveedores');

        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado.');
    }

    /**
     * Mostrar formulario para editar un proveedor.
     */
    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id); // Corregido: Obtiene el proveedor
        return view('proveedores.edit', compact('proveedor'));
    }

    /**
     * Actualizar un proveedor.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'descripcion' => $request->descripcion,
        ]);
        HistorialHelper::registrar('Actualizó proveedor', 'Proveedor: ' . $proveedor->nombre, 'Proveedores');

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado.');
    }

    /**
     * Eliminar un proveedor.
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id); // Corregido: Obtiene el proveedor
        $nombre = $proveedor->nombre;
        $proveedor->delete();
        HistorialHelper::registrar('Eliminó proveedor', 'Proveedor: ' . $nombre, 'Proveedores');

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado.');
    }
}
