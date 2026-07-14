<?php

namespace App\Http\Controllers;

use App\Models\Consumidor;
use App\Models\Consumo;
use App\Models\Receta;
use App\Models\TipoComida;
use App\Models\Venta;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MenuDia;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;

class ConsumoController extends Controller
{
    public function index(Request $request)
    {
        $query = Consumo::with(['consumidor', 'receta', 'insumo', 'presentacion.insumo', 'tipoComida']);

        $query->when($request->filled('buscar'), function ($query) use ($request) {
            $buscar = $request->string('buscar')->toString();
            $query->whereHas('consumidor', fn ($q) => $q
                ->where('nombre_completo', 'like', "%{$buscar}%")
                ->orWhere('ci', 'like', "%{$buscar}%"));
        })->when($request->filled('consumidor_id'), fn ($q) => $q->where('consumidor_id', $request->consumidor_id))
            ->when($request->filled('tipo_comida_id'), fn ($q) => $q->where('tipo_comida_id', $request->tipo_comida_id))
            ->when($request->filled('receta_id'), fn ($q) => $q->where('receta_id', $request->receta_id))
            ->when($request->filled('estado_pago'), fn ($q) => $q->where('estado_pago', $request->estado_pago))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('fecha_consumo', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('fecha_consumo', '<=', $request->fecha_hasta));

        $resumen = (clone $query)->selectRaw('estado_pago, COUNT(*) cantidad, SUM(total) total')
            ->groupBy('estado_pago')->get()->keyBy('estado_pago');
        $consumos = $query->latest('fecha_consumo')->latest('hora_consumo')->paginate(25)->withQueryString();
        $tiposComida = TipoComida::where('activo', true)->orderBy('nombre')->get();
        $recetas = Receta::orderBy('nombre')->get();
        $estadisticasPorTipo = (clone $query)->reorder()->whereNotNull('tipo_comida_id')
            ->selectRaw('tipo_comida_id, COUNT(*) cantidad, SUM(total) total')
            ->groupBy('tipo_comida_id')->get()->keyBy('tipo_comida_id');

        return view('consumos.index', compact('consumos', 'resumen', 'tiposComida', 'recetas', 'estadisticasPorTipo'));
    }

    public function create(Request $request)
    {
        $recetas = Receta::orderBy('nombre')->get();
        $menus = MenuDia::with(['recetas' => fn ($query) => $query->orderBy('nombre'), 'presentacionesDirectas.insumo', 'tipoComida'])
            ->where('activo', true)
            ->orderByDesc('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return view('consumos.create', [
            'consumidores' => Consumidor::activos()->orderBy('nombre_completo')->get(),
            'recetas' => $recetas,
            'recetasJson' => $recetas->map(fn ($receta) => [
                'id' => $receta->id,
                'nombre' => $receta->nombre,
                'precio' => (float) $receta->precio,
            ])->values(),
            'menusJson' => $menus->map(fn ($menu) => [
                'id' => $menu->id,
                'titulo' => $menu->titulo,
                'fecha' => $menu->fecha->toDateString(),
                'hora_inicio' => $menu->hora_inicio,
                'hora_fin' => $menu->hora_fin,
                'tipo_comida_id' => $menu->tipo_comida_id,
                'platos' => $menu->recetas->map(fn ($receta) => [
                    'id' => $receta->id,
                    'nombre' => $receta->nombre,
                    'precio' => (float) ($receta->pivot->precio_venta ?? $receta->precio),
                    'disponible' => (int) $receta->pivot->cantidad,
                ])->concat($menu->presentacionesDirectas->map(fn($presentacion)=>['id'=>'presentacion:'.$presentacion->id,'nombre'=>$presentacion->nombre_completo,'precio'=>(float)$presentacion->pivot->precio_venta,'disponible'=>(int)$presentacion->pivot->cantidad]))->values(),
            ])->values(),
            'tiposComida' => TipoComida::where('activo', true)->orderBy('nombre')->get(),
            'consumidorSeleccionado' => $request->integer('consumidor_id') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'origen' => ['nullable', 'in:consumos,dashboard'],
            'consumidor_id' => [$request->input('origen') === 'consumos' ? 'required' : 'nullable', 'exists:consumidores,id'],
            'receta_id' => ['nullable', 'string'],
            'tipo_comida_id' => ['nullable', 'exists:tipos_comida,id'],
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'fecha_consumo' => ['nullable', 'date'],
            'hora_consumo' => ['nullable', 'date_format:H:i'],
            'observaciones' => ['nullable', 'string'],
            'items' => ['nullable', 'json'],
        ]);

        $items = collect(json_decode($datos['items'] ?? '[]', true));
        if ($items->isEmpty() && ! empty($datos['receta_id'])) {
            $items->push(['receta_id' => $datos['receta_id'], 'cantidad' => $datos['cantidad'] ?? 1]);
        }
        if ($items->isEmpty()) {
            return back()->withInput()->with('error', 'Debes agregar al menos un plato al pedido.');
        }
        foreach ($items as $item) {
            if (empty($item['receta_id']) || (int) ($item['cantidad'] ?? 0) < 1) {
                return back()->withInput()->with('error', 'Hay platos con datos incompletos en el pedido.');
            }
        }

        $fecha = $datos['fecha_consumo'] ?? now()->toDateString();
        $hora = $datos['hora_consumo'] ?? now()->format('H:i');

        $resultado = DB::transaction(function () use ($datos, $items, $fecha, $hora) {
            $consumos = collect();
            $ventas = collect();

            foreach ($items as $item) {
                if (str_starts_with((string) $item['receta_id'], 'presentacion:')) {
                    $presentacionId=(int)substr((string)$item['receta_id'],13);
                    $presentacion=InsumoPresentacion::with(['insumo.unidad_medida','movimientos'])->lockForUpdate()->findOrFail($presentacionId);$insumo=$presentacion->insumo;
                    if(!in_array($presentacion->tipo_uso,['directo','mixto'],true)) abort(422,'Esta presentacion no esta clasificada para venta directa.');
                    $cantidad=(int)$item['cantidad'];$cantidadBase=$cantidad;
                    $stock=$presentacion->stockDisponible();
                    if($stock+0.0001<$cantidadBase) abort(422,"No hay suficiente stock de {$insumo->nombre}. Disponible: ".number_format($stock,2).' '.$insumo->unidad_medida?->abreviatura.'.');
                    $menu=!empty($item['menu_dia_id'])?MenuDia::with('presentacionesDirectas')->lockForUpdate()->findOrFail($item['menu_dia_id']):null;
                    $descontado=0;
                    if(!$menu)abort(422,'Selecciona el menú que contiene este producto directo.');$producto=$menu->presentacionesDirectas()->where('insumo_presentaciones.id',$presentacion->id)->firstOrFail();$disponible=(int)$producto->pivot->cantidad;if($disponible<$cantidad)abort(422,"Solo quedan {$disponible} unidades de {$presentacion->nombre_completo} en el menú.");$precioVenta=(float)$producto->pivot->precio_venta;$menu->presentacionesDirectas()->updateExistingPivot($presentacion->id,['cantidad'=>$disponible-$cantidad]);$descontado=$cantidad;$this->registrarVentaEnHistorialMenu($menu,$presentacion->nombre_completo,$cantidad,$disponible-$cantidad);
                    $total=$precioVenta*$cantidad;
                    if(!empty($datos['consumidor_id'])){$consumos->push(Consumo::create(['consumidor_id'=>$datos['consumidor_id'],'insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'tipo_comida_id'=>$datos['tipo_comida_id']??null,'menu_dia_id'=>$menu->id,'cantidad'=>$cantidad,'cantidad_menu_descontada'=>$descontado,'precio_unitario'=>$precioVenta,'total'=>$total,'fecha_consumo'=>$fecha,'hora_consumo'=>$hora,'estado_pago'=>'pendiente','observaciones'=>$datos['observaciones']??null,'usuario_registro_id'=>Auth::id()]));}
                    $venta=Venta::create(['insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'consumidor_id'=>$datos['consumidor_id']??null,'tipo_comida_id'=>$datos['tipo_comida_id']??null,'cantidad'=>$cantidad,'precio'=>$precioVenta,'total'=>$total,'fecha_venta'=>$fecha,'hora_venta'=>$hora,'observaciones'=>$datos['observaciones']??null]);$ventas->push($venta);
                    $movimiento=new MovimientoInventario(['tipo'=>'salida','cantidad'=>$cantidadBase,'cantidad_original'=>$cantidadBase,'cantidad_convertida'=>$cantidadBase,'unidad_medida_id'=>$presentacion->unidad_stock_id?:$insumo->unidad_medida_id,'unidad_inventario_id'=>$presentacion->unidad_stock_id?:$insumo->unidad_medida_id,'insumo_id'=>$insumo->id,'presentacion_id'=>$presentacion->id,'motivo'=>'Venta directa de '.$presentacion->nombre_completo]);$movimiento->venta_id=$venta->id;$movimiento->save();
                    continue;
                }
                $receta = Receta::with('insumos')->findOrFail((int) $item['receta_id']);
                $cantidad = (int) $item['cantidad'];
                $menu = ! empty($item['menu_dia_id'])
                    ? $this->menuParaDescontar($item['menu_dia_id'], $fecha, $datos['tipo_comida_id'] ?? null, $receta->id)
                    : null;
                $descontado = 0;

                if ($menu) {
                    $plato = $menu->recetas()->where('receta_id', $receta->id)->firstOrFail();
                    $disponible = (int) $plato->pivot->cantidad;
                    if ($disponible < $cantidad) {
                        abort(422, "No hay suficientes porciones de {$receta->nombre}. Disponibles: {$disponible}.");
                    }
                    $menu->recetas()->updateExistingPivot($receta->id, ['cantidad' => $disponible - $cantidad]);
                    $descontado = $cantidad;
                    $this->registrarVentaEnHistorialMenu(
                        $menu,
                        $receta->nombre,
                        $cantidad,
                        $disponible - $cantidad
                    );
                }

                $precioVenta = $menu ? (float) ($plato->pivot->precio_venta ?? $receta->precio) : (float) $receta->precio;
                $total = $precioVenta * $cantidad;
                if (! empty($datos['consumidor_id'])) {
                    $consumos->push(Consumo::create([
                        'consumidor_id' => $datos['consumidor_id'], 'receta_id' => $receta->id,
                        'tipo_comida_id' => $datos['tipo_comida_id'] ?? null, 'menu_dia_id' => $menu?->id,
                        'cantidad' => $cantidad, 'cantidad_menu_descontada' => $descontado,
                        'precio_unitario' => $precioVenta, 'total' => $total,
                        'fecha_consumo' => $fecha, 'hora_consumo' => $hora,
                        'estado_pago' => 'pendiente', 'observaciones' => $datos['observaciones'] ?? null,
                        'usuario_registro_id' => Auth::id(),
                    ]));
                }

                $venta = Venta::create([
                    'receta_id' => $receta->id, 'consumidor_id' => $datos['consumidor_id'] ?? null,
                    'tipo_comida_id' => $datos['tipo_comida_id'] ?? null, 'cantidad' => $cantidad,
                    'precio' => $precioVenta, 'total' => $total, 'fecha_venta' => $fecha,
                    'hora_venta' => $hora, 'observaciones' => $datos['observaciones'] ?? null,
                ]);
                $ventas->push($venta);
            }

            return ['consumos' => $consumos, 'ventas' => $ventas];
        });

        $cliente = ! empty($datos['consumidor_id'])
            ? Consumidor::find($datos['consumidor_id'])?->nombre_completo
            : 'Venta pública';
        $resumenPlatos = $items->map(function ($item) {
            if(str_starts_with((string)$item['receta_id'],'presentacion:')){$presentacion=InsumoPresentacion::with('insumo')->find((int)substr((string)$item['receta_id'],13));return ($presentacion?->nombre_completo??'Producto').' x'.(int)$item['cantidad'];}
            $receta = Receta::find($item['receta_id']); return ($receta?->nombre ?? 'Plato').' x'.(int) $item['cantidad'];
        })->implode(', ');
        $totalPedido = $resultado['ventas']->sum(fn ($venta) => (float) $venta->total);
        \App\Helpers\HistorialHelper::registrar(
            $resultado['consumos']->isNotEmpty() ? 'Registró consumo y venta' : 'Registró venta pública',
            'Cliente: '.$cliente.'. Platos: '.$resumenPlatos.'. Total: Bs '.number_format($totalPedido, 2).'. Fecha: '.$fecha.' '.$hora.'.',
            $resultado['consumos']->isNotEmpty() ? 'Consumos' : 'Ventas'
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'consumos_count' => $resultado['consumos']->count(), 'ventas_count' => $resultado['ventas']->count()]);
        }
        if ($resultado['consumos']->isNotEmpty()) {
            return redirect()->route('consumos.index')->with('success', 'Pedido registrado en consumos y ventas.');
        }
        return redirect()->route('ventas.index')->with('success', 'Venta pública registrada correctamente.');
    }

    private function menuParaDescontar($menuId, string $fecha, $tipoComidaId, int $recetaId): ?MenuDia
    {
        $query = MenuDia::whereDate('fecha', $fecha)->where('activo', true)
            ->whereHas('recetas', fn ($q) => $q->where('recetas.id', $recetaId));
        if ($menuId) {
            $query->whereKey($menuId);
        } elseif ($tipoComidaId) {
            $query->where('tipo_comida_id', $tipoComidaId);
        }
        return $query->lockForUpdate()->orderBy('hora_inicio')->first();
    }

    private function registrarVentaEnHistorialMenu(MenuDia $menu, string $plato, int $vendido, int $restante): void
    {
        $usuario = auth()->user();
        $historial = $menu->historial ?? [];
        $historial[] = [
            'fecha' => now()->timezone(config('app.timezone'))->toIso8601String(),
            'usuario' => $usuario?->name ?? $usuario?->nombre ?? $usuario?->email ?? 'Sistema',
            'accion' => 'Porciones descontadas por venta',
            'detalles' => "Plato: {$plato}. Vendidas: {$vendido}. Disponibles: {$restante}.",
        ];
        $menu->historial = $historial;
        $menu->save();
    }

    public function show(Consumo $consumo)
    {
        $consumo->load(['consumidor.fuerza', 'consumidor.institucion', 'consumidor.grado', 'receta', 'tipoComida', 'pagos']);

        return view('consumos.show', compact('consumo'));
    }

    public function destroy(Consumo $consumo)
    {
        if ($consumo->pagos()->exists()) {
            return back()->with('error', 'No se puede eliminar un consumo con pagos aplicados.');
        }

        $consumo->delete();

        return redirect()->route('consumos.index')->with('success', 'Consumo eliminado.');
    }

    public function pendientes(Consumidor $consumidor)
    {
        $consumos = $consumidor->consumos()->with(['receta', 'tipoComida'])
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->oldest('fecha_consumo')->get()->map(function (Consumo $consumo) {
                $aplicado = (float) $consumo->pagos()->sum('pagos_consumos.monto_aplicado');
                $saldo = max(0, (float) $consumo->total - $aplicado);

                return [
                    'id' => $consumo->id,
                    'descripcion' => $consumo->receta?->nombre,
                    'receta_nombre' => $consumo->receta?->nombre,
                    'tipo_comida' => $consumo->tipoComida?->nombre ?? 'N/A',
                    'fecha' => $consumo->fecha_consumo->format('d/m/Y'),
                    'hora' => \Carbon\Carbon::parse($consumo->hora_consumo)->format('H:i'),
                    'cantidad' => $consumo->cantidad,
                    'total' => number_format($saldo, 2),
                    'total_raw' => $saldo,
                    'total_original' => (float) $consumo->total,
                    'monto_pagado' => $aplicado,
                    'saldo' => $saldo,
                    'estado_pago' => $saldo <= 0 ? 'pagado' : ($aplicado > 0 ? 'parcial' : 'pendiente'),
                ];
            });

        return response()->json($consumos);
    }
}
