<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\Receta;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\ReporteMovimiento;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StockBajo;
use App\Notifications\StockRepuesto;
use App\Helpers\HistorialHelper;
use App\Helpers\ConversionesHelper;

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
        $unidades = UnidadMedida::all();
        $recetas = Receta::all();
        $proveedores = Proveedor::all();
        return view('movimientos.create', compact('insumos', 'unidades', 'recetas', 'proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'cantidad' => 'required|numeric|min:0.01',
            'tipo' => 'required|in:entrada,salida',
            'motivo' => 'required|string|max:100',
            'insumo_id' => 'required|exists:insumos,id',
            'unidad_medida_id' => 'nullable|exists:unidad_medidas,id',
            'costo_compra' => 'required_if:tipo,entrada|nullable|numeric|min:0',
            'proveedor_id' => 'required_if:tipo,entrada|nullable|exists:proveedores,id',
            'detalle_compra' => 'nullable|string|max:255',
            'receta_id' => 'nullable|exists:recetas,id',
            'fecha' => 'required|date',
        ]);

        // Obtener insumo y su unidad base
        $insumo = Insumo::with('unidad_medida')->find($request->insumo_id);
        $unidadBase = $insumo->unidad_medida;

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
                // No se pudo convertir, usar cantidad original pero mostrar advertencia
                $cantidadConvertida = $cantidadOriginal;
            }
        }

        // Validación para salidas: no permitir más que el stock disponible (usar cantidad convertida)
        if ($request->tipo === 'salida') {
            $stockDisponible = $insumo->getCantidadTotal();
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
            $costoCompra = $request->costo_compra;
            // Si no hay costo, usar el costo estándar calculado
            if (empty($costoCompra) || $costoCompra == 0) {
                if ($insumo->costo_estandar) {
                    // Calcular costo basado en cantidad y unidad
                    $costoCompra = round($insumo->costo_estandar * $cantidadOriginal, 2);
                } else {
                    return back()->withErrors([
                        'costo_compra' => 'Debe ingresar un costo para esta compra o definir un costo estándar para el insumo.'
                    ])->withInput();
                }
            }

            $proveedor = Proveedor::find($request->proveedor_id);
            if (!$proveedor) {
                return back()->withErrors([
                    'proveedor_id' => 'Debe seleccionar un proveedor válido.'
                ])->withInput();
            }

            try {
                $compra = Compra::create([
                    'costo_total' => $costoCompra,
                    'proveedor' => $proveedor->nombre,
                    'proveedor_id' => $request->proveedor_id,
                    'descripcion' => $request->detalle_compra,
                ]);
                $compraId = $compra->id;
            } catch (\Exception $e) {
                return back()->withErrors([
                    'error' => 'Error al crear la compra: ' . $e->getMessage()
                ])->withInput();
            }

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

        // Crear el movimiento de inventario
        $movimiento = MovimientoInventario::create([
            'cantidad' => $cantidadOriginal, // Mantener para compatibilidad
            'cantidad_original' => $cantidadOriginal,
            'cantidad_convertida' => $cantidadConvertida,
            'unidad_medida_id' => $unidadMovimientoId,
            'tipo' => $request->tipo,
            'motivo' => $motivoFinal,
            'insumo_id' => $request->insumo_id,
            'compra_id' => $compraId,
            'receta_id' => $recetaId,
            'created_at' => $request->fecha,
        ]);

        $detalles = 'Tipo: ' . $movimiento->tipo . ', Insumo: ' . $insumo->nombre . ', Cantidad: ' .
                    $cantidadOriginal . ' ' . $unidadMovimiento->abreviatura;
        if ($unidadMovimientoId != $unidadBase->id) {
            $detalles .= ' (' . number_format($cantidadConvertida, 2) . ' ' . $unidadBase->abreviatura . ')';
        }
        if ($compraId) {
            $detalles .= ', Compra #'.$compraId.' (Bs. '.$compra->costo_total.')';
        }
        HistorialHelper::registrar('Registró movimiento', $detalles, 'Movimientos');

        $insumo = Insumo::find($request->insumo_id);
        $stockActual = $insumo->getCantidadTotal();

        // Si es salida, verificar stock después de registrar el movimiento
        if ($request->tipo === 'salida') {
            if ($stockActual <= $insumo->stock_minimo) {
                $usuarios = \App\Models\User::role(['admin', 'cocinero', 'director', 'ayudante_cocina'])->get();
                \Illuminate\Support\Facades\Notification::send($usuarios, new \App\Notifications\StockBajo($insumo, $request->cantidad, now()));
            }
        }

        // Si es entrada, verificar si había alerta y notificar reposición
        if ($request->tipo === 'entrada') {
            if ($stockActual > $insumo->stock_minimo) {
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
                    $usuario->notify(new \App\Notifications\StockRepuesto($insumo, $request->cantidad, now()));
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

        $movimientos = MovimientoInventario::with(['insumo.unidad_medida', 'unidad_medida', 'compra.proveedorRel'])
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
            $costo = $movimiento->compra->costo_total ?? 0;
            return [
                'id' => $movimiento->id,
                'insumo' => $movimiento->insumo->nombre,
                'cantidad' => $movimiento->cantidad_original ?? $movimiento->cantidad,
                'unidad' => $unidad?->abreviatura ?? '',
                'tipo' => $movimiento->tipo,
                'fecha' => $movimiento->created_at->format('Y-m-d'),
                'motivo' => $movimiento->motivo,
                'costo' => (float) $costo,
                'proveedor' => optional($movimiento->compra->proveedorRel)->nombre ?? ($movimiento->compra->proveedor ?? ''),
                'detalle' => $movimiento->compra->descripcion,
            ];
        });

        $totalCosto = $detalles->sum('costo');
        $fechaDesde = $data['fecha_inicio'] ?? optional($movimientos->min('created_at'))->toDateString();
        $fechaHasta = $data['fecha_fin'] ?? optional($movimientos->max('created_at'))->toDateString();

        $reporte = ReporteMovimiento::create([
            'nombre' => $data['nombre'] ?: 'Movimientos seleccionados ' . now()->format('d/m/Y H:i'),
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
        $insumos = \App\Models\Insumo::with('unidad_medida')->get();
        $unidades = UnidadMedida::all();
        $recetas = Receta::all();
        $proveedores = Proveedor::all();
        return view('movimientos.edit', compact('movimiento', 'insumos', 'unidades', 'recetas', 'proveedores'));
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
            'costo_compra' => 'required_if:tipo,entrada|numeric|min:0',
            'proveedor_id' => 'required_if:tipo,entrada|exists:proveedores,id',
            'detalle_compra' => 'nullable|string|max:255',
            'receta_id' => 'nullable|exists:recetas,id',
            'fecha' => 'required|date',
        ]);

        // Obtener insumo y su unidad base
        $insumo = Insumo::with('unidad_medida')->find($request->insumo_id);
        $unidadBase = $insumo->unidad_medida;

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
            $stockDisponible = $insumo->getCantidadTotal();
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
            if (!$costoCompra && $insumo->costo_estandar) {
                $costoCompra = round($insumo->costo_estandar * $cantidadOriginal, 2);
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

        // Guardar referencia a la compra antes de eliminar el movimiento
        $compraId = $movimiento->compra_id;
        $compra = $movimiento->compra;

        // Eliminar primero el movimiento (esto libera la referencia de la clave foránea)
        $movimiento->delete();

        // Ahora eliminar la compra si existe y no hay otros movimientos que la referencien
        if ($compraId && $compra) {
            // Verificar si hay otros movimientos que usen esta compra
            $otrosMovimientos = MovimientoInventario::where('compra_id', $compraId)->count();
            if ($otrosMovimientos == 0) {
                $compra->delete();
            }
        }

        HistorialHelper::registrar('Eliminó movimiento', $info, 'Movimientos');
        return redirect()->route('movimientos.index')->with('success', 'Movimiento eliminado correctamente');
    }
}
