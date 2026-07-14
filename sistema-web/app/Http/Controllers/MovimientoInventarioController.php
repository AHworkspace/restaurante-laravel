<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\Receta;
use App\Models\Compra;
use App\Models\CompraLinea;
use App\Models\Proveedor;
use App\Models\ReporteMovimiento;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StockBajo;
use App\Notifications\StockRepuesto;
use App\Helpers\HistorialHelper;
use App\Helpers\ConversionesHelper;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\MovimientoInventario::with([
            'insumo.unidad_medida',
            'unidad_medida',
            'compra.proveedorRel',
            'compraLinea.marca','compraLinea.unidadInventario','compraLinea.unidadMedida','compraLinea.formatoEmpaque','compraLinea.presentacion','compraLinea.insumo.unidad_medida','presentacion','unidadInventario',
            'receta',
        ]);
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('tipo') && in_array($request->tipo, ['entrada', 'salida'])) {
            $query->where('tipo', $request->tipo);
        }
        $movimientos = $query->orderBy('created_at', 'desc')->paginate();
        return view('movimientos.index', compact('movimientos'))
            ->with('i', (request()->input('page', 1) - 1) * $movimientos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $insumos = Insumo::with('unidad_medida')->get();
        $unidades = UnidadMedida::reales();
        $recetas = Receta::all();
        $proveedores = Proveedor::all();
        $presentaciones=\App\Models\InsumoPresentacion::with(['insumo','movimientos','unidadStockRelacion'])->where('activa',true)->orderBy('nombre')->get();
        $lineasCompra = CompraLinea::with(['compra.proveedorRel', 'insumo.unidad_medida', 'presentacion', 'formatoEmpaque', 'marca', 'unidadMedida', 'unidadPrecio', 'unidadContenido', 'unidadInventario'])
            ->whereColumn('cantidad_recibida_base', '<', 'cantidad_pedida_base')
            ->whereHas('compra', fn ($q) => $q->whereNotIn('estado', ['anulada', 'recibida']))
            ->orderBy('compra_id')->get();
        return view('movimientos.create', compact('insumos', 'presentaciones', 'unidades', 'recetas', 'proveedores', 'lineasCompra'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'cantidad' => 'required|numeric|min:0',
            'cantidad_suelta' => 'nullable|numeric|min:0',
            'tipo' => 'required|in:entrada,salida',
            'motivo' => 'required|string|max:100',
            'insumo_id' => 'nullable|exists:insumos,id',
            'unidad_medida_id' => 'nullable|exists:unidad_medidas,id',
            'costo_compra' => 'nullable|numeric|min:0',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'detalle_compra' => 'nullable|string|max:255',
            'receta_id' => 'nullable|exists:recetas,id',
            'fecha' => 'required|date',
            'compra_linea_id' => 'required_if:tipo,entrada|nullable|exists:compra_lineas,id',
            'presentacion_id' => 'required_if:tipo,salida|nullable|exists:insumo_presentaciones,id',
        ]);

        $lineaCompra = null;
        if ($request->tipo === 'entrada' && ! $request->filled('compra_linea_id')) {
            return back()->withErrors(['compra_linea_id' => 'Para registrar una entrada debes seleccionar una línea de compra pendiente.'])->withInput();
        }

        if ($request->filled('compra_linea_id')) {
            if ($request->tipo !== 'entrada') return back()->withErrors(['compra_linea_id' => 'Una línea de compra solo puede recibirse como entrada.'])->withInput();
            $lineaCompra = CompraLinea::with(['compra', 'insumo', 'marca'])->findOrFail($request->compra_linea_id);
            $request->merge([
                'insumo_id' => $lineaCompra->insumo_id,
                'unidad_medida_id' => $lineaCompra->unidad_medida_id ?: $lineaCompra->insumo->unidad_medida_id,
                'presentacion_id' => $lineaCompra->presentacion_id,
            ]);
        } elseif ($request->tipo === 'salida' && $request->filled('presentacion_id')) {
            $presentacionSalida = \App\Models\InsumoPresentacion::findOrFail($request->presentacion_id);
            $request->merge(['insumo_id' => $presentacionSalida->insumo_id]);
        }

        // Obtener insumo y su unidad base
        $insumo = Insumo::with('unidad_medida')->find($request->insumo_id);
        $unidadBase = $insumo->unidad_medida;
        $presentacionId=$lineaCompra?->presentacion_id ?: ($request->presentacion_id ?: $insumo->presentacionPredeterminada()->value('id'));
        $presentacionSeleccionada=$presentacionId?\App\Models\InsumoPresentacion::with('unidadStockRelacion')->find($presentacionId):null;
        if(!$lineaCompra&&$presentacionSeleccionada?->unidadStockRelacion)$unidadBase=$presentacionSeleccionada->unidadStockRelacion;
        if($request->tipo==='salida'&&!$presentacionSeleccionada)return back()->withErrors(['presentacion_id'=>'Para registrar una salida debes seleccionar la presentación del insumo.'])->withInput();
        if($presentacionId&&\App\Models\InsumoPresentacion::whereKey($presentacionId)->where('insumo_id','!=',$insumo->id)->exists())return back()->withErrors(['presentacion_id'=>'La presentación no pertenece al insumo seleccionado.'])->withInput();

        // Determinar unidad del movimiento (si no se especifica, usar la unidad base del insumo)
        $unidadMovimientoId = $request->unidad_medida_id ?? $unidadBase->id;
        $unidadMovimiento = UnidadMedida::find($unidadMovimientoId);

        // Convertir cantidad a unidad base si es necesario
        $cantidadOriginal = $request->cantidad;
        $cantidadSuelta = (float) ($request->cantidad_suelta ?? 0);
        if ((float)$cantidadOriginal <= 0 && $cantidadSuelta <= 0) return back()->withErrors(['cantidad'=>'Debes recibir al menos un empaque o una unidad suelta.'])->withInput();
        $cantidadConvertida = $cantidadOriginal;

        if ($unidadMovimientoId != $unidadBase->id) {
            $cantidadConvertida = ConversionesHelper::convertir(
                $cantidadOriginal,
                $unidadMovimientoId,
                $unidadBase->id
            );

            // Si no hay conversión disponible, usar la cantidad original
            if ($cantidadConvertida === $cantidadOriginal && $unidadMovimientoId != $unidadBase->id) {
                // No se pudo convertir, usar cantidad original pero mostrar advertencia
                $cantidadConvertida = $cantidadOriginal;
            }
        }

        if ($lineaCompra) {
            $cantidadConvertida = ($cantidadOriginal * (float) ($lineaCompra->factor_compra_base ?: 1)) + $cantidadSuelta;
        } elseif ($cantidadConvertida === null) {
            return back()->withErrors(['unidad_medida_id' => 'No existe una conversión entre la unidad seleccionada y la unidad base del insumo.'])->withInput();
        }

        // Validación para salidas: no permitir más que el stock disponible (usar cantidad convertida)
        if ($request->tipo === 'salida') {
            $stockDisponible = $presentacionSeleccionada->stockDisponible();
            if ($cantidadConvertida > $stockDisponible) {
                return back()->withErrors([
                    'cantidad' => 'No hay suficiente stock disponible para realizar esta salida. Stock actual: ' .
                    number_format($stockDisponible, 2) . ' ' . $unidadBase->abreviatura
                ]);
            }
        }

        $motivoFinal = $request->motivo;
        $compraId = null;
        $recetaId = null;

        if ($request->tipo === 'entrada') {
            if ($lineaCompra && $cantidadConvertida > $lineaCompra->cantidad_faltante_base + 0.0001) {
                return back()->withErrors(['cantidad' => 'La recepción supera las '.number_format($lineaCompra->cantidad_faltante_base, 2).' unidades interiores pendientes.'])->withInput();
            }
            $costoCompra = $lineaCompra ? (float) $lineaCompra->costo_linea : $request->costo_compra;
            $costoEstandarPresentacion=(float)(\App\Models\InsumoPresentacion::find($presentacionId)?->costo_estandar??0);
            // Si no hay costo, usar el costo estándar calculado
            if (! $lineaCompra && (empty($costoCompra) || $costoCompra == 0)) {
                if ($costoEstandarPresentacion>0) {
                    // Calcular costo basado en cantidad y unidad
                    $costoCompra = round($costoEstandarPresentacion * $cantidadConvertida, 2);
                } else {
                    return back()->withErrors([
                        'costo_compra' => 'Debe ingresar un costo para esta compra o definir un costo estándar para el insumo.'
                    ])->withInput();
                }
            }

            $compraId = $lineaCompra?->compra_id;

            if (!$motivoFinal) {
                $motivoFinal = 'Compra de ' . $insumo->nombre;
            }
        } elseif ($request->receta_id) {
            $receta = Receta::find($request->receta_id);
            if ($receta) {
                $recetaId = $receta->id;
                $motivoFinal = trim(($motivoFinal ? $motivoFinal . ' - ' : '') . 'Receta: ' . $receta->nombre);
            }
        }

        $movimientoData = [
            'cantidad' => $cantidadOriginal, // Mantener para compatibilidad
            'cantidad_original' => $cantidadOriginal,
            'cantidad_suelta' => $cantidadSuelta,
            'cantidad_convertida' => $cantidadConvertida,
            'unidad_inventario_id' => $lineaCompra?->unidad_inventario_id ?: $unidadBase->id,
            'unidad_medida_id' => $unidadMovimientoId,
            'tipo' => $request->tipo,
            'motivo' => $motivoFinal,
            'insumo_id' => $request->insumo_id,
            'compra_id' => $compraId,
            'compra_linea_id' => $lineaCompra?->id,
            'presentacion_id' => $presentacionId,
            'receta_id' => $recetaId,
            'created_at' => $request->fecha,
        ];

        if ($lineaCompra) {
            $movimiento = DB::transaction(function () use ($lineaCompra, $cantidadOriginal, $cantidadConvertida, $movimientoData) {
                $linea = CompraLinea::lockForUpdate()->findOrFail($lineaCompra->id);
                if ($cantidadConvertida > $linea->cantidad_faltante_base + 0.0001) abort(422, 'La cantidad recibida supera el faltante de la línea de compra.');
                $movimiento = MovimientoInventario::create($movimientoData);
                $linea->increment('cantidad_recibida', $cantidadOriginal);
                $linea->increment('cantidad_recibida_base', $cantidadConvertida);
                $compra = Compra::lockForUpdate()->findOrFail($linea->compra_id);
                $compra->load('lineas');
                $recibido = $compra->lineas->sum(fn ($item) => (float) $item->cantidad_recibida_base);
                $pedido = $compra->lineas->sum(fn ($item) => (float) $item->cantidad_pedida_base);
                $compra->update([
                    'cantidad_recibida' => $recibido,
                    'estado' => $recibido <= 0 ? 'pendiente' : ($recibido + 0.0001 >= $pedido ? 'recibida' : 'parcial'),
                ]);
                return $movimiento;
            });
        } else {
            $movimiento = MovimientoInventario::create($movimientoData);
        }

        if($request->tipo==='entrada'&&$presentacionId&&$cantidadConvertida>0){
            $costoEntrada=$lineaCompra
                ? (float)$lineaCompra->costo_linea*($cantidadConvertida/max(0.0001,(float)$lineaCompra->cantidad_pedida_base))
                : (float)($costoCompra??0);
            if($costoEntrada>0)$this->actualizarCostoPromedioPresentacion($presentacionId,(float)$cantidadConvertida,$costoEntrada);
        }

        $detalles = 'Tipo: ' . $movimiento->tipo . ', Insumo: ' . $insumo->nombre . ', Cantidad: ' .
                    $cantidadOriginal . ' ' . $unidadMovimiento->abreviatura;
        if ($lineaCompra && $lineaCompra->unidad_inventario_id) {
            $detalles .= ' (' . number_format($cantidadConvertida, 2) . ' ' . $lineaCompra->unidadInventario?->abreviatura . ' ingresan al inventario)';
        } elseif ($unidadMovimientoId != $unidadBase->id) {
            $detalles .= ' (' . number_format($cantidadConvertida, 2) . ' ' . $unidadBase->abreviatura . ')';
        }
        if ($compraId) {
            $detalles .= ', Compra #'.$compraId.', línea #'.$lineaCompra->id.', faltante después de entrada: '.number_format($lineaCompra->fresh()->cantidad_faltante, 2);
        }
        HistorialHelper::registrar('Registró movimiento', $detalles, 'Movimientos');

        $insumo = Insumo::find($request->insumo_id);
        $presentacionStock=$presentacionId?\App\Models\InsumoPresentacion::with(['movimientos','unidadContenido','insumo.unidad_medida'])->find($presentacionId):null;
        $stockActual = $presentacionStock?->stockDisponible() ?? $insumo->getCantidadTotal();
        $stockMinimo = (float)($presentacionStock?->stock_minimo ?? $insumo->stock_minimo);

        // Si es salida, verificar stock después de registrar el movimiento
        if ($request->tipo === 'salida') {
            if ($stockActual <= $stockMinimo) {
                $usuarios = \App\Models\User::role(['admin', 'cocinero', 'director', 'ayudante_cocina'])->get();
                \Illuminate\Support\Facades\Notification::send($usuarios, new \App\Notifications\StockBajo($insumo, $request->cantidad, now(),$presentacionStock));
            }
        }

        // Si es entrada, verificar si había alerta y notificar reposición
        if ($request->tipo === 'entrada') {
            if ($stockActual > $stockMinimo) {
                $usuarios = \App\Models\User::role(['admin', 'cocinero', 'director', 'ayudante_cocina'])->get();
                foreach ($usuarios as $usuario) {
                    // Convertir TEXT a JSON primero, luego extraer el valor
                    $notificaciones = $usuario->unreadNotifications()
                        ->where('type', 'App\\Notifications\\StockBajo')
                        ->whereRaw("CAST(data::json->>'insumo_id' AS INTEGER) = ?", [$insumo->id])
                        ->get();
                    foreach ($notificaciones as $notificacion) {
                        $notificacion->markAsRead();
                    }
                    $usuario->notify(new \App\Notifications\StockRepuesto($insumo, $request->cantidad, now(),$presentacionStock));
                }
            }
        }

        return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado correctamente');
    }

    /**
     * Guardar una selección de movimientos como reporte de movimientos
     */
    public function guardarSeleccion(Request $request)
    {
        $data = $request->validate([
            'movimientos' => 'required|array|min:1',
            'movimientos.*' => 'integer|exists:movimiento_inventarios,id',
            'nombre' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);

        $movimientos = MovimientoInventario::with(['insumo.unidad_medida', 'unidad_medida', 'compra.proveedorRel', 'presentacion', 'unidadInventario'])
            ->whereIn('id', $data['movimientos'])
            ->get();

        if ($movimientos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los movimientos seleccionados.',
            ], 422);
        }

        $detalles = $movimientos->map(function ($movimiento) {
            $unidad = $movimiento->unidad_medida ?? $movimiento->insumo->unidad_medida;
            $costo = $movimiento->tipo === 'entrada'
                ? ($movimiento->compra->costo_total ?? 0)
                : ((float) ($movimiento->presentacion?->costo_estandar ?? 0) * (float) ($movimiento->cantidad_convertida ?? $movimiento->cantidad ?? 0));
            return [
                'id' => $movimiento->id,
                'insumo' => $movimiento->insumo->nombre,
                'presentacion' => $movimiento->presentacion?->nombre,
                'cantidad' => $movimiento->cantidad_original ?? $movimiento->cantidad,
                'unidad' => $unidad?->abreviatura ?? '',
                'tipo' => $movimiento->tipo,
                'fecha' => $movimiento->created_at->format('Y-m-d'),
                'motivo' => $movimiento->motivo,
                'costo' => (float) $costo,
                'proveedor' => optional($movimiento->compra?->proveedorRel)->nombre ?? ($movimiento->compra?->proveedor ?? ''),
                'detalle' => $movimiento->compra?->descripcion,
            ];
        });

        $totalCosto = $detalles->sum('costo');
        $fechaDesde = $data['fecha_inicio'] ?? optional($movimientos->min('created_at'))->toDateString();
        $fechaHasta = $data['fecha_fin'] ?? optional($movimientos->max('created_at'))->toDateString();

        $reporte = ReporteMovimiento::create([
            'nombre' => $data['nombre'] ?: 'Movimientos de ' . ($movimientos->pluck('tipo')->unique()->implode(', ') ?: 'inventario') . ' ' . now()->format('d/m/Y H:i'),
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'total_movimientos' => $movimientos->count(),
            'total_costo' => $totalCosto,
            'datos' => $detalles->values(),
        ]);

        HistorialHelper::registrar(
            'Guardó selección de movimientos',
            "Reporte #{$reporte->id} ({$reporte->total_movimientos} movimientos)",
            'Movimientos'
        );

        return response()->json([
            'success' => true,
            'message' => 'Selección guardada correctamente.',
            'reporte' => $reporte,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(MovimientoInventario $movimiento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MovimientoInventario $movimiento)
    {
        $movimiento->load(['compraLinea.formatoEmpaque','compraLinea.unidadMedida','compraLinea.unidadInventario','compraLinea.presentacion','compraLinea.insumo.unidad_medida']);
        $insumos = \App\Models\Insumo::with('unidad_medida')->get();
        $presentaciones=\App\Models\InsumoPresentacion::with(['insumo','movimientos','unidadStockRelacion'])->where('activa',true)->orderBy('nombre')->get();
        $unidades = UnidadMedida::reales();
        $recetas = Receta::all();
        $proveedores = Proveedor::all();
        return view('movimientos.edit', compact('movimiento', 'insumos', 'presentaciones', 'unidades', 'recetas', 'proveedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MovimientoInventario $movimiento)
    {
        $request->validate([
            'cantidad' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:entrada,salida',
            'motivo' => 'required|string|max:100',
            'insumo_id' => 'required|exists:insumos,id',
            'unidad_medida_id' => 'nullable|exists:unidad_medidas,id',
            'costo_compra' => 'nullable|numeric|min:0',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'detalle_compra' => 'nullable|string|max:255',
            'receta_id' => 'nullable|exists:recetas,id',
            'fecha' => 'required|date',
            'presentacion_id' => 'nullable|exists:insumo_presentaciones,id',
        ]);

        if ($movimiento->compra_linea_id) {
            if ($request->tipo !== 'entrada') return back()->withErrors(['tipo' => 'Una recepción de compra debe conservarse como entrada.']);
            DB::transaction(function () use ($request, $movimiento) {
                $linea = CompraLinea::lockForUpdate()->findOrFail($movimiento->compra_linea_id);
                $anterior = (float) ($movimiento->cantidad_original ?? $movimiento->cantidad);
                $nueva = (float) $request->cantidad;
                $recibidoNuevo = (float) $linea->cantidad_recibida - $anterior + $nueva;
                if ($recibidoNuevo < 0 || $recibidoNuevo > (float) $linea->cantidad_pedida) {
                    abort(422, 'La corrección supera la cantidad pedida o deja una recepción negativa.');
                }
                $linea->update(['cantidad_recibida' => $recibidoNuevo]);
                $insumo = Insumo::with('unidad_medida')->findOrFail($linea->insumo_id);
                $unidadId = $linea->unidad_medida_id ?: $insumo->unidad_medida_id;
                $convertida = $nueva * (float) ($linea->factor_compra_base ?: 1);
                $movimiento->update([
                    'cantidad' => $nueva,
                    'cantidad_original' => $nueva,
                    'cantidad_convertida' => $convertida,
                    'tipo' => 'entrada',
                    'motivo' => $request->motivo,
                    'insumo_id' => $linea->insumo_id,
                    'unidad_medida_id' => $unidadId,
                    'presentacion_id' => $linea->presentacion_id,
                    'created_at' => $request->fecha,
                ]);
                $this->actualizarEstadoCompra($linea->compra_id);
            });
            HistorialHelper::registrar('Corrigió recepción de compra', 'Movimiento #'.$movimiento->id.', compra #'.$movimiento->compra_id.', nueva cantidad: '.$request->cantidad.'.', 'Movimientos');
            return redirect()->route('movimientos.index')->with('success', 'Recepción actualizada correctamente');
        }

        // Obtener insumo y su unidad base
        $insumo = Insumo::with('unidad_medida')->find($request->insumo_id);
        $unidadBase = $insumo->unidad_medida;
        if($request->tipo==='entrada'&&!$movimiento->compra_linea_id)return back()->withErrors(['compra_linea_id'=>'Una entrada debe estar asociada a una línea de compra.'])->withInput();
        $presentacionSeleccionada=$request->presentacion_id?\App\Models\InsumoPresentacion::with('unidadStockRelacion')->find($request->presentacion_id):null;
        if($request->tipo==='salida'&&!$presentacionSeleccionada)return back()->withErrors(['presentacion_id'=>'Para registrar una salida debes seleccionar la presentación del insumo.'])->withInput();
        if($presentacionSeleccionada?->unidadStockRelacion)$unidadBase=$presentacionSeleccionada->unidadStockRelacion;
        if($request->presentacion_id&&\App\Models\InsumoPresentacion::whereKey($request->presentacion_id)->where('insumo_id','!=',$insumo->id)->exists())return back()->withErrors(['presentacion_id'=>'La presentación no pertenece al insumo seleccionado.'])->withInput();

        // Determinar unidad del movimiento (si no se especifica, usar la unidad base del insumo)
        $unidadMovimientoId = $request->unidad_medida_id ?? $unidadBase->id;
        $unidadMovimiento = UnidadMedida::find($unidadMovimientoId);

        // Convertir cantidad a unidad base si es necesario
        $cantidadOriginal = $request->cantidad;
        $cantidadConvertida = $cantidadOriginal;

        if ($unidadMovimientoId != $unidadBase->id) {
            $cantidadConvertida = ConversionesHelper::convertir(
                $cantidadOriginal,
                $unidadMovimientoId,
                $unidadBase->id
            );

            // Si no hay conversión disponible, usar la cantidad original
            if ($cantidadConvertida === $cantidadOriginal && $unidadMovimientoId != $unidadBase->id) {
                $cantidadConvertida = $cantidadOriginal;
            }
        }

        // Validación para salidas: no permitir más que el stock disponible
        if ($request->tipo === 'salida') {
            // Calcular stock disponible excluyendo el movimiento actual
            $stockDisponible = $presentacionSeleccionada->stockDisponible();
            // Si estamos editando, agregar la cantidad convertida del movimiento actual
            if ($movimiento->cantidad_convertida) {
                $stockDisponible += $movimiento->cantidad_convertida;
            } else {
                $stockDisponible += $movimiento->cantidad;
            }

            if ($cantidadConvertida > $stockDisponible) {
                return back()->withErrors([
                    'cantidad' => 'No hay suficiente stock disponible para realizar esta salida. Stock actual: ' .
                    number_format($stockDisponible, 2) . ' ' . $unidadBase->abreviatura
                ]);
            }
        }

        $motivoFinal = $request->motivo;
        $compraId = $movimiento->compra_id;
        $recetaId = $movimiento->receta_id;

        if ($request->tipo === 'entrada') {
            $costoCompra = $request->costo_compra;
            $costoEstandarPresentacion=(float)(\App\Models\InsumoPresentacion::find($request->presentacion_id)?->costo_estandar??0);
            if (!$costoCompra && $costoEstandarPresentacion>0) {
                $costoCompra = round($costoEstandarPresentacion * $cantidadConvertida, 2);
            }

            $proveedor = Proveedor::find($request->proveedor_id);

            if ($movimiento->compra_id && $movimiento->compra) {
                $movimiento->compra->update([
                    'costo_total' => $costoCompra,
                    'proveedor' => $proveedor?->nombre,
                    'proveedor_id' => $request->proveedor_id,
                    'descripcion' => $request->detalle_compra,
                ]);
                $compraId = $movimiento->compra_id;
            } else {
                $compra = Compra::create([
                    'costo_total' => $costoCompra,
                    'proveedor' => $proveedor?->nombre,
                    'proveedor_id' => $request->proveedor_id,
                    'descripcion' => $request->detalle_compra,
                ]);
                $compraId = $compra->id;
            }

            if (!$motivoFinal) {
                $motivoFinal = 'Compra de ' . $insumo->nombre;
            }
            $recetaId = null;
        } else {
            if ($movimiento->compra_id && $movimiento->compra) {
                $movimiento->compra->delete();
            }
            $compraId = null;

            if ($request->receta_id) {
                $receta = Receta::find($request->receta_id);
                if ($receta) {
                    $recetaId = $receta->id;
                    $motivoFinal = trim(($motivoFinal ? $motivoFinal . ' - ' : '') . 'Receta: ' . $receta->nombre);
                }
            } else {
                $recetaId = null;
            }
        }

        $movimiento->cantidad = $cantidadOriginal;
        $movimiento->cantidad_original = $cantidadOriginal;
        $movimiento->cantidad_convertida = $cantidadConvertida;
        $movimiento->unidad_medida_id = $unidadMovimientoId;
        $movimiento->tipo = $request->tipo;
        $movimiento->motivo = $motivoFinal;
        $movimiento->insumo_id = $request->insumo_id;
        $movimiento->presentacion_id = $request->presentacion_id ?: $insumo->presentacionPredeterminada()->value('id');
        $movimiento->compra_id = $compraId;
        $movimiento->receta_id = $recetaId;
        $movimiento->created_at = $request->fecha;
        $movimiento->save();

        $detalles = 'Tipo: ' . $movimiento->tipo . ', Insumo: ' . $insumo->nombre . ', Cantidad: ' .
                    $cantidadOriginal . ' ' . $unidadMovimiento->abreviatura;
        if ($unidadMovimientoId != $unidadBase->id) {
            $detalles .= ' (' . number_format($cantidadConvertida, 2) . ' ' . $unidadBase->abreviatura . ')';
        }
        if ($compraId) {
            $detalles .= ', Compra #' . $compraId;
        }
        HistorialHelper::registrar('Actualizó movimiento', $detalles, 'Movimientos');

        return redirect()->route('movimientos.index')->with('success', 'Movimiento actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MovimientoInventario $movimiento)
    {
        $info = 'Tipo: ' . $movimiento->tipo . ', Insumo ID: ' . $movimiento->insumo_id . ', Cantidad: ' . $movimiento->cantidad;

        DB::transaction(function () use ($movimiento) {
            $compraId = $movimiento->compra_id;
            if ($movimiento->compra_linea_id) {
                $linea = CompraLinea::lockForUpdate()->findOrFail($movimiento->compra_linea_id);
                $cantidad = (float) ($movimiento->cantidad_original ?? $movimiento->cantidad);
                $cantidadBase = (float) ($movimiento->cantidad_convertida ?? $cantidad);
                $linea->update(['cantidad_recibida' => max(0, (float) $linea->cantidad_recibida - $cantidad),'cantidad_recibida_base'=>max(0,(float)$linea->cantidad_recibida_base-$cantidadBase)]);
            }
            $movimiento->delete();
            if ($compraId) $this->actualizarEstadoCompra($compraId);
        });

        HistorialHelper::registrar('Eliminó movimiento', $info, 'Movimientos');
        return redirect()->route('movimientos.index')->with('success', 'Movimiento eliminado correctamente');
    }

    private function actualizarEstadoCompra(int $compraId): void
    {
        $compra = Compra::with('lineas')->lockForUpdate()->findOrFail($compraId);
        $recibido = $compra->lineas->sum(fn ($linea) => (float) $linea->cantidad_recibida_base);
        $pedido = $compra->lineas->sum(fn ($linea) => (float) $linea->cantidad_pedida_base);
        $compra->update([
            'cantidad_recibida' => $recibido,
            'estado' => $recibido <= 0 ? 'pendiente' : ($recibido + 0.0001 >= $pedido ? 'recibida' : 'parcial'),
        ]);
    }

    private function actualizarCostoPromedioPresentacion(int $presentacionId,float $cantidadEntrada,float $costoEntrada):void
    {
        $presentacion=\App\Models\InsumoPresentacion::with('movimientos')->find($presentacionId);if(!$presentacion)return;
        $stockActual=$presentacion->stockDisponible();$stockAnterior=max(0,$stockActual-$cantidadEntrada);
        $valorAnterior=$stockAnterior*(float)($presentacion->costo_estandar??0);
        $nuevoCosto=($valorAnterior+$costoEntrada)/max(0.0001,$stockAnterior+$cantidadEntrada);
        $presentacion->update(['costo_estandar'=>round($nuevoCosto,4)]);
    }
}
