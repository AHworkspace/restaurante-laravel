<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate();
        \App\Helpers\HistorialHelper::registrar('Consultó listado de usuarios', 'Se mostró la lista de todos los usuarios del sistema.', 'Usuarios');
        return view('users.index', compact('users'));
    }


    public function store(Request $request)
    {

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = bcrypt($data['password']);

        try {
            User::create([
                'nombre' => $data['nombre'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear usuario')
                ->withInput();
        }
    }
}
