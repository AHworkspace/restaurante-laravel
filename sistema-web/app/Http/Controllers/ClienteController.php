<?php

namespace App\Http\Controllers;

use App\Models\MenuDia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function inicio(Request $request)
    {
        $fecha=now()->timezone(config('app.timezone'))->toDateString();
        $menusDia=MenuDia::with(['tipoComida','recetas','presentacionesDirectas.insumo'])
            ->whereDate('fecha',$fecha)
            ->where('activo',true)
            ->where('visible_para_clientes',true)
            ->orderBy('hora_inicio')
            ->get()
            ->filter(fn (MenuDia $menu) => $menu->visibleEnDashboard($fecha))
            ->values();
        return view('home', [
            'menusDia' => $menusDia,
            'fecha' => $fecha,
            'esCliente' => true,
            'platos' => collect(),
            'consumidores' => collect(),
            'tiposProduccion'=>\App\Models\TipoProduccion::orderBy('orden')->get()->keyBy('id'),
        ]);
    }
    public function consumos(Request $request)
    {
        $consumidor=Auth::guard('cliente')->user();
        $query=$consumidor->consumos()->with(['receta','insumo','presentacion.insumo','tipoComida'])
            ->when($request->filled('fecha_inicio'),fn($q)=>$q->whereDate('fecha_consumo','>=',$request->fecha_inicio))
            ->when($request->filled('fecha_fin'),fn($q)=>$q->whereDate('fecha_consumo','<=',$request->fecha_fin));
        $consumos=$query->latest('fecha_consumo')->get();
        return view('cliente.consumos',['consumidor'=>$consumidor,'consumos'=>$consumos,'saldoPendiente'=>$consumidor->saldoPendiente(),'saldoAdelantado'=>$consumidor->saldoAdelantadoDisponible()]);
    }
    public function pagos()
    {
        $consumidor=Auth::guard('cliente')->user();
        $pagos=$consumidor->pagos()->with('consumos.receta')->latest('fecha_pago')->get();
        return view('cliente.pagos',['consumidor'=>$consumidor,'pagos'=>$pagos,'saldoPendiente'=>$consumidor->saldoPendiente(),'saldoAdelantado'=>$consumidor->saldoAdelantadoDisponible()]);
    }
}
