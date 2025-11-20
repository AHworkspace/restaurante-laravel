<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use App\Helpers\HistorialHelper;

class UnidadMedidaController extends Controller
{
    //
    public function index()
    {
        $unidades = UnidadMedida::all();
        return view('unidades.index', compact('unidades'));
    }

    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'abreviatura' => 'required|string|max:10',
            'descripcion' => 'nullable|string|max:100'
        ]);

        try {
            // Crear nueva unidad
            $unidad = UnidadMedida::create([
                'nombre' => $validated['nombre'],
                'abreviatura' => $validated['abreviatura'],
                'descripcion' => $validated['descripcion']
            ]);
            HistorialHelper::registrar('Creó unidad de medida', 'Nombre: ' . $unidad->nombre, 'Unidades de medida');

            return redirect()->route('unidades.index')
                ->with('success', 'Unidad de medida creada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la unidad de medida')
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        // Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'abreviatura' => 'required|string|max:10',
            'descripcion' => 'nullable|string|max:100'
        ]);

        try {
            // Buscar la unidad
            $unidad = UnidadMedida::findOrFail($id);

            // Actualizar
            $unidad->update([
                'nombre' => $validated['nombre'],
                'abreviatura' => $validated['abreviatura'],
                'descripcion' => $validated['descripcion']
            ]);
            HistorialHelper::registrar('Actualizó unidad de medida', 'Nombre: ' . $unidad->nombre, 'Unidades de medida');

            return redirect()->route('unidades.index')
                ->with('success', 'Unidad de medida actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la unidad de medida')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Buscar y eliminar
            $unidad = UnidadMedida::findOrFail($id);
            $nombre = $unidad->nombre;
            $unidad->delete();
            HistorialHelper::registrar('Eliminó unidad de medida', 'Nombre: ' . $nombre, 'Unidades de medida');

            return redirect()->route('unidades.index')
                ->with('success', 'Unidad de medida eliminada correctamente');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') { // Violación de clave foránea
                return redirect()->route('unidades.index')
                    ->with('error', 'No se puede eliminar la unidad de medida porque está en uso.');
            }
            return redirect()->route('unidades.index')
                ->with('error', 'Error al eliminar la unidad de medida');
        } catch (\Exception $e) {
            return redirect()->route('unidades.index')
                ->with('error', 'Error al eliminar la unidad de medida');
        }
    }
}
