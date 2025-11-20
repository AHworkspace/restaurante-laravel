<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Helpers\HistorialHelper;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::all();
        return view('categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:100'
        ]);

        try {
            $categoria = Categoria::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion
            ]);
            HistorialHelper::registrar('Creó categoría', 'Nombre: ' . $categoria->nombre, 'Categorías');
            return redirect()->route('categorias.index')
                ->with('success', 'Categoría creada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear la categoría. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $categoria)
    {
        return view('categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:100'
        ]);

        try {
            $categoria->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion
            ]);
            HistorialHelper::registrar('Actualizó categoría', 'Nombre: ' . $categoria->nombre, 'Categorías');
            return redirect()->route('categorias.index')
                ->with('success', 'Categoría actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar la categoría. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        try {
            $nombre = $categoria->nombre;
            $categoria->delete();
            HistorialHelper::registrar('Eliminó categoría', 'Nombre: ' . $nombre, 'Categorías');
            return redirect()->route('categorias.index')
                ->with('success', 'Categoría eliminada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la categoría. Por favor, intenta nuevamente.');
        }
    }
}
