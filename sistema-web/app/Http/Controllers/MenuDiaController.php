<?php

namespace App\Http\Controllers;

use App\Models\MenuDia;
use App\Models\Receta;
use App\Models\TipoComida;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\TipoProduccion;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuDiaController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuDia::with(['tipoComida', 'recetas', 'presentacionesDirectas.insumo', 'usuarioCreador'])
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha', $request->fecha))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($s) => $s
                ->where('titulo', 'like', '%'.$request->buscar.'%')
                ->orWhere('descripcion', 'like', '%'.$request->buscar.'%')))
            ->when($request->filled('tipo_comida_id'), fn ($q) => $q->where('tipo_comida_id', $request->tipo_comida_id));

        $estado = $request->get('estado', 'todos');
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

        $menusDia = $query->orderByDesc('fecha')->orderBy('hora_inicio')->get();
        if ($estado === 'publicados') $menusDia = $menusDia->filter(fn ($menu) => $menu->pasaFiltroHorarioVisualizacion($hoy))->values();
        if ($estado === 'fuera_horario') $menusDia = $menusDia->filter(fn ($menu) => ! $menu->pasaFiltroHorarioVisualizacion($hoy))->values();
        $tiposComida = TipoComida::where('activo', true)->orderBy('nombre')->get();

        return view('menus-dia.index', [
            'menusDia' => $menusDia, 'tiposComida' => $tiposComida,
            'totalMenus' => $menusDia->count(),
            'totalPlatos' => $menusDia->sum(fn ($menu) => $menu->recetas->count() + $menu->presentacionesDirectas->count()),
            'menusVisibles' => $menusDia->filter->visibleParaClientesEfectivoAhora()->count(),
        ]);
    }

    public function create(Request $request)
    {
        return view('menus-dia.create', $this->datosFormulario(null, $request->get('fecha', now()->toDateString())));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $menu = DB::transaction(function () use ($request, $datos) {
            $menu = MenuDia::create(array_merge($datos, [
                'visible_para_clientes' => $request->boolean('visible_para_clientes'),
                'visible_en_horario' => $request->boolean('visible_en_horario'),
                'activo' => true, 'usuario_creador_id' => auth()->id(),
            ]));
            $menu->recetas()->sync($this->recetasPivot($request));
            $menu->presentacionesDirectas()->sync($this->presentacionesMenuPivot($request));
            $menu->load(['recetas','presentacionesDirectas.insumo']);
            $this->registrarHistorial($menu, 'Menú creado', sprintf(
                'Fecha: %s. Horario: %s - %s. Platos: %s.',
                $menu->fecha->format('d/m/Y'), $menu->hora_inicio ?: 'sin inicio',
                $menu->hora_fin ?: 'sin fin',
                $menu->recetas->map(fn ($r) => $r->nombre.' x'.$r->pivot->cantidad_inicial)->implode(', ')
            ));
            return $menu;
        });
        return redirect()->route('menus-dia.show', $menu)->with('success', 'Menu del dia creado.');
    }

    public function show(MenuDia $menuDia)
    {
        $menuDia->load(['tipoComida', 'recetas', 'presentacionesDirectas.insumo', 'usuarioCreador']);
        $tiposProduccion=TipoProduccion::orderBy('orden')->get()->keyBy('id');
        return view('menus-dia.show', compact('menuDia','tiposProduccion'));
    }

    public function edit(MenuDia $menuDia)
    {
        $menuDia->load(['recetas','presentacionesDirectas.insumo']);
        return view('menus-dia.edit', $this->datosFormulario($menuDia, $menuDia->fecha->toDateString()));
    }

    public function update(Request $request, MenuDia $menuDia)
    {
        $datos = $this->validar($request);
        DB::transaction(function () use ($request, $datos, $menuDia) {
            $menuDia->load(['recetas','presentacionesDirectas']);
            $anterior = [
                'titulo' => $menuDia->titulo, 'fecha' => $menuDia->fecha->toDateString(),
                'hora_inicio' => $menuDia->hora_inicio, 'hora_fin' => $menuDia->hora_fin,
                'tipo_comida_id' => $menuDia->tipo_comida_id,
                'visible' => (bool) $menuDia->visible_para_clientes,
                'recetas' => $menuDia->recetas->mapWithKeys(fn ($r) => [$r->id => [
                    'nombre' => $r->nombre,
                    'cantidad' => (int) ($r->pivot->cantidad ?? $r->pivot->cantidad_inicial),
                ]])->all(),
            ];
            $nuevoPivot = $this->recetasPivot($request, $menuDia);
            $nuevoPivotPresentaciones = $this->presentacionesMenuPivot($request, $menuDia);
            if (empty($nuevoPivot) && empty($nuevoPivotPresentaciones)) {
                throw ValidationException::withMessages(['recetas' => 'El menu debe mantener al menos un plato o producto directo.']);
            }
            $menuDia->update(array_merge($datos, [
                'visible_para_clientes' => $request->boolean('visible_para_clientes'),
                'visible_en_horario' => $request->boolean('visible_en_horario'),
            ]));
            $menuDia->recetas()->sync($nuevoPivot);
            $menuDia->presentacionesDirectas()->sync($nuevoPivotPresentaciones);

            $cambios = [];
            foreach (['titulo' => 'Título', 'fecha' => 'Fecha', 'hora_inicio' => 'Hora inicial', 'hora_fin' => 'Hora final', 'tipo_comida_id' => 'Tipo de comida'] as $campo => $etiqueta) {
                $nuevo = $campo === 'fecha' ? $menuDia->fecha->toDateString() : $menuDia->{$campo};
                if ((string) $anterior[$campo] !== (string) $nuevo) $cambios[] = "$etiqueta: ".($anterior[$campo] ?: 'vacío')." → ".($nuevo ?: 'vacío');
            }
            if ($anterior['visible'] !== (bool) $menuDia->visible_para_clientes) {
                $cambios[] = 'Publicación: '.($anterior['visible'] ? 'permitida' : 'oculta').' → '.($menuDia->visible_para_clientes ? 'permitida' : 'oculta');
            }
            $idsAnteriores = array_keys($anterior['recetas']);
            $idsNuevos = array_map('intval', array_keys($nuevoPivot));
            $agregados = array_diff($idsNuevos, $idsAnteriores);
            $eliminados = array_diff($idsAnteriores, $idsNuevos);
            if ($agregados) $cambios[] = 'Platos agregados: '.Receta::whereIn('id', $agregados)->pluck('nombre')->implode(', ');
            if ($eliminados) $cambios[] = 'Platos eliminados: '.collect($eliminados)->map(fn ($id) => $anterior['recetas'][$id]['nombre'])->implode(', ');
            foreach ($nuevoPivot as $id => $pivot) {
                if (isset($anterior['recetas'][$id]) && $anterior['recetas'][$id]['cantidad'] !== (int) $pivot['cantidad_inicial']) {
                    $cambios[] = 'Cantidad '.$anterior['recetas'][$id]['nombre'].': '.$anterior['recetas'][$id]['cantidad'].' → '.$pivot['cantidad_inicial'];
                }
            }
            if ($cambios) $this->registrarHistorial($menuDia, 'Menú actualizado', implode(' | ', $cambios));
        });
        return redirect()->route('menus-dia.show', $menuDia)->with('success', 'Menu actualizado.');
    }

    public function destroy(MenuDia $menuDia)
    {
        $menuDia->load('recetas');
        $this->registrarHistorial($menuDia, 'Menú desactivado', 'Platos al desactivar: '.$menuDia->recetas->pluck('nombre')->implode(', '));
        $menuDia->update(['activo' => false]);
        return redirect()->route('menus-dia.index')->with('success', 'Menu marcado como inactivo.');
    }

    public function toggleVisible(MenuDia $menuDia)
    {
        $menuDia->update(['visible_para_clientes' => ! $menuDia->visible_para_clientes]);
        $this->registrarHistorial(
            $menuDia,
            $menuDia->visible_para_clientes ? 'Publicación permitida' : 'Menú ocultado manualmente',
            $menuDia->visible_para_clientes ? 'El menú podrá mostrarse cuando coincidan su fecha y horario.' : 'El menú no se mostrará aunque coincidan su fecha y horario.'
        );
        return back()->with('success', 'Visibilidad actualizada.');
    }

    public function updateTituloExisting(Request $request, MenuDia $menuDia)
    {
        $data = $request->validate(['titulo' => ['required', 'string', 'max:100']]);
        $anterior = $menuDia->titulo;
        $menuDia->update(['titulo' => trim($data['titulo'])]);
        if ($anterior !== $menuDia->titulo) $this->registrarHistorial($menuDia, 'Título actualizado', "$anterior → {$menuDia->titulo}");

        return response()->json(['success' => true, 'titulo' => $menuDia->titulo, 'menu_id' => $menuDia->id]);
    }

    public function eliminarTitulo(MenuDia $menuDia)
    {
        $menuDia->loadMissing('tipoComida');
        $anterior = $menuDia->titulo;
        $menuDia->update(['titulo' => $menuDia->tipoComida?->nombre ?: 'Menu del Dia']);
        $this->registrarHistorial($menuDia, 'Título personalizado eliminado', "$anterior → {$menuDia->titulo}");

        return response()->json(['success' => true, 'titulo' => $menuDia->titulo, 'menu_id' => $menuDia->id]);
    }

    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:100'],
            'tipo_comida_id' => ['nullable', 'exists:tipos_comida,id'],
            'fecha' => ['required', 'date'], 'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'descripcion' => ['nullable', 'string'], 'recetas' => ['nullable', 'array'],
            'recetas.*' => ['exists:recetas,id'], 'cantidades' => ['nullable', 'array'],
            'cantidades.*' => ['integer', 'min:0'],
            'adiciones' => ['nullable', 'array'], 'adiciones.*' => ['nullable', 'integer', 'min:0'],
            'precios_recetas' => ['nullable', 'array'], 'precios_recetas.*' => ['nullable', 'numeric', 'min:0'],
            'quitar_recetas' => ['nullable', 'array'], 'quitar_recetas.*' => ['exists:recetas,id'],
            'tipos_produccion_recetas'=>['nullable','array'],'tipos_produccion_recetas.*'=>['nullable','exists:tipos_produccion,id'],
            'presentaciones_directas' => ['nullable','array'], 'presentaciones_directas.*' => ['exists:insumo_presentaciones,id'],
            'cantidades_presentaciones' => ['nullable','array'], 'cantidades_presentaciones.*' => ['integer','min:0'],
            'adiciones_presentaciones' => ['nullable','array'], 'adiciones_presentaciones.*' => ['nullable','integer','min:0'],
            'quitar_presentaciones' => ['nullable','array'], 'quitar_presentaciones.*' => ['exists:insumo_presentaciones,id'],
            'precios_presentaciones' => ['nullable','array'], 'precios_presentaciones.*' => ['nullable','numeric','min:0'],
            'tipos_produccion_presentaciones'=>['nullable','array'],'tipos_produccion_presentaciones.*'=>['nullable','exists:tipos_produccion,id'],
        ]);
        if (empty($request->input('recetas')) && empty($request->input('presentaciones_directas'))) {
            throw ValidationException::withMessages(['recetas'=>'Selecciona al menos un plato o producto directo.']);
        }
        return $datos;
    }

    private function recetasPivot(Request $request, ?MenuDia $menuDia = null): array
    {
        $pivot = [];
        $actuales = $menuDia?->recetas->keyBy('id') ?? collect();
        $quitar = array_map('intval', $request->input('quitar_recetas', []));
        foreach ($request->input('recetas', []) as $id) {
            if (in_array((int) $id, $quitar, true)) continue;
            $cantidad = $menuDia ? max(0, (int) $request->input("cantidades.$id", 0)) : max(1, (int) $request->input("cantidades.$id", 1));
            $existente = $actuales->get((int) $id);
            if ($existente) {
                $cantidadAnterior = (int) ($existente->pivot->cantidad ?? 0);
                $adiciones = $this->adicionesDesdePivot($existente->pivot->adiciones ?? null);
                $adicionActual = max(0, (int) $request->input("adiciones.$id", 0));
                if ($adicionActual > 0) $adiciones[] = $adicionActual;
                $precio = $request->input("precios_recetas.$id", $existente->pivot->precio_venta ?? $existente->precio);
                $pivot[$id] = [
                    'cantidad' => $cantidadAnterior + $adicionActual,
                    'cantidad_inicial' => (int) ($existente->pivot->cantidad_inicial ?? $cantidadAnterior),
                    'adiciones' => $this->codificarAdiciones($adiciones),
                    'precio_venta' => (float) $precio,
                    'tipo_produccion_id'=>$request->input("tipos_produccion_recetas.$id")?:null,
                ];
                continue;
            }
            $receta = Receta::find((int) $id);
            $precio = $request->input("precios_recetas.$id", $receta?->precio ?? 0);
            $pivot[$id] = ['cantidad' => $cantidad, 'cantidad_inicial' => $cantidad, 'adiciones' => null, 'precio_venta' => (float) $precio,'tipo_produccion_id'=>$request->input("tipos_produccion_recetas.$id")?:null];
        }
        return $pivot;
    }

    private function presentacionesPivot(Request $request, ?MenuDia $menuDia = null): array
    {
        $pivot=[];
        $actuales = $menuDia?->presentacionesDirectas->keyBy('id') ?? collect();
        foreach($request->input('presentaciones_directas',[]) as $id){$cantidad=max(1,(int)$request->input("cantidades_presentaciones.$id",1));$precio=$request->input("precios_presentaciones.$id");if($precio===null||$precio==='')throw ValidationException::withMessages(["precios_presentaciones.$id"=>'Indica el precio de venta de la presentación.']);$pivot[$id]=['cantidad'=>$cantidad,'cantidad_inicial'=>$cantidad,'precio_venta'=>(float)$precio,'tipo_produccion_id'=>$request->input("tipos_produccion_presentaciones.$id")?:null];}
        return $pivot;
    }

    private function presentacionesMenuPivot(Request $request, ?MenuDia $menuDia = null): array
    {
        $pivot = [];
        $actuales = $menuDia?->presentacionesDirectas->keyBy('id') ?? collect();
        $quitar = array_map('intval', $request->input('quitar_presentaciones', []));
        foreach ($request->input('presentaciones_directas', []) as $id) {
            if (in_array((int) $id, $quitar, true)) continue;
            $cantidad = $menuDia ? max(0, (int) $request->input("cantidades_presentaciones.$id", 0)) : max(1, (int) $request->input("cantidades_presentaciones.$id", 1));
            $precio = $request->input("precios_presentaciones.$id");
            if ($precio === null || $precio === '') throw ValidationException::withMessages(["precios_presentaciones.$id" => 'Indica el precio de venta de la presentacion.']);

            $existente = $actuales->get((int) $id);
            if ($existente) {
                $cantidadAnterior = (int) ($existente->pivot->cantidad ?? 0);
                $adiciones = $this->adicionesDesdePivot($existente->pivot->adiciones ?? null);
                $adicionActual = max(0, (int) $request->input("adiciones_presentaciones.$id", 0));
                if ($adicionActual > 0) $adiciones[] = $adicionActual;
                $pivot[$id] = [
                    'cantidad' => $cantidadAnterior + $adicionActual,
                    'cantidad_inicial' => (int) ($existente->pivot->cantidad_inicial ?? $cantidadAnterior),
                    'adiciones' => $this->codificarAdiciones($adiciones),
                    'precio_venta' => (float) $precio,
                    'tipo_produccion_id' => $request->input("tipos_produccion_presentaciones.$id") ?: null,
                ];
                continue;
            }

            $pivot[$id] = [
                'cantidad' => $cantidad,
                'cantidad_inicial' => $cantidad,
                'adiciones' => null,
                'precio_venta' => (float) $precio,
                'tipo_produccion_id' => $request->input("tipos_produccion_presentaciones.$id") ?: null,
            ];
        }
        return $pivot;
    }

    private function adicionesDesdePivot($valor): array
    {
        if (is_array($valor)) return array_values(array_filter(array_map('intval', $valor), fn($cantidad) => $cantidad > 0));
        if (! $valor) return [];
        $decodificado = json_decode((string) $valor, true);
        return is_array($decodificado) ? array_values(array_filter(array_map('intval', $decodificado), fn($cantidad) => $cantidad > 0)) : [];
    }

    private function codificarAdiciones(array $adiciones): ?string
    {
        $adiciones = array_values(array_filter(array_map('intval', $adiciones), fn($cantidad) => $cantidad > 0));
        return $adiciones ? json_encode($adiciones) : null;
    }

    private function datosFormulario(?MenuDia $menuDia, string $fecha): array
    {
        return [
            'menuDia' => $menuDia, 'fechaPredefinida' => $fecha,
            'tiposComida' => TipoComida::where('activo', true)->orderBy('nombre')->get(),
            'tiposProduccion'=>TipoProduccion::where('activo',true)->orderBy('orden')->orderBy('nombre')->get(),
            'recetas' => Receta::orderBy('nombre')->get(),
            'presentacionesDirectas' => InsumoPresentacion::with(['insumo.unidad_medida', 'movimientos.unidadInventario', 'unidadStockRelacion', 'unidadContenido'])
                ->where('activa',true)->whereIn('tipo_uso',['directo','mixto'])
                ->get()->sortBy(fn($p)=>$p->insumo->nombre.' '.$p->nombre)->values(),
            'seleccionadas' => $menuDia?->recetas->pluck('id')->all() ?? [],
            'cantidades' => $menuDia?->recetas->mapWithKeys(fn ($r) => [$r->id => $r->pivot->cantidad])->all() ?? [],
            'cantidadesIniciales' => $menuDia?->recetas->mapWithKeys(fn ($r) => [$r->id => $r->pivot->cantidad_inicial])->all() ?? [],
            'adicionesRecetas' => $menuDia?->recetas->mapWithKeys(fn ($r) => [$r->id => $this->adicionesDesdePivot($r->pivot->adiciones ?? null)])->all() ?? [],
            'preciosRecetas' => $menuDia?->recetas->mapWithKeys(fn ($r) => [$r->id => $r->pivot->precio_venta ?? $r->precio])->all() ?? [],
            'tiposRecetas'=>$menuDia?->recetas->mapWithKeys(fn($r)=>[$r->id=>$r->pivot->tipo_produccion_id])->all()??[],
            'presentacionesSeleccionadas' => $menuDia?->presentacionesDirectas->pluck('id')->all() ?? [],
            'cantidadesPresentaciones' => $menuDia?->presentacionesDirectas->mapWithKeys(fn($p)=>[$p->id=>$p->pivot->cantidad])->all() ?? [],
            'cantidadesInicialesPresentaciones' => $menuDia?->presentacionesDirectas->mapWithKeys(fn($p)=>[$p->id=>$p->pivot->cantidad_inicial])->all() ?? [],
            'adicionesPresentaciones' => $menuDia?->presentacionesDirectas->mapWithKeys(fn($p)=>[$p->id=>$this->adicionesDesdePivot($p->pivot->adiciones ?? null)])->all() ?? [],
            'preciosPresentaciones' => $menuDia?->presentacionesDirectas->mapWithKeys(fn($p)=>[$p->id=>$p->pivot->precio_venta])->all() ?? [],
            'tiposPresentaciones'=>$menuDia?->presentacionesDirectas->mapWithKeys(fn($p)=>[$p->id=>$p->pivot->tipo_produccion_id])->all()??[],
        ];
    }

    private function registrarHistorial(MenuDia $menu, string $accion, string $detalles): void
    {
        $usuario = auth()->user();
        $nombre = $usuario?->name ?? $usuario?->nombre ?? $usuario?->email ?? 'Sistema';
        $historial = $menu->historial ?? [];
        $historial[] = [
            'fecha' => now()->timezone(config('app.timezone'))->toIso8601String(),
            'usuario' => $nombre,
            'accion' => $accion,
            'detalles' => $detalles,
        ];
        $menu->historial = $historial;
        $menu->save();
        \App\Helpers\HistorialHelper::registrar(
            $accion,
            'Menú: '.$menu->titulo.' (ID '.$menu->id.'). '.$detalles,
            'Menús del día'
        );
    }
}
