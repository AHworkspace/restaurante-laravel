<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\HistorialHelper;

class InsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Insumo::query();
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
            $query->where('categoria_id', $request->categoria);
        }
        if ($request->filled('stock_minimo')) {
            $query->where('stock_minimo', $request->stock_minimo);
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
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:100',
            'stock_minimo' => 'required|numeric|min:0',
            'costo_estandar' => 'nullable|numeric|min:0',
            'categoria_id' => 'required',
            'unidad_medida_id' => 'required',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'stock_minimo' => $request->stock_minimo,
            'costo_estandar' => $request->costo_estandar,
            'categoria_id' => $request->categoria_id,
            'unidad_medida_id' => $request->unidad_medida_id
        ];

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('insumos', 'public');
        }

        $insumo = Insumo::create($data);
        HistorialHelper::registrar('Creó insumo', 'Nombre: ' . $insumo->nombre, 'Insumos');
        return redirect()->route('insumos.index')->with('success', 'Insumo creado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Insumo $insumo)
    {
        //
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
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:100',
            'stock_minimo' => 'required|numeric|min:0',
            'costo_estandar' => 'nullable|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nombre', 'descripcion', 'stock_minimo', 'costo_estandar', 'categoria_id', 'unidad_medida_id']);

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($insumo->imagen) {
                Storage::disk('public')->delete($insumo->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('insumos', 'public');
        }

        $insumo->update($data);
        HistorialHelper::registrar('Actualizó insumo', 'Nombre: ' . $insumo->nombre, 'Insumos');
        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Insumo $insumo)
    {
        // Eliminar movimientos relacionados
        foreach ($insumo->movimiento_inventarios as $movimiento) {
            $movimiento->delete();
        }
        // Eliminar relaciones en recetas
        $insumo->recetas()->detach();
        if ($insumo->imagen) {
            Storage::disk('public')->delete($insumo->imagen);
        }
        $nombre = $insumo->nombre;
        $insumo->delete();
        HistorialHelper::registrar('Eliminó insumo', 'Nombre: ' . $nombre, 'Insumos');
        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado correctamente');
    }
}
