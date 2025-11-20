<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\Insumo;
use Illuminate\Http\Request;
use App\Helpers\HistorialHelper;

class RecetaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Receta::query();
        $busqueda = null;
        $detalles = '';
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
            $busqueda = $request->buscar;
            $detalles = 'Término de búsqueda: ' . $busqueda;
        } else {
            $detalles = 'Se mostró la lista completa de recetas disponibles.';
        }
        $recetas = $query->get();
        \App\Helpers\HistorialHelper::registrar(
            $busqueda ? 'Buscó recetas' : 'Consultó listado de recetas',
            $detalles,
            'Recetas'
        );
        return view('recetas.index', compact('recetas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $insumos = Insumo::all();
        return view('recetas.create', compact('insumos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'indicaciones' => 'required|string',
            'tiempo_preparacion' => 'required|integer|min:1',
            'insumos' => 'required|array|min:1',
            'insumos.*.id' => 'required|exists:insumos,id',
            'insumos.*.cantidad' => 'required|numeric|min:0',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'indicaciones' => $request->indicaciones,
            'tiempo_preparacion' => $request->tiempo_preparacion,
            'precio' => $request->precio,
        ];
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('recetas', 'public');
        }
        $receta = Receta::create($data);
        foreach ($request->insumos as $insumo) {
            $receta->insumos()->attach($insumo['id'], ['cantidad' => $insumo['cantidad']]);
        }
        HistorialHelper::registrar('Creó receta', 'Receta: ' . $receta->nombre, 'Recetas');
        return redirect()->route('recetas.index')->with('success', 'Receta creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Receta $receta)
    {
        return view('recetas.show', compact('receta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receta $receta)
    {
        $insumos = Insumo::all();
        return view('recetas.edit', compact('receta', 'insumos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receta $receta)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'indicaciones' => 'required|string',
            'tiempo_preparacion' => 'required|integer|min:1',
            'insumos' => 'required|array|min:1',
            'insumos.*.id' => 'required|exists:insumos,id',
            'insumos.*.cantidad' => 'required|numeric|min:0',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048',
        ]);
        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'indicaciones' => $request->indicaciones,
            'tiempo_preparacion' => $request->tiempo_preparacion,
            'precio' => $request->precio,
        ];
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($receta->imagen) {
                \Storage::disk('public')->delete($receta->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('recetas', 'public');
        }
        $receta->update($data);
        $insumosSync = collect($request->insumos)->mapWithKeys(function ($insumo) {
            return [$insumo['id'] => ['cantidad' => $insumo['cantidad']]];
        })->all();
        $receta->insumos()->sync($insumosSync);
        HistorialHelper::registrar('Actualizó receta', 'Receta: ' . $receta->nombre, 'Recetas');
        return redirect()->route('recetas.index')->with('success', 'Receta actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receta $receta)
    {
        $nombre = $receta->nombre;
        // Eliminar relaciones en la tabla pivote
        $receta->insumos()->detach();
        // Eliminar ventas relacionadas y sus movimientos
        foreach ($receta->ventas as $venta) {
            foreach ($venta->movimientos_inventario as $movimiento) {
                $movimiento->delete();
            }
            $venta->delete();
        }
        $receta->delete();
        HistorialHelper::registrar('Eliminó receta', 'Receta: ' . $nombre, 'Recetas');
        return redirect()->route('recetas.index')->with('success', 'Receta eliminada correctamente');
    }

    public function toggleVisible(Request $request, Receta $receta)
    {
        $receta->visible = !$receta->visible;
        $receta->save();
        HistorialHelper::registrar(
            $receta->visible ? 'Hizo visible receta en dashboard' : 'Ocultó receta del dashboard',
            'Receta: ' . $receta->nombre,
            'Dashboard'
        );
        return redirect()->route('recetas.index')->with('success', 'Visibilidad de la receta actualizada correctamente');
    }
}
