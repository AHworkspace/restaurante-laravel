<?php

namespace App\Http\Controllers;

use App\Models\Fuerza;
use App\Models\Grado;
use App\Models\Institucion;
use App\Models\TipoComida;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfiguracionConsumidoresController extends Controller
{
    private const CATALOGOS = [
        'fuerzas' => Fuerza::class,
        'instituciones' => Institucion::class,
        'grados' => Grado::class,
        'tipos-comida' => TipoComida::class,
    ];

    public function index(string $catalogo = 'fuerzas')
    {
        $modelo = $this->modelo($catalogo);
        $registros = $modelo::orderBy('nombre')->get();

        return view('configuracion.catalogo', array_merge(
            compact('catalogo', 'registros'), $this->relaciones()
        ));
    }

    public function fuerzas() { return $this->index('fuerzas'); }
    public function instituciones() { return $this->index('instituciones'); }
    public function grados() { return $this->index('grados'); }
    public function tiposComida() { return $this->index('tipos-comida'); }
    public function storeFuerza(Request $request) { return $this->store($request, 'fuerzas'); }
    public function storeInstitucion(Request $request) { return $this->store($request, 'instituciones'); }
    public function storeGrado(Request $request) { return $this->store($request, 'grados'); }
    public function storeTipoComida(Request $request) { return $this->store($request, 'tipos-comida'); }
    public function updateFuerza(Request $request, int $id) { return $this->update($request, 'fuerzas', $id); }
    public function updateInstitucion(Request $request, int $id) { return $this->update($request, 'instituciones', $id); }
    public function updateGrado(Request $request, int $id) { return $this->update($request, 'grados', $id); }
    public function updateTipoComida(Request $request, int $id) { return $this->update($request, 'tipos-comida', $id); }
    public function destroyFuerza(int $id) { return $this->destroy('fuerzas', $id); }
    public function destroyInstitucion(int $id) { return $this->destroy('instituciones', $id); }
    public function destroyGrado(int $id) { return $this->destroy('grados', $id); }
    public function destroyTipoComida(int $id) { return $this->destroy('tipos-comida', $id); }

    public function store(Request $request, string $catalogo)
    {
        $modelo = $this->modelo($catalogo);
        $datos = $request->validate($this->reglas($catalogo));
        $datos['activo'] = $request->boolean('activo', true);
        $modelo::create($datos);

        return back()->with('success', 'Registro creado correctamente.');
    }

    public function update(Request $request, string $catalogo, int $id)
    {
        $modelo = $this->modelo($catalogo);
        $registro = $modelo::findOrFail($id);
        $datos = $request->validate($this->reglas($catalogo, $id));
        $datos['activo'] = $request->boolean('activo');
        $registro->update($datos);

        return back()->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(string $catalogo, int $id)
    {
        $modelo = $this->modelo($catalogo);
        $modelo::findOrFail($id)->delete();

        return back()->with('success', 'Registro eliminado correctamente.');
    }

    private function modelo(string $catalogo): string
    {
        abort_unless(isset(self::CATALOGOS[$catalogo]), 404);

        return self::CATALOGOS[$catalogo];
    }

    private function reglas(string $catalogo, ?int $id = null): array
    {
        $tabla = str_replace('-', '_', $catalogo);
        $reglas = [
            'nombre' => ['required', 'string', 'max:120', Rule::unique($tabla)->ignore($id)],
            'codigo' => ['nullable', 'string', 'max:20'],
            'descripcion' => ['nullable', 'string'],
        ];
        if ($catalogo === 'instituciones') $reglas['fuerza_id'] = ['nullable', 'exists:fuerzas,id'];
        if ($catalogo === 'grados') {
            $reglas['institucion_id'] = ['nullable', 'exists:instituciones,id'];
            $reglas['orden'] = ['nullable', 'integer', 'min:0'];
        }
        if ($catalogo === 'tipos-comida') {
            $reglas['hora_inicio'] = ['nullable', 'date_format:H:i'];
            $reglas['hora_fin'] = ['nullable', 'date_format:H:i'];
        }

        return $reglas;
    }

    private function relaciones(): array
    {
        return [
            'fuerzas' => Fuerza::where('activo', true)->orderBy('nombre')->get(),
            'instituciones' => Institucion::where('activo', true)->orderBy('nombre')->get(),
        ];
    }
}
