<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HistorialHelper;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        HistorialHelper::registrar('Consultó notificaciones', null, 'Alerta y Notificaciones');
        $query = \Auth::user()->notifications()->latest();
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        $notificaciones = $query->paginate(20);
        return view('notificaciones.index', compact('notificaciones'));
    }
}
