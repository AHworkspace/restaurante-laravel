<?php

namespace App\Http\Controllers\Auth\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Consumidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.cliente.register');
    }

    public function register(Request $request)
    {
        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:150'],
            'ci' => ['required', 'string', 'max:30', 'unique:consumidores,ci'],
            'email' => ['required', 'email', 'max:255', 'unique:consumidores,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'ci.unique' => 'Ya existe una cuenta de cliente con este CI.',
            'email.unique' => 'Ya existe una cuenta de cliente con este correo.',
            'password.confirmed' => 'La confirmacion de la contrasena no coincide.',
        ]);

        $consumidor = Consumidor::create([
            'nombre_completo' => trim($datos['nombre_completo']),
            'ci' => trim($datos['ci']),
            'email' => mb_strtolower($datos['email']),
            'password' => $datos['password'],
            'codigo_unico' => 'WEB-'.Str::upper(Str::random(12)),
            'activo' => true,
            'observaciones' => 'Cuenta creada por el cliente desde el registro web.',
        ]);

        Auth::guard('cliente')->login($consumidor);
        $request->session()->regenerate();

        return redirect()->route('cliente.inicio')->with('success', 'Tu cuenta fue creada correctamente.');
    }
}
