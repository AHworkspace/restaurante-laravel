<?php

namespace App\Http\Controllers\Auth\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(){return view('auth.cliente.login');}
    public function login(Request $request)
    {
        $credenciales=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if(Auth::guard('cliente')->attempt(array_merge($credenciales,['activo'=>true]),$request->boolean('remember'))){$request->session()->regenerate();return redirect()->intended(route('cliente.inicio'));}
        return back()->withErrors(['email'=>'Las credenciales no son validas.'])->onlyInput('email');
    }
    public function logout(Request $request){Auth::guard('cliente')->logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('cliente.login');}
}
