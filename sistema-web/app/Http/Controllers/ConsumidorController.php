<?php

namespace App\Http\Controllers;

use App\Models\Consumidor;
use App\Models\Fuerza;
use App\Models\Grado;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsumidorController extends Controller
{
    public function index(Request $request)
    {
        $consumidores = Consumidor::query()
            ->with(['fuerza', 'institucion', 'grado'])
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->string('buscar')->toString();
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre_completo', 'like', "%{$buscar}%")
                        ->orWhere('ci', 'like', "%{$buscar}%")
                        ->orWhere('codigo_unico', 'like', "%{$buscar}%");
                });
            })
            ->when($request->filled('fuerza_id'), fn ($query) => $query->where('fuerza_id', $request->fuerza_id))
            ->when($request->filled('institucion_id'), fn ($query) => $query->where('institucion_id', $request->institucion_id))
            ->when($request->filled('grado_id'), fn ($query) => $query->where('grado_id', $request->grado_id))
            ->latest()->paginate(20)->withQueryString();

        \App\Helpers\HistorialHelper::registrar('Consultó consumidores', 'Visualizó el listado y estado general de cuentas de clientes.', 'Consumidores');
        return view('consumidores.index', array_merge(compact('consumidores'), $this->catalogos()));
    }

    public function buscar(Request $request)
    {
        $buscar = $request->string('q')->trim()->toString();
        if (mb_strlen($buscar) < 2) return response()->json([]);

        return response()->json(Consumidor::activos()
            ->where(fn ($query) => $query->where('nombre_completo', 'like', "%{$buscar}%")
                ->orWhere('ci', 'like', "%{$buscar}%"))
            ->orderBy('nombre_completo')->limit(12)->get(['id', 'nombre_completo', 'ci']));
    }

    public function create()
    {
        return view('consumidores.create', $this->catalogos());
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());
        $consumidor = Consumidor::create($datos);
        \App\Helpers\HistorialHelper::registrar('Registró consumidor', 'Cliente: '.$consumidor->nombre_completo.'. CI: '.$consumidor->ci.'. Código: '.($consumidor->codigo_unico ?: 'sin código').'.', 'Consumidores');

        return redirect()->route('consumidores.show', $consumidor)
            ->with('success', 'Consumidor registrado correctamente.');
    }

    public function show(Consumidor $consumidor)
    {
        $consumidor->load(['fuerza', 'institucion', 'grado', 'consumos.receta', 'consumos.pagos', 'pagos']);
        \App\Helpers\HistorialHelper::registrar('Consultó cuenta de consumidor', 'Cliente: '.$consumidor->nombre_completo.'. Saldo pendiente: Bs '.number_format($consumidor->saldoPendiente(), 2).'. Saldo adelantado: Bs '.number_format($consumidor->saldoAdelantadoDisponible(), 2).'.', 'Consumidores');

        return view('consumidores.show', compact('consumidor'));
    }

    public function edit(Consumidor $consumidor)
    {
        return view('consumidores.edit', array_merge(
            $this->catalogos(), compact('consumidor')
        ));
    }

    public function update(Request $request, Consumidor $consumidor)
    {
        $datos = $request->validate($this->reglas($consumidor));
        $consumidor->update($datos);
        \App\Helpers\HistorialHelper::registrar('Actualizó consumidor', 'Cliente: '.$consumidor->nombre_completo.'. CI: '.$consumidor->ci.'.', 'Consumidores');

        return redirect()->route('consumidores.show', $consumidor)
            ->with('success', 'Consumidor actualizado correctamente.');
    }

    public function destroy(Consumidor $consumidor)
    {
        $nombre = $consumidor->nombre_completo;
        $ci = $consumidor->ci;
        $consumidor->delete();
        \App\Helpers\HistorialHelper::registrar('Eliminó consumidor', 'Cliente: '.$nombre.'. CI: '.$ci.'.', 'Consumidores');

        return redirect()->route('consumidores.index')
            ->with('success', 'Consumidor eliminado correctamente.');
    }

    private function catalogos(): array
    {
        return [
            'fuerzas' => Fuerza::where('activo', true)->orderBy('nombre')->get(),
            'instituciones' => Institucion::where('activo', true)->orderBy('nombre')->get(),
            'grados' => Grado::where('activo', true)->orderBy('orden')->orderBy('nombre')->get(),
        ];
    }

    private function reglas(?Consumidor $consumidor = null): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:200'],
            'ci' => ['required', 'string', 'max:30', Rule::unique('consumidores')->ignore($consumidor)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('consumidores')->ignore($consumidor)],
            'codigo_unico' => ['nullable', 'string', 'max:50', Rule::unique('consumidores')->ignore($consumidor)],
            'fuerza_id' => ['nullable', 'exists:fuerzas,id'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'grado_id' => ['nullable', 'exists:grados,id'],
            'activo' => ['required', 'boolean'],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
