<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('historial');

        // Filtros
        if ($request->filled('usuario')) {
            $query->where('usuario', 'like', '%' . $request->usuario . '%');
        }
        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }
        if ($request->filled('accion')) {
            $query->where('accion', 'like', '%' . $request->accion . '%');
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }
        if ($request->filled('hora_inicio')) {
            $query->whereTime('hora', '>=', $request->hora_inicio);
        }
        if ($request->filled('hora_fin')) {
            $query->whereTime('hora', '<=', $request->hora_fin);
        }
        if ($request->filled('seccion')) {
            $query->where('seccion', 'like', '%' . $request->seccion . '%');
        }

        $historial = $query->orderByDesc('fecha')->orderByDesc('hora')->paginate(20);

        // Obtener listas para los selects
        $usuarios = DB::table('historial')->distinct()->pluck('usuario')->filter()->sort()->values();
        $roles = DB::table('historial')->distinct()->pluck('rol')->filter()->sort()->values();
        $secciones = DB::table('historial')->distinct()->pluck('seccion')->filter()->sort()->values();

        return view('historial.index', compact('historial', 'usuarios', 'roles', 'secciones'));
    }
}
