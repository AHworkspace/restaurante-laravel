<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\MovimientoInventario;
use App\Models\ReportePersonalizado;
use App\Models\ReporteMovimiento;
use App\Models\ReporteGuardado;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\Consumo;
use App\Models\MenuDia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Helpers\HistorialHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        \App\Helpers\HistorialHelper::registrar('Consultó listado de reportes', 'Se mostró la lista completa de reportes.', 'Reportes');
        return view('reportes.index');
    }

    public function getVentasData(Request $request)
    {
        $periodo = $request->get('periodo', 'semana');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $detalles = 'Periodo: ' . $periodo . ', Desde: ' . ($fechaInicio ?: 'N/A') . ', Hasta: ' . ($fechaFin ?: 'N/A');
        \App\Helpers\HistorialHelper::registrar(
            'Consultó reporte de ventas',
            $detalles,
            'Reportes'
        );

        if (in_array($periodo, ['anio', 'año'], true)) {
            return response()->json($this->getVentasAnuales($fechaInicio, $fechaFin));
        }

        switch ($periodo) {
            case 'semana':
                $datos = $this->getVentasSemanales($fechaInicio, $fechaFin);
                break;
            case 'mes':
                $datos = $this->getVentasMensuales($fechaInicio, $fechaFin);
                break;
            case 'año':
                $datos = $this->getVentasAnuales($fechaInicio, $fechaFin);
                break;
            default:
                $datos = $this->getVentasSemanales($fechaInicio, $fechaFin);
        }

        return response()->json($datos);
    }

    private function getVentasSemanales($fechaInicio = null, $fechaFin = null)
    {
        if ($fechaInicio) {
            $fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
        } else {
            $fechaInicio = Carbon::now()->subDays(6)->startOfDay();
        }
        if ($fechaFin) {
            $fechaFin = Carbon::parse($fechaFin)->endOfDay();
        } else {
            $fechaFin = Carbon::now()->endOfDay();
        }
        // Obtener datos de ventas de los últimos 7 días o rango seleccionado
        $ventas = Venta::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('SUM(total) as total_ventas'),
            DB::raw('COUNT(*) as cantidad_ventas'),
            DB::raw('SUM(cantidad) as platos_vendidos')
        )
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        // Calcular estadísticas
        $totalVentas = $ventas->sum('cantidad_ventas');
        $totalIngresos = $ventas->sum('total_ventas');
        $totalPlatosVendidos = $ventas->sum('platos_vendidos');
        $promedioVenta = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;
        // Venta más alta y más baja (por ticket individual)
        $ventaMax = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('total')
            ->first();
        $ventaMin = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('total')
            ->first();
        return [
            'datos' => $ventas,
            'estadisticas' => [
                'total_ventas' => $totalVentas,
                'ingresos_totales' => $totalIngresos,
                'promedio_venta' => $promedioVenta,
                'platos_vendidos' => $totalPlatosVendidos
            ],
            'venta_maxima' => $ventaMax ? [
                'plato' => $ventaMax->producto_nombre,
                'total' => $ventaMax->total
            ] : null,
            'venta_minima' => $ventaMin ? [
                'plato' => $ventaMin->producto_nombre,
                'total' => $ventaMin->total
            ] : null
        ];
    }

    private function getVentasMensuales($fechaInicio = null, $fechaFin = null)
    {
        if ($fechaInicio) {
            $fechaInicio = Carbon::parse($fechaInicio)->startOfMonth();
        } else {
            $fechaInicio = Carbon::now()->subMonths(11)->startOfMonth();
        }
        if ($fechaFin) {
            $fechaFin = Carbon::parse($fechaFin)->endOfMonth();
        } else {
            $fechaFin = Carbon::now()->endOfMonth();
        }
        // Obtener datos de ventas
        $ventas = Venta::select(
            $this->fechaAgrupada('mes'),
            DB::raw('SUM(total) as total_ventas'),
            DB::raw('COUNT(*) as cantidad_ventas'),
            DB::raw('SUM(cantidad) as platos_vendidos')
        )
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        // Calcular estadísticas
        $totalVentas = $ventas->sum('cantidad_ventas');
        $totalIngresos = $ventas->sum('total_ventas');
        $totalPlatosVendidos = $ventas->sum('platos_vendidos');
        $promedioVenta = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;
        // Venta más alta y más baja (por ticket individual)
        $ventaMax = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('total')
            ->first();
        $ventaMin = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('total')
            ->first();
        return [
            'datos' => $ventas,
            'estadisticas' => [
                'total_ventas' => $totalVentas,
                'ingresos_totales' => $totalIngresos,
                'promedio_venta' => $promedioVenta,
                'platos_vendidos' => $totalPlatosVendidos
            ],
            'venta_maxima' => $ventaMax ? [
                'plato' => $ventaMax->producto_nombre,
                'total' => $ventaMax->total
            ] : null,
            'venta_minima' => $ventaMin ? [
                'plato' => $ventaMin->producto_nombre,
                'total' => $ventaMin->total
            ] : null
        ];
    }

    private function getVentasAnuales($fechaInicio = null, $fechaFin = null)
    {
        if ($fechaInicio) {
            $fechaInicio = Carbon::parse($fechaInicio)->startOfYear();
        } else {
            $fechaInicio = Carbon::now()->subYears(4)->startOfYear();
        }
        if ($fechaFin) {
            $fechaFin = Carbon::parse($fechaFin)->endOfYear();
        } else {
            $fechaFin = Carbon::now()->endOfYear();
        }
        // Obtener datos de ventas
        $ventas = Venta::select(
            $this->fechaAgrupada('anio'),
            DB::raw('SUM(total) as total_ventas'),
            DB::raw('COUNT(*) as cantidad_ventas'),
            DB::raw('SUM(cantidad) as platos_vendidos')
        )
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        // Calcular estadísticas
        $totalVentas = $ventas->sum('cantidad_ventas');
        $totalIngresos = $ventas->sum('total_ventas');
        $totalPlatosVendidos = $ventas->sum('platos_vendidos');
        $promedioVenta = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;
        // Venta más alta y más baja (por ticket individual)
        $ventaMax = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('total')
            ->first();
        $ventaMin = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('total')
            ->first();
        return [
            'datos' => $ventas,
            'estadisticas' => [
                'total_ventas' => $totalVentas,
                'ingresos_totales' => $totalIngresos,
                'promedio_venta' => $promedioVenta,
                'platos_vendidos' => $totalPlatosVendidos
            ],
            'venta_maxima' => $ventaMax ? [
                'plato' => $ventaMax->producto_nombre,
                'total' => $ventaMax->total
            ] : null,
            'venta_minima' => $ventaMin ? [
                'plato' => $ventaMin->producto_nombre,
                'total' => $ventaMin->total
            ] : null
        ];
    }

    private function fechaAgrupada(string $periodo)
    {
        $driver = DB::connection()->getDriverName();

        if ($periodo === 'mes') {
            return match ($driver) {
                'pgsql' => DB::raw("to_char(created_at, 'YYYY-MM') as fecha"),
                'sqlite' => DB::raw("strftime('%Y-%m', created_at) as fecha"),
                default => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as fecha"),
            };
        }

        return match ($driver) {
            'pgsql' => DB::raw("EXTRACT(YEAR FROM created_at)::int as fecha"),
            'sqlite' => DB::raw("strftime('%Y', created_at) as fecha"),
            default => DB::raw('YEAR(created_at) as fecha'),
        };
    }

    private function getEstadisticasSemanales()
    {
        $fechaInicio = Carbon::now()->subDays(6);

        // Estadísticas de ventas
        $ventasStats = Venta::where('created_at', '>=', $fechaInicio)
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(total) as total_ventas_monto,
                AVG(total) as promedio_venta
            ')->first();

        // Estadísticas de ingresos
        $ingresosStats = MovimientoInventario::where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'entrada')
            ->count();

        return [
            'total_ventas' => $ventasStats->total_ventas ?? 0,
            'ingresos_totales' => $ingresosStats ?? 0,
            'promedio_venta' => $ventasStats->promedio_venta ?? 0
        ];
    }

    private function getEstadisticasMensuales()
    {
        $fechaInicio = Carbon::now()->startOfMonth();

        // Estadísticas de ventas
        $ventasStats = Venta::where('created_at', '>=', $fechaInicio)
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(total) as total_ventas_monto,
                AVG(total) as promedio_venta
            ')->first();

        // Estadísticas de ingresos
        $ingresosStats = MovimientoInventario::where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'entrada')
            ->count();

        return [
            'total_ventas' => $ventasStats->total_ventas ?? 0,
            'ingresos_totales' => $ingresosStats ?? 0,
            'promedio_venta' => $ventasStats->promedio_venta ?? 0
        ];
    }

    private function getEstadisticasAnuales()
    {
        $fechaInicio = Carbon::now()->startOfYear();

        // Estadísticas de ventas
        $ventasStats = Venta::where('created_at', '>=', $fechaInicio)
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(total) as total_ventas_monto,
                AVG(total) as promedio_venta
            ')->first();

        // Estadísticas de ingresos
        $ingresosStats = MovimientoInventario::where('created_at', '>=', $fechaInicio)
            ->where('tipo', 'entrada')
            ->count();

        return [
            'total_ventas' => $ventasStats->total_ventas ?? 0,
            'ingresos_totales' => $ingresosStats ?? 0,
            'promedio_venta' => $ventasStats->promedio_venta ?? 0
        ];
    }

    public function parcialSemanal() {
        return view('reportes.parcial_semanal');
    }
    public function parcialMensual() {
        return view('reportes.parcial_mensual');
    }
    public function parcialAnual() {
        return view('reportes.parcial_anual');
    }

    // ========================================
    // CU8 - CRUD DE REPORTES PERSONALIZADOS
    // ========================================

    /**
     * Guardar un reporte personalizado
     */
    public function guardarReporte(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'fecha_desde' => 'required|date',
                'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
                'descripcion' => 'nullable|string',
            ]);

            $reporte = ReportePersonalizado::create([
                'nombre' => $validated['nombre'],
                'fecha_desde' => $validated['fecha_desde'],
                'fecha_hasta' => $validated['fecha_hasta'],
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            HistorialHelper::registrar(
                'Guardó reporte personalizado',
                "Nombre: {$reporte->nombre}, Período: {$reporte->fecha_desde} a {$reporte->fecha_hasta}",
                'Reportes'
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte guardado exitosamente',
                'reporte' => $reporte
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error guardando reporte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los reportes personalizados
     */
    public function listarPersonalizados()
    {
        $reportes = ReportePersonalizado::orderBy('created_at', 'desc')->get();

        return response()->json($reportes);
    }

    /**
     * Ver detalles de un reporte personalizado
     */
    public function verPersonalizado($id)
    {
        $reporte = ReportePersonalizado::findOrFail($id);

        HistorialHelper::registrar(
            'Consultó reporte personalizado',
            "ID: {$reporte->id}, Nombre: {$reporte->nombre}",
            'Reportes'
        );

        return response()->json($reporte);
    }

    /**
     * Actualizar un reporte personalizado
     */
    public function actualizarPersonalizado(Request $request, $id)
    {
        $reporte = ReportePersonalizado::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'fecha_desde' => 'sometimes|required|date',
            'fecha_hasta' => 'sometimes|required|date',
            'descripcion' => 'nullable|string',
        ]);

        $camposActualizados = [];
        if ($request->has('nombre')) {
            $camposActualizados[] = "nombre: {$reporte->nombre} → {$request->nombre}";
            $reporte->nombre = $request->nombre;
        }
        if ($request->has('fecha_desde')) {
            $camposActualizados[] = "fecha_desde: {$reporte->fecha_desde} → {$request->fecha_desde}";
            $reporte->fecha_desde = $request->fecha_desde;
        }
        if ($request->has('fecha_hasta')) {
            $camposActualizados[] = "fecha_hasta: {$reporte->fecha_hasta} → {$request->fecha_hasta}";
            $reporte->fecha_hasta = $request->fecha_hasta;
        }
        if ($request->has('descripcion')) {
            $reporte->descripcion = $request->descripcion;
        }

        $reporte->save();

        HistorialHelper::registrar(
            'Actualizó reporte personalizado',
            "ID: {$reporte->id}, Cambios: " . implode(', ', $camposActualizados),
            'Reportes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Reporte actualizado exitosamente',
            'reporte' => $reporte
        ]);
    }

    /**
     * Eliminar un reporte personalizado
     */
    public function eliminarPersonalizado($id)
    {
        $reporte = ReportePersonalizado::findOrFail($id);
        $nombreReporte = $reporte->nombre;

        $reporte->delete();

        HistorialHelper::registrar(
            'Eliminó reporte personalizado',
            "ID: {$id}, Nombre: {$nombreReporte}",
            'Reportes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Reporte eliminado exitosamente'
        ]);
    }

    /**
     * Generar PDF de un reporte personalizado (descarga)
     */
    public function generarPDF($id)
    {
        $reporte = ReportePersonalizado::findOrFail($id);

        // Generar datos del reporte para el PDF
        $ventas = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $platosVendidos = $ventas->sum('cantidad');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;

        // Top 5 productos más vendidos
        $top5 = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->select('receta_id', 'presentacion_id', 'insumo_id', DB::raw('SUM(cantidad) as total_vendido'), DB::raw('SUM(total) as ingresos'), DB::raw('COUNT(*) as num_ventas'), DB::raw('AVG(precio) as precio_promedio'))
            ->groupBy('receta_id', 'presentacion_id', 'insumo_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $pdf = Pdf::loadView('reportes.pdf_ventas', compact('reporte', 'ventas', 'totalVentas', 'ingresosTotales', 'platosVendidos', 'promedioVenta', 'top5'));

        HistorialHelper::registrar(
            'Generó PDF de reporte personalizado',
            "ID: {$reporte->id}, Nombre: {$reporte->nombre}",
            'Reportes'
        );

        return $pdf->download("reporte_{$reporte->nombre}_{$reporte->id}.pdf");
    }

    /**
     * Obtener PDF como stream (para Java/Gmail)
     * Sin autenticación para que Java pueda acceder
     */
    public function obtenerPDF($id)
    {
        $reporte = ReportePersonalizado::findOrFail($id);

        // Generar datos del reporte para el PDF
        $ventas = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $platosVendidos = $ventas->sum('cantidad');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;

        // Top 5 productos más vendidos
        $top5 = Venta::with(['receta', 'presentacion.insumo', 'insumo'])
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->select('receta_id', 'presentacion_id', 'insumo_id', DB::raw('SUM(cantidad) as total_vendido'), DB::raw('SUM(total) as ingresos'), DB::raw('COUNT(*) as num_ventas'), DB::raw('AVG(precio) as precio_promedio'))
            ->groupBy('receta_id', 'presentacion_id', 'insumo_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $pdf = Pdf::loadView('reportes.pdf_ventas', compact('reporte', 'ventas', 'totalVentas', 'ingresosTotales', 'platosVendidos', 'promedioVenta', 'top5'));

        // Retornar PDF como stream (no como descarga)
        return $pdf->stream("reporte_{$reporte->nombre}_{$reporte->id}.pdf");
    }

    // ===========================
    // Reportes de movimientos
    // ===========================

    public function listarMovimientos()
    {
        return response()->json(ReporteMovimiento::orderBy('created_at', 'desc')->get());
    }

    public function verMovimientos($id)
    {
        $reporte = ReporteMovimiento::findOrFail($id);
        HistorialHelper::registrar(
            'Consultó reporte de movimientos',
            "ID: {$reporte->id}, Nombre: " . ($reporte->nombre ?? 'Sin nombre'),
            'Reportes'
        );
        return response()->json($reporte);
    }

    public function actualizarMovimientos(Request $request, $id)
    {
        $reporte = ReporteMovimiento::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);
        $anterior = $reporte->nombre;
        $reporte->nombre = $request->nombre;
        $reporte->save();

        HistorialHelper::registrar(
            'Actualizó reporte de movimientos',
            "ID: {$reporte->id}, Nombre: {$anterior} → {$reporte->nombre}",
            'Reportes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Reporte actualizado correctamente.',
            'reporte' => $reporte,
        ]);
    }

    public function eliminarMovimientos($id)
    {
        $reporte = ReporteMovimiento::findOrFail($id);
        $nombre = $reporte->nombre ?? "Reporte {$reporte->id}";
        $reporte->delete();

        HistorialHelper::registrar(
            'Eliminó reporte de movimientos',
            "ID: {$id}, Nombre: {$nombre}",
            'Reportes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Reporte eliminado correctamente.',
        ]);
    }

    public function pdfMovimientos($id)
    {
        $reporte = ReporteMovimiento::findOrFail($id);
        $datos = collect($reporte->datos ?? []);

        $pdf = Pdf::loadView('reportes.pdf_movimientos', [
            'reporte' => $reporte,
            'datos' => $datos,
        ]);

        HistorialHelper::registrar(
            'Descargó PDF de reporte de movimientos',
            "ID: {$reporte->id}, Nombre: " . ($reporte->nombre ?? 'Sin nombre'),
            'Reportes'
        );

        return $pdf->download("reporte_movimientos_{$reporte->id}.pdf");
    }

    public function guardarSector(Request $request)
    {
        $data = $request->validate([
            'sector' => 'required|in:compras,ventas,pagos,consumos,menus-dia',
            'nombre' => 'nullable|string|max:120',
            'filtros' => 'nullable|array',
            'ids' => 'nullable|array',
            'ids.*' => 'integer',
        ]);

        [$datos, $total, $fechaDesde, $fechaHasta, $subtipo] = $this->datosReporteSector(
            $data['sector'],
            collect($data['filtros'] ?? [])->filter(fn ($valor) => $valor !== null && $valor !== '')->toArray(),
            collect($data['ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->values()->all()
        );

        if ($datos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos para guardar con los filtros actuales.',
            ], 422);
        }

        $reporte = ReporteGuardado::create([
            'nombre' => $data['nombre'] ?: ucfirst($data['sector']) . ' ' . now()->format('d/m/Y H:i'),
            'sector' => $data['sector'],
            'subtipo' => $subtipo,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'total_registros' => $datos->count(),
            'total_monto' => $total,
            'filtros' => $data['filtros'] ?? [],
            'datos' => $datos->values(),
        ]);

        HistorialHelper::registrar(
            'Guardó reporte filtrado',
            "Sector: {$reporte->sector}. Registros: {$reporte->total_registros}. Total: Bs " . number_format((float) $reporte->total_monto, 2) . '.',
            'Reportes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Reporte guardado correctamente.',
            'reporte' => $reporte,
        ]);
    }

    public function verGuardado($id)
    {
        return response()->json(ReporteGuardado::findOrFail($id));
    }

    public function eliminarGuardado($id)
    {
        $reporte = ReporteGuardado::findOrFail($id);
        $nombre = $reporte->nombre;
        $reporte->delete();

        HistorialHelper::registrar('Eliminó reporte guardado', "ID: {$id}. Nombre: {$nombre}.", 'Reportes');

        return response()->json([
            'success' => true,
            'message' => 'Reporte eliminado correctamente.',
        ]);
    }

    public function pdfGuardado($id)
    {
        $reporte = ReporteGuardado::findOrFail($id);

        $pdf = Pdf::loadView('reportes.pdf_guardado', compact('reporte'));

        HistorialHelper::registrar('Descargó PDF de reporte guardado', "ID: {$reporte->id}. Nombre: {$reporte->nombre}.", 'Reportes');

        return $pdf->download("reporte_{$reporte->sector}_{$reporte->id}.pdf");
    }

    public function combinarGuardados(Request $request)
    {
        $data = $request->validate([
            'reportes' => 'required|array|min:2',
            'reportes.*' => 'string',
            'nombre' => 'nullable|string|max:120',
        ]);

        $reportes = collect($data['reportes'])->map(function ($token) {
            if (str_starts_with($token, 'movimiento:')) {
                $reporte = ReporteMovimiento::findOrFail((int) substr($token, 11));
                return [
                    'id' => $reporte->id,
                    'token' => $token,
                    'nombre' => $reporte->nombre ?? 'Movimientos',
                    'sector' => 'movimientos',
                    'subtipo' => null,
                    'fecha_desde' => $reporte->fecha_desde,
                    'fecha_hasta' => $reporte->fecha_hasta,
                    'total' => (float) $reporte->total_costo,
                    'datos' => collect($reporte->datos ?? [])->map(fn ($item) => [
                        'fecha' => $item['fecha'] ?? null,
                        'detalle' => ($item['insumo'] ?? 'Movimiento') . (!empty($item['presentacion']) ? ' · '.$item['presentacion'] : ''),
                        'persona' => $item['proveedor'] ?? '-',
                        'estado' => $item['tipo'] ?? 'movimiento',
                        'cantidad' => trim(($item['cantidad'] ?? '-') . ' ' . ($item['unidad'] ?? '')),
                        'total' => (float) ($item['costo'] ?? 0),
                    ])->values(),
                ];
            }

            $id = str_starts_with($token, 'guardado:') ? (int) substr($token, 9) : (int) $token;
            $reporte = ReporteGuardado::findOrFail($id);
            return [
                'id' => $reporte->id,
                'token' => 'guardado:' . $reporte->id,
                'nombre' => $reporte->nombre,
                'sector' => $reporte->sector,
                'subtipo' => $reporte->subtipo,
                'fecha_desde' => $reporte->fecha_desde,
                'fecha_hasta' => $reporte->fecha_hasta,
                'total' => (float) $reporte->total_monto,
                'datos' => collect($reporte->datos ?? []),
            ];
        });

        $datos = $reportes->flatMap(function ($reporte) {
            return collect($reporte['datos'])->map(function ($item) use ($reporte) {
                $item['detalle'] = '[' . strtoupper($reporte['sector']) . '] ' . ($item['detalle'] ?? '-');
                $item['reporte_origen'] = $reporte['nombre'];
                return $item;
            });
        })->values();

        $combinado = ReporteGuardado::create([
            'nombre' => $data['nombre'] ?: 'Reporte combinado ' . now()->format('d/m/Y H:i'),
            'sector' => 'combinado',
            'subtipo' => $reportes->pluck('sector')->unique()->implode(' + '),
            'fecha_desde' => $reportes->pluck('fecha_desde')->filter()->min(),
            'fecha_hasta' => $reportes->pluck('fecha_hasta')->filter()->max(),
            'total_registros' => $datos->count(),
            'total_monto' => (float) $reportes->sum('total'),
            'filtros' => ['reportes_origen' => $reportes->pluck('token')->values()],
            'datos' => $datos,
        ]);

        HistorialHelper::registrar(
            'Combinó reportes guardados',
            'Nuevo reporte #' . $combinado->id . '. Origen: ' . $reportes->pluck('token')->implode(', ') . '.',
            'Reportes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Reporte combinado correctamente.',
            'reporte' => $combinado,
        ]);
    }

    private function datosReporteSector(string $sector, array $filtros, array $ids = []): array
    {
        return match ($sector) {
            'compras' => $this->datosReporteCompras($filtros, $ids),
            'ventas' => $this->datosReporteVentas($filtros, $ids),
            'pagos' => $this->datosReportePagos($filtros, $ids),
            'consumos' => $this->datosReporteConsumos($filtros, $ids),
            'menus-dia' => $this->datosReporteMenusDia($filtros, $ids),
        };
    }

    private function datosReporteCompras(array $filtros, array $ids = []): array
    {
        $query = Compra::with(['proveedorRel', 'lineas.insumo', 'lineas.presentacion'])
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when($filtros['proveedor_id'] ?? null, fn ($q, $v) => $q->where('proveedor_id', $v))
            ->when($filtros['fecha_desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha_compra', '>=', $v))
            ->when($filtros['fecha_hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha_compra', '<=', $v));

        $items = $query->orderByDesc('fecha_compra')->limit(1000)->get();

        return [
            $items->map(fn ($compra) => [
                'id' => $compra->id,
                'fecha' => optional($compra->fecha_compra)->format('Y-m-d'),
                'detalle' => 'Compra ' . ($compra->numero_documento ?: '#'.$compra->id) . ' · ' . $compra->lineas->map(fn ($linea) => $linea->insumo?->nombre)->filter()->take(3)->implode(', '),
                'persona' => $compra->proveedorRel?->nombre ?: $compra->proveedor,
                'estado' => $compra->estado,
                'cantidad' => $compra->lineas->count() . ' líneas',
                'total' => (float) $compra->costo_total,
            ]),
            (float) $items->sum('costo_total'),
            $filtros['fecha_desde'] ?? optional($items->min('fecha_compra'))->toDateString(),
            $filtros['fecha_hasta'] ?? optional($items->max('fecha_compra'))->toDateString(),
            $filtros['estado'] ?? null,
        ];
    }

    private function datosReporteVentas(array $filtros, array $ids = []): array
    {
        $query = Venta::with(['receta', 'presentacion.insumo', 'insumo', 'consumidor'])
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->when($filtros['receta_id'] ?? null, fn ($q, $v) => $q->where('receta_id', $v))
            ->when($filtros['fecha_inicio'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filtros['fecha_fin'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        $items = $query->orderByDesc('created_at')->limit(1000)->get();

        return [
            $items->map(fn ($venta) => [
                'id' => $venta->id,
                'fecha' => $venta->created_at?->format('Y-m-d'),
                'detalle' => $venta->producto_nombre,
                'persona' => $venta->consumidor?->nombre_completo ?: 'Venta pública',
                'estado' => 'venta',
                'cantidad' => $venta->cantidad,
                'total' => (float) $venta->total,
            ]),
            (float) $items->sum('total'),
            $filtros['fecha_inicio'] ?? optional($items->min('created_at'))->toDateString(),
            $filtros['fecha_fin'] ?? optional($items->max('created_at'))->toDateString(),
            null,
        ];
    }

    private function datosReportePagos(array $filtros, array $ids = []): array
    {
        $query = Pago::with('consumidor')
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->when($filtros['consumidor_id'] ?? null, fn ($q, $v) => $q->where('consumidor_id', $v))
            ->when($filtros['tipo_pago'] ?? null, fn ($q, $v) => $q->where('tipo_pago', $v))
            ->when($filtros['metodo_pago'] ?? null, fn ($q, $v) => $q->where('metodo_pago', $v))
            ->when($filtros['fecha_desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha_pago', '>=', $v))
            ->when($filtros['fecha_hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha_pago', '<=', $v));

        $items = $query->orderByDesc('fecha_pago')->limit(1000)->get();

        return [
            $items->map(fn ($pago) => [
                'id' => $pago->id,
                'fecha' => optional($pago->fecha_pago)->format('Y-m-d'),
                'detalle' => $pago->referencia ?: ($pago->periodo_pagado ?: 'Pago registrado'),
                'persona' => $pago->consumidor?->nombre_completo,
                'tipo' => $pago->tipo_pago . ' / ' . $pago->metodo_pago,
                'cantidad' => '-',
                'total' => (float) $pago->monto,
            ]),
            (float) $items->sum('monto'),
            $filtros['fecha_desde'] ?? optional($items->min('fecha_pago'))->toDateString(),
            $filtros['fecha_hasta'] ?? optional($items->max('fecha_pago'))->toDateString(),
            $filtros['tipo_pago'] ?? null,
        ];
    }

    private function datosReporteConsumos(array $filtros, array $ids = []): array
    {
        $query = Consumo::with(['consumidor', 'receta', 'presentacion.insumo', 'insumo', 'tipoComida'])
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->when($filtros['consumidor_id'] ?? null, fn ($q, $v) => $q->where('consumidor_id', $v))
            ->when($filtros['tipo_comida_id'] ?? null, fn ($q, $v) => $q->where('tipo_comida_id', $v))
            ->when($filtros['receta_id'] ?? null, fn ($q, $v) => $q->where('receta_id', $v))
            ->when($filtros['estado_pago'] ?? null, fn ($q, $v) => $q->where('estado_pago', $v))
            ->when($filtros['fecha_desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha_consumo', '>=', $v))
            ->when($filtros['fecha_hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha_consumo', '<=', $v));

        $items = $query->orderByDesc('fecha_consumo')->limit(1000)->get();

        return [
            $items->map(fn ($consumo) => [
                'id' => $consumo->id,
                'fecha' => optional($consumo->fecha_consumo)->format('Y-m-d'),
                'detalle' => $consumo->producto_nombre,
                'persona' => $consumo->consumidor?->nombre_completo,
                'estado' => $consumo->estado_pago,
                'cantidad' => $consumo->cantidad,
                'total' => (float) $consumo->total,
            ]),
            (float) $items->sum('total'),
            $filtros['fecha_desde'] ?? optional($items->min('fecha_consumo'))->toDateString(),
            $filtros['fecha_hasta'] ?? optional($items->max('fecha_consumo'))->toDateString(),
            $filtros['estado_pago'] ?? null,
        ];
    }

    private function datosReporteMenusDia(array $filtros, array $ids = []): array
    {
        $query = MenuDia::with(['tipoComida', 'recetas', 'presentacionesDirectas.insumo', 'usuarioCreador'])
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->when($filtros['fecha'] ?? null, fn ($q, $v) => $q->whereDate('fecha', $v))
            ->when($filtros['buscar'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s
                ->where('titulo', 'like', '%' . $v . '%')
                ->orWhere('descripcion', 'like', '%' . $v . '%')))
            ->when($filtros['tipo_comida_id'] ?? null, fn ($q, $v) => $q->where('tipo_comida_id', $v));

        $estado = $filtros['estado'] ?? 'todos';
        $hoy = now()->timezone(config('app.timezone'))->toDateString();

        if ($estado === 'publicados') {
            $query->where('activo', true)->where('visible_para_clientes', true)->whereDate('fecha', $hoy);
        } elseif ($estado === 'ocultos') {
            $query->where('activo', true)->where('visible_para_clientes', false);
        } elseif ($estado === 'finalizados') {
            $query->where('activo', true)->whereDate('fecha', '<', $hoy);
        } elseif ($estado === 'programados') {
            $query->where('activo', true)->where('visible_para_clientes', true)->whereDate('fecha', '>', $hoy);
        } elseif ($estado === 'fuera_horario') {
            $query->where('activo', true)->where('visible_para_clientes', true)->whereDate('fecha', $hoy);
        } elseif ($estado === 'inactivos') {
            $query->where('activo', false);
        }

        $items = $query->orderByDesc('fecha')->orderBy('hora_inicio')->limit(1000)->get();
        if ($estado === 'publicados') {
            $items = $items->filter(fn ($menu) => $menu->pasaFiltroHorarioVisualizacion($hoy))->values();
        }
        if ($estado === 'fuera_horario') {
            $items = $items->filter(fn ($menu) => ! $menu->pasaFiltroHorarioVisualizacion($hoy))->values();
        }

        $datos = $items->map(function ($menu) {
            [$estadoPublicacion] = $menu->estadoPublicacion();
            $totalRecetas = $menu->recetas->sum(fn ($receta) => (float) ($receta->pivot->precio_venta ?? $receta->precio ?? 0) * (int) ($receta->pivot->cantidad_inicial ?? $receta->pivot->cantidad ?? 0));
            $totalDirectos = $menu->presentacionesDirectas->sum(fn ($presentacion) => (float) ($presentacion->pivot->precio_venta ?? 0) * (int) ($presentacion->pivot->cantidad_inicial ?? $presentacion->pivot->cantidad ?? 0));

            return [
                'id' => $menu->id,
                'fecha' => $menu->fecha?->format('Y-m-d'),
                'detalle' => $menu->titulo . ' · ' . ($menu->tipoComida?->nombre ?: 'General'),
                'persona' => $menu->usuarioCreador?->name ?? $menu->usuarioCreador?->nombre ?? '-',
                'estado' => $estadoPublicacion,
                'cantidad' => ($menu->recetas->count() + $menu->presentacionesDirectas->count()) . ' items',
                'total' => (float) ($totalRecetas + $totalDirectos),
            ];
        });

        return [
            $datos,
            (float) $datos->sum('total'),
            $filtros['fecha'] ?? optional($items->min('fecha'))->toDateString(),
            $filtros['fecha'] ?? optional($items->max('fecha'))->toDateString(),
            $estado !== 'todos' ? $estado : null,
        ];
    }
}
