<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Receta;
use Illuminate\Http\Request;
use App\Helpers\HistorialHelper;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Venta::with(['receta','insumo','presentacion.insumo']);
        if ($request->filled('receta_id')) {
            $query->where('receta_id', $request->receta_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        $ventas = $query->orderBy('created_at', 'desc')->paginate();
        $recetas = \App\Models\Receta::all();
        return view('ventas.index', compact('ventas', 'recetas'))
            ->with('i', (request()->input('page', 1) - 1) * $ventas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $recetas = Receta::all();
        return view('ventas.create', compact('recetas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cantidad' => 'required|numeric',
            'precio' => 'required|numeric',
            'total' => 'required|numeric',
            'receta_id' => 'required',
            'fecha' => 'required|date',
        ]);

        $venta = new Venta();
        $venta->cantidad = $request->cantidad;
        $venta->precio = $request->precio;
        $venta->total = $request->total;
        $venta->receta_id = $request->receta_id;
        $venta->created_at = $request->fecha;
        $venta->save();
        HistorialHelper::registrar('Registró venta', 'Receta ID: ' . $venta->receta_id . ', Cantidad: ' . $venta->cantidad . ', Total: ' . $venta->total, 'Ventas');

        return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load(['receta.insumos','insumo.unidad_medida','presentacion.insumo','consumidor']);
        if (!auth()->user() || !auth()->user()->restaurante) {
            return redirect()->route('home')->with('error', 'No tienes un restaurante asociado.');
        }

        return view($venta->insumo_id ? 'ventas.show-directo' : 'ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        if($venta->insumo_id)return redirect()->route('ventas.show',$venta)->with('error','La venta directa se corrige desde el consumo asociado para conservar el inventario.');
        $recetas = Receta::all();
        return view('ventas.edit', compact('venta', 'recetas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        $request->validate([
            'cantidad' => 'required|numeric',
            'precio' => 'required|numeric',
            'total' => 'required|numeric',
            'receta_id' => 'required|exists:recetas,id',
            'fecha' => 'required|date',
        ]);

        $venta->cantidad = $request->cantidad;
        $venta->precio = $request->precio;
        $venta->total = $request->total;
        $venta->receta_id = $request->receta_id;
        $venta->created_at = $request->fecha;
        $venta->save();
        HistorialHelper::registrar('Actualizó venta', 'Receta ID: ' . $venta->receta_id . ', Cantidad: ' . $venta->cantidad . ', Total: ' . $venta->total, 'Ventas');
        return redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        $info = 'Receta ID: ' . $venta->receta_id . ', Cantidad: ' . $venta->cantidad . ', Total: ' . $venta->total;
        // Eliminar movimientos relacionados
        foreach ($venta->movimientos_inventario as $movimiento) {
            $movimiento->delete();
        }
        $venta->delete();
        HistorialHelper::registrar('Eliminó venta', $info, 'Ventas');
        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente');
    }
}
