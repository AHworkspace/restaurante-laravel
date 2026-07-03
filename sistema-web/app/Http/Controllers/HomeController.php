<?php

namespace App\Http\Controllers;

use App\Models\Consumidor;
use App\Models\MenuDia;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        \App\Helpers\HistorialHelper::registrar(
            'Consultó dashboard',
            'Accedió al panel principal del sistema.',
            'Dashboard'
        );

        $fechaRaw = request()->query('fecha');
        if ($fechaRaw === null || $fechaRaw === '') {
            $fecha = now()->toDateString();
        } else {
            try {
                $fecha = Carbon::parse($fechaRaw, config('app.timezone'))->toDateString();
            } catch (\Throwable $e) {
                $fecha = now()->toDateString();
            }
        }

        // Vista /home solo para personal web. Clientes usan Consumidor (/login-cliente).
        $esCliente = false;

        // Dashboard interno: todos los menús activos del día (con platos), sin filtrar por ventana horaria.
        // La visibilidad automática por horario aplica solo en la vista cliente (MenusVisiblesParaClienteService).
        $menusDia = MenuDia::with(['recetas', 'presentacionesDirectas.insumo', 'tipoComida'])
            ->select('menus_dia.*')
            ->whereDate('fecha', $fecha)
            ->where('activo', true)
            ->where('visible_para_clientes', true)
            ->orderBy('hora_inicio')
            ->get();
        $menusDia = $menusDia->filter(fn ($menu) => $menu->visibleEnDashboard($fecha))->values();

        // Se mantiene por compatibilidad, pero el dashboard ya no muestra recetas sueltas.
        $platos = collect();

        // Obtener consumidores activos para el modal
        $consumidores = Consumidor::where('activo', true)
            ->with(['fuerza', 'institucion', 'grado'])
            ->orderBy('nombre_completo')
            ->limit(50) // Limitar a 50 para no sobrecargar
            ->get();

        $tiposProduccion=\App\Models\TipoProduccion::orderBy('orden')->get()->keyBy('id');
        return view('home', compact('menusDia', 'platos', 'fecha', 'esCliente', 'consumidores','tiposProduccion'));
    }
}
