<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrediccionesController extends Controller
{
    //
    public function index()
    {
        return view('predicciones');
    }
}
