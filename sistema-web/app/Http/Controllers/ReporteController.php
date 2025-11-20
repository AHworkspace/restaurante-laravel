<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\MovimientoInventario;
use App\Models\ReportePersonalizado;
use App\Models\ReporteMovimiento;
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
        $ventaMax = Venta::with('receta')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('total')
            ->first();
        $ventaMin = Venta::with('receta')
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
                'plato' => $ventaMax->receta->nombre ?? 'N/A',
                'total' => $ventaMax->total
            ] : null,
            'venta_minima' => $ventaMin ? [
                'plato' => $ventaMin->receta->nombre ?? 'N/A',
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
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as fecha'),
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
        $ventaMax = Venta::with('receta')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('total')
            ->first();
        $ventaMin = Venta::with('receta')
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
                'plato' => $ventaMax->receta->nombre ?? 'N/A',
                'total' => $ventaMax->total
            ] : null,
            'venta_minima' => $ventaMin ? [
                'plato' => $ventaMin->receta->nombre ?? 'N/A',
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
            DB::raw('YEAR(created_at) as fecha'),
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
        $ventaMax = Venta::with('receta')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('total')
            ->first();
        $ventaMin = Venta::with('receta')
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
                'plato' => $ventaMax->receta->nombre ?? 'N/A',
                'total' => $ventaMax->total
            ] : null,
            'venta_minima' => $ventaMin ? [
                'plato' => $ventaMin->receta->nombre ?? 'N/A',
                'total' => $ventaMin->total
            ] : null
        ];
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
        $ventas = Venta::with('receta')
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $platosVendidos = $ventas->sum('cantidad');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;

        // Top 5 productos más vendidos
        $top5 = Venta::with('receta')
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->select('receta_id', DB::raw('SUM(cantidad) as total_vendido'), DB::raw('SUM(total) as ingresos'), DB::raw('COUNT(*) as num_ventas'), DB::raw('AVG(precio) as precio_promedio'))
            ->groupBy('receta_id')
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
        $ventas = Venta::with('receta')
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalVentas = $ventas->count();
        $ingresosTotales = $ventas->sum('total');
        $platosVendidos = $ventas->sum('cantidad');
        $promedioVenta = $totalVentas > 0 ? $ingresosTotales / $totalVentas : 0;

        // Top 5 productos más vendidos
        $top5 = Venta::with('receta')
            ->whereBetween('created_at', [$reporte->fecha_desde, $reporte->fecha_hasta])
            ->select('receta_id', DB::raw('SUM(cantidad) as total_vendido'), DB::raw('SUM(total) as ingresos'), DB::raw('COUNT(*) as num_ventas'), DB::raw('AVG(precio) as precio_promedio'))
            ->groupBy('receta_id')
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
}
