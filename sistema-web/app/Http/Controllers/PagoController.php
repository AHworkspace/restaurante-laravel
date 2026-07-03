<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Consumidor;
use App\Models\Consumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pago::with(['consumidor', 'usuarioRegistro']);

        if ($request->has('consumidor_id') && $request->consumidor_id) {
            $query->where('consumidor_id', $request->consumidor_id);
        }

        if ($request->has('buscar_consumidor') && $request->buscar_consumidor) {
            $buscar = $request->buscar_consumidor;
            $query->whereHas('consumidor', function($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('ci', 'like', "%{$buscar}%");
            });
        }

        if ($request->has('tipo_pago') && $request->tipo_pago) {
            $query->where('tipo_pago', $request->tipo_pago);
        }

        if ($request->has('metodo_pago') && $request->metodo_pago) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('fecha_pago', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('fecha_pago', '<=', $request->fecha_hasta);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')
                      ->orderBy('hora_pago', 'desc')
                      ->paginate(50);

        $consumidores = Consumidor::where('activo', true)->orderBy('nombre_completo')->get();

        return view('pagos.index', compact('pagos', 'consumidores'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load(['consumidor.fuerza', 'consumidor.institucion', 'consumidor.grado', 'consumos.receta', 'consumos.tipoComida', 'usuarioRegistro']);
        return view('pagos.show', compact('pago'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $consumidorId = $request->get('consumidor_id');
        $consumidor = $consumidorId ? Consumidor::find($consumidorId) : null;
        $fechaPendientes = $request->get('fecha_pendientes', now()->toDateString());

        $consumidores = Consumidor::where('activo', true)->orderBy('nombre_completo')->get();

        // Si hay consumidor seleccionado, cargar sus consumos pendientes
        $consumosPendientes = collect();
        $saldoAdelantado = 0;
        $saldoPendiente = 0;

        if ($consumidor) {
            $consumosPendientes = $consumidor->consumos()
                                             ->whereIn('estado_pago', ['pendiente', 'parcial'])
                                             ->with('receta', 'tipoComida')
                                             ->orderBy('fecha_consumo')
                                             ->orderBy('hora_consumo')
                                             ->get();
            $saldoAdelantado = $consumidor->saldoAdelantadoDisponible();
            $saldoPendiente = $consumidor->saldoPendiente();
        }

        $pendientesDelDia = Consumo::with(['consumidor.fuerza', 'consumidor.grado', 'receta', 'insumo'])
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->whereDate('fecha_consumo', $fechaPendientes)
            ->get()
            ->groupBy('consumidor_id')
            ->map(function($consumosCliente) {
                $consumidor = $consumosCliente->first()->consumidor;
                $resumenPlatos = $consumosCliente->groupBy('receta_id')->map(function($itemsReceta) {
                    $receta = $itemsReceta->first()->receta;
                    $cantidad = $itemsReceta->sum('cantidad');
                    return ($receta ? $receta->nombre : 'Plato') . ' x' . $cantidad;
                })->values()->take(3)->implode(', ');

                return [
                    'consumidor' => $consumidor,
                    'total_pendiente' => $consumosCliente->sum(fn ($consumo) => $consumo->saldoPendiente()),
                    'cantidad_consumos' => $consumosCliente->count(),
                    'resumen_platos' => $resumenPlatos,
                ];
            })
            ->values()
            ->sortByDesc('total_pendiente')
            ->values();

        return view('pagos.create', compact(
            'consumidores',
            'consumidor',
            'consumosPendientes',
            'saldoAdelantado',
            'saldoPendiente',
            'pendientesDelDia',
            'fechaPendientes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'consumidor_id' => 'required|exists:consumidores,id',
            'monto' => 'required|numeric|min:0.01',
            'tipo_pago' => 'required|in:consumo_especifico,adelanto,cuenta_periodo',
            'metodo_pago' => 'required|in:efectivo,qr,transferencia',
            'usar_saldo_adelantado' => 'nullable|boolean',
            'completar_restante' => 'nullable|boolean',
            'periodo_pagado' => 'nullable|string|max:2000',
            'fecha_pago' => 'nullable|date',
            'hora_pago' => 'nullable|date_format:H:i',
            'referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
            'consumos_ids' => 'nullable|array', // IDs de consumos a pagar
            'consumos_ids.*' => 'exists:consumos,id',
        ]);

        // Importante: la regla 'boolean' puede devolver strings dependiendo del input,
        // por eso convertimos explícitamente.
        $usarSaldoAdelantado = filter_var($validated['usar_saldo_adelantado'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $completarRestante = filter_var($validated['completar_restante'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $montoSolicitadoOriginal = (float) $validated['monto'];
        $montoUsarAdelanto = 0.0;
        $montoPagoDirecto = $montoSolicitadoOriginal;
        $restanteSinCubrir = 0.0;

        // Si se desea cubrir consumos usando el saldo adelantado, aplicamos adelanto y
        // decidimos si el restante se paga ahora (mixto) o queda pendiente.
        if ($usarSaldoAdelantado && in_array($validated['tipo_pago'], ['consumo_especifico', 'cuenta_periodo'], true)) {
            $consumidor = Consumidor::findOrFail($validated['consumidor_id']);
            $saldoDisponible = (float) $consumidor->saldoAdelantadoDisponible();
            $montoUsarAdelanto = min($montoSolicitadoOriginal, $saldoDisponible);
            $restanteSinCubrir = max(0.0, $montoSolicitadoOriginal - $montoUsarAdelanto);

            if ($montoUsarAdelanto <= 0) {
                return redirect()->back()->withInput()->with('error', 'Saldo adelantado insuficiente para cubrir este pago.');
            }

            // Si no se completa restante, este movimiento refleja solo el adelanto aplicado.
            // Si se completa restante, refleja el pago directo adicional.
            if ($restanteSinCubrir > 0.00001) {
                $montoPagoDirecto = $completarRestante ? $restanteSinCubrir : 0.0;
                $validated['monto'] = $completarRestante ? $montoPagoDirecto : $montoUsarAdelanto;
            } else {
                // El adelanto cubre todo: se mantiene el monto original para trazabilidad.
                $montoPagoDirecto = 0.0;
                $validated['monto'] = $montoUsarAdelanto;
            }
        }

        DB::beginTransaction();

        try {
            $fechaPago = $validated['fecha_pago'] ?? now()->toDateString();
            $horaPago = $validated['hora_pago'] ?? now()->format('H:i:s');

            $pago = Pago::create([
                'consumidor_id' => $validated['consumidor_id'],
                'monto' => $validated['monto'],
                'tipo_pago' => $validated['tipo_pago'],
                'metodo_pago' => $validated['metodo_pago'],
                'periodo_pagado' => $validated['periodo_pagado'] ?? null,
                'fecha_pago' => $fechaPago,
                'hora_pago' => $horaPago,
                'referencia' => $validated['referencia'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'usuario_registro_id' => Auth::id(),
            ]);

            // Determinar consumos objetivo según tipo de pago
            $consumosObjetivo = collect();
            if ($validated['tipo_pago'] === 'consumo_especifico') {
                if ($request->has('consumos_ids')) {
                    $consumosObjetivo = Consumo::whereIn('id', $request->consumos_ids)
                        ->where('consumidor_id', $validated['consumidor_id'])
                        ->whereIn('estado_pago', ['pendiente', 'parcial'])
                        ->orderBy('fecha_consumo')
                        ->orderBy('hora_consumo')
                        ->get();
                } else {
                    $consumosObjetivo = Consumo::where('consumidor_id', $validated['consumidor_id'])
                        ->whereIn('estado_pago', ['pendiente', 'parcial'])
                        ->orderBy('fecha_consumo')
                        ->orderBy('hora_consumo')
                        ->get();
                }
            } elseif ($validated['tipo_pago'] === 'cuenta_periodo') {
                $consumosObjetivo = collect($this->obtenerConsumosPeriodo(
                    $validated['consumidor_id'],
                    $validated['periodo_pagado']
                ))->sortBy(function($c) {
                    return ($c->fecha_consumo ? $c->fecha_consumo->format('Y-m-d') : '') . ' ' . ($c->hora_consumo ?? '');
                })->values();
                if ($consumosObjetivo->isEmpty()) {
                    throw new \RuntimeException('No hay consumos pendientes en el periodo seleccionado.');
                }
                $saldoPeriodo = $consumosObjetivo->sum(fn ($consumo) => $consumo->saldoPendiente());
                if ($montoSolicitadoOriginal > $saldoPeriodo + 0.00001) {
                    throw new \RuntimeException('El monto no puede superar el saldo pendiente del periodo.');
                }
            }

            // 1) Aplicar adelanto (si se pidió)
            if ($usarSaldoAdelantado && $consumosObjetivo->count() > 0) {
                $this->aplicarSaldoAdelantadoACubrirConsumos(
                    (int) $validated['consumidor_id'],
                    $consumosObjetivo,
                    (float) $montoUsarAdelanto,
                    $pago
                );
            }

            // 2) Aplicar pago directo (normal o mixto)
            $montoDirectoAplicar = (!$usarSaldoAdelantado)
                ? (float) $validated['monto']
                : (float) $montoPagoDirecto;

            if ($consumosObjetivo->count() > 0 && $montoDirectoAplicar > 0.00001) {
                $montoRestante = $montoDirectoAplicar;

                foreach ($consumosObjetivo as $consumo) {
                    if ($montoRestante <= 0) break;

                    $totalPagadoActual = (float) DB::table('pagos_consumos')
                        ->where('consumo_id', $consumo->id)
                        ->sum('monto_aplicado');
                    $pendienteConsumo = max(0.0, (float) $consumo->total - $totalPagadoActual);
                    if ($pendienteConsumo <= 0) continue;

                    $montoAplicar = min($pendienteConsumo, $montoRestante);
                    if ($montoAplicar <= 0) continue;

                    $pago->consumos()->attach($consumo->id, [
                        'monto_aplicado' => $montoAplicar
                    ]);

                    $totalPagado = (float) DB::table('pagos_consumos')
                        ->where('consumo_id', $consumo->id)
                        ->sum('monto_aplicado');

                    if ($totalPagado + 0.00001 >= (float) $consumo->total) {
                        $consumo->estado_pago = 'pagado';
                    } else {
                        $consumo->estado_pago = $totalPagado > 0 ? 'parcial' : 'pendiente';
                    }
                    $consumo->save();

                    $montoRestante -= $montoAplicar;
                }
            }

            DB::commit();

            $tipoLegible = match ($validated['tipo_pago']) {
                'consumo_especifico' => 'consumos específicos',
                'cuenta_periodo' => 'cuenta por período',
                'adelanto' => 'saldo adelantado',
                default => $validated['tipo_pago'],
            };
            $resumenConsumos = $consumosObjetivo->take(5)->map(function ($consumo) {
                return $consumo->producto_nombre.' #'.$consumo->id;
            })->implode(', ');
            \App\Helpers\HistorialHelper::registrar(
                'Registró pago',
                "Cliente: {$pago->consumidor->nombre_completo}. Modalidad: {$tipoLegible}. Monto: Bs ".number_format((float) $validated['monto'], 2).'. '.
                (! empty($validated['periodo_pagado']) ? "Período: {$validated['periodo_pagado']}. " : '').
                ($resumenConsumos ? "Consumos: {$resumenConsumos}. " : '').
                ($usarSaldoAdelantado ? 'Saldo adelantado aplicado: Bs '.number_format($montoUsarAdelanto, 2).'. ' : '').
                'Saldo pendiente final: Bs '.number_format($pago->consumidor->fresh()->saldoPendiente(), 2).'.',
                'Pagos'
            );

            return redirect()->route('pagos.index')
                ->with('success', 'Pago registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Obtener consumos de un período específico
     */
    public function consumosPeriodo(Request $request)
    {
        $datos = $request->validate([
            'consumidor_id' => ['required', 'exists:consumidores,id'],
            'tipo' => ['required', 'in:dia,semana,mes,rango'],
            'valores' => ['nullable', 'string', 'max:1500'],
            'desde' => ['nullable', 'string', 'max:10'],
            'hasta' => ['nullable', 'string', 'max:10'],
        ]);
        if (! empty($datos['valores'])) {
            $valores = collect(explode(',', $datos['valores']))->filter()->unique()->values();
            $periodo = 'seleccion:'.$datos['tipo'].':'.$valores->implode(',');
        } elseif (! empty($datos['desde'])) {
            $periodo = $datos['tipo'].':'.$datos['desde'].':'.($datos['hasta'] ?: $datos['desde']);
        } else {
            return response()->json(['message' => 'Selecciona al menos un periodo.'], 422);
        }
        $consumos = $this->obtenerConsumosPeriodo((int) $datos['consumidor_id'], $periodo);

        return response()->json([
            'periodo' => $periodo,
            'total_pendiente' => round($consumos->sum(fn ($consumo) => $consumo->saldoPendiente()), 2),
            'consumos' => $consumos->map(fn ($consumo) => [
                'id' => $consumo->id,
                'fecha' => $consumo->fecha_consumo?->format('d/m/Y'),
                'hora' => Carbon::parse($consumo->hora_consumo)->format('H:i'),
                'plato' => $consumo->producto_nombre,
                'tipo' => $consumo->tipoComida?->nombre ?? 'Sin clasificar',
                'cantidad' => (int) $consumo->cantidad,
                'total' => (float) $consumo->total,
                'pagado' => $consumo->montoPagado(),
                'saldo' => $consumo->saldoPendiente(),
                'estado' => $consumo->estado_pago,
            ])->values(),
        ]);
    }

    private function obtenerConsumosPeriodo($consumidorId, $periodo)
    {
        if (preg_match('/^seleccion:(dia|semana|mes|rango):(.+)$/', (string) $periodo, $seleccion)) {
            $rangos = collect(explode(',', $seleccion[2]))->filter()->unique()->map(function ($valor) use ($seleccion) {
                try {
                    return $this->rangoPeriodo($seleccion[1], $valor, $valor);
                } catch (\Throwable $e) {
                    return null;
                }
            })->filter()->values();
            if ($rangos->isEmpty()) return collect();

            return Consumo::where('consumidor_id', $consumidorId)
                ->whereIn('estado_pago', ['pendiente', 'parcial'])
                ->where(function ($query) use ($rangos) {
                    foreach ($rangos as [$desde, $hasta]) $query->orWhereBetween('fecha_consumo', [$desde, $hasta]);
                })
                ->with(['receta', 'tipoComida', 'pagos'])
                ->orderBy('fecha_consumo')->orderBy('hora_consumo')->get();
        }

        if (! preg_match('/^(dia|semana|mes):([^:]+):([^:]+)$/', (string) $periodo, $partes)) {
            return $this->obtenerConsumosPeriodoLegacy($consumidorId, $periodo);
        }

        try {
            [$desde, $hasta] = $this->rangoPeriodo($partes[1], $partes[2], $partes[3]);
        } catch (\Throwable $e) {
            return collect();
        }

        return Consumo::where('consumidor_id', $consumidorId)
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->whereBetween('fecha_consumo', [$desde, $hasta])
            ->with(['receta', 'tipoComida', 'pagos'])
            ->orderBy('fecha_consumo')->orderBy('hora_consumo')->get();
    }

    private function rangoPeriodo(string $tipo, string $desde, string $hasta): array
    {
        if ($tipo === 'rango') {
            if (! str_contains($desde, '~')) throw new \InvalidArgumentException('Rango invalido.');
            [$inicioRango, $finRango] = explode('~', $desde, 2);
            $inicio = Carbon::createFromFormat('Y-m-d', $inicioRango)->startOfDay();
            $fin = Carbon::createFromFormat('Y-m-d', $finRango)->endOfDay();
        } elseif ($tipo === 'dia') {
            $inicio = Carbon::createFromFormat('Y-m-d', $desde)->startOfDay();
            $fin = Carbon::createFromFormat('Y-m-d', $hasta)->endOfDay();
        } elseif ($tipo === 'semana') {
            if (! preg_match('/^(\d{4})-W(\d{2})$/', $desde, $inicioSemana)
                || ! preg_match('/^(\d{4})-W(\d{2})$/', $hasta, $finSemana)) {
                throw new \InvalidArgumentException('Semana invalida.');
            }
            $inicio = Carbon::now()->setISODate((int) $inicioSemana[1], (int) $inicioSemana[2])->startOfWeek();
            $fin = Carbon::now()->setISODate((int) $finSemana[1], (int) $finSemana[2])->endOfWeek();
        } else {
            $inicio = Carbon::createFromFormat('Y-m', $desde)->startOfMonth();
            $fin = Carbon::createFromFormat('Y-m', $hasta)->endOfMonth();
        }

        if ($inicio->greaterThan($fin)) throw new \InvalidArgumentException('Periodo invertido.');
        return [$inicio->toDateString(), $fin->toDateString()];
    }

    private function obtenerConsumosPeriodoLegacy($consumidorId, $periodo)
    {
        $consumos = Consumo::where('consumidor_id', $consumidorId)
                          ->whereIn('estado_pago', ['pendiente', 'parcial']);

        if (!$periodo) {
            return collect();
        }

        // Interpretar período
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo)) {
            // Formato de fecha: "2025-01-15" (día específico)
            $consumos->whereDate('fecha_consumo', $periodo);
        } elseif (preg_match('/^\d{4}-\d{2}-semana\d+$/', $periodo)) {
            // Formato de semana: "2025-01-semana2"
            // Extraer año y mes
            preg_match('/^(\d{4})-(\d{2})-semana(\d+)$/', $periodo, $matches);
            $anio = $matches[1];
            $mes = $matches[2];
            $numeroSemana = (int)$matches[3];

            // Calcular rango de fechas de la semana
            $primerDiaMes = \Carbon\Carbon::create($anio, $mes, 1);
            $primerDiaSemana = $primerDiaMes->copy()->addWeeks($numeroSemana - 1)->startOfWeek();
            $ultimoDiaSemana = $primerDiaSemana->copy()->endOfWeek();

            $consumos->whereBetween('fecha_consumo', [
                $primerDiaSemana->toDateString(),
                $ultimoDiaSemana->toDateString()
            ]);
        } elseif (preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            // Formato de mes: "2025-01"
            $consumos->whereYear('fecha_consumo', substr($periodo, 0, 4))
                    ->whereMonth('fecha_consumo', substr($periodo, 5, 2));
        } else {
            // Formato no reconocido, retornar vacío
            return collect();
        }

        return $consumos->get();
    }

    /**
     * Aplica saldo adelantado (pagos.tipo_pago = 'adelanto') contra consumos pendientes.
     *
     * - Vincula consumos con pagos tipo 'adelanto' con su monto real aplicado (esto alimenta el saldo a favor).
     * - Si se recibe un $pagoDisplay (el pago normal/consumo que está registrando el cajero),
     *   también se adjuntan los consumos a ese pago pero con monto_aplicado = 0 para que
     *   se vea el detalle en pagos/show sin alterar cálculos por sumas.
     */
    private function aplicarSaldoAdelantadoACubrirConsumos(int $consumidorId, $consumos, float $montoDisponible, ?Pago $pagoDisplay = null): void
    {
        if ($montoDisponible <= 0) return;

        $montoRestante = $montoDisponible;

        // Adelantos: aplicamos en orden FIFO (más antiguos primero).
        $adelantos = Pago::where('consumidor_id', $consumidorId)
            ->where('tipo_pago', 'adelanto')
            ->orderBy('fecha_pago')
            ->orderBy('hora_pago')
            ->get();

        $saldoRestantePorPago = [];
        foreach ($adelantos as $adelanto) {
            $aplicado = DB::table('pagos_consumos')
                ->where('pago_id', $adelanto->id)
                ->sum('monto_aplicado');

            $saldoRestantePorPago[$adelanto->id] = max(0, (float)$adelanto->monto - (float)$aplicado);
        }

        $consumosOrdenados = collect($consumos)->sortBy(function($c) {
            $fecha = $c->fecha_consumo ? $c->fecha_consumo->format('Y-m-d') : '';
            $hora = $c->hora_consumo ?? '';
            return $fecha . ' ' . $hora;
        })->values();

        $consumosVinculadosDisplay = [];

        foreach ($consumosOrdenados as $consumo) {
            if ($montoRestante <= 0) break;

            $pagadoHastaAhora = DB::table('pagos_consumos')
                ->where('consumo_id', $consumo->id)
                ->sum('monto_aplicado');

            $pendienteConsumo = max(0, (float)$consumo->total - (float)$pagadoHastaAhora);
            if ($pendienteConsumo <= 0) continue;

            $paraEsteConsumo = min($pendienteConsumo, $montoRestante);
            if ($paraEsteConsumo <= 0) continue;

            foreach ($adelantos as $adelanto) {
                if ($paraEsteConsumo <= 0 || $montoRestante <= 0) break;

                $saldoPago = $saldoRestantePorPago[$adelanto->id] ?? 0;
                if ($saldoPago <= 0) continue;

                $aplicar = min($saldoPago, $paraEsteConsumo);
                if ($aplicar <= 0) continue;

                // Vincular el adelanto con el consumo.
                $adelanto->consumos()->attach($consumo->id, [
                    'monto_aplicado' => $aplicar
                ]);

                $saldoRestantePorPago[$adelanto->id] -= $aplicar;
                $paraEsteConsumo -= $aplicar;
                $montoRestante -= $aplicar;
            }

            // Actualizar estado del consumo.
            $totalPagado = DB::table('pagos_consumos')
                ->where('consumo_id', $consumo->id)
                ->sum('monto_aplicado');

            // Para el detalle del pago normal, marcamos el consumo si terminó con monto aplicado
            // (independiente de cuánto haya sido en cada adelanto).
            if ((float) $totalPagado > 0.00001) {
                $consumosVinculadosDisplay[$consumo->id] = true;
            }

            $consumo->estado_pago = ($totalPagado + 0.00001 >= (float)$consumo->total)
                ? 'pagado'
                : ($totalPagado > 0 ? 'parcial' : 'pendiente');
            $consumo->save();
        }

        // Solo para mostrar detalle en pagos/show sin afectar sumas (monto_aplicado = 0).
        if ($pagoDisplay && !empty($consumosVinculadosDisplay)) {
            foreach (array_keys($consumosVinculadosDisplay) as $consumoId) {
                // Evitar duplicados en el pivot (y asegurar que realmente exista la fila).
                $yaExiste = DB::table('pagos_consumos')
                    ->where('pago_id', $pagoDisplay->id)
                    ->where('consumo_id', $consumoId)
                    ->exists();

                if (!$yaExiste) {
                    $pagoDisplay->consumos()->attach($consumoId, [
                        'monto_aplicado' => 0
                    ]);
                }
            }
        }
    }
}
