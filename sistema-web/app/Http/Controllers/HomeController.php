<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receta;
use App\Helpers\HistorialHelper;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        \App\Helpers\HistorialHelper::registrar(
            'Consultó dashboard',
            'Accedió al panel principal del sistema.',
            'Dashboard'
        );
        $platos = \App\Models\Receta::where('visible', true)->get();
        return view('home', compact('platos'));
    }
}
