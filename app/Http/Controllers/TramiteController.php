<?php

namespace App\Http\Controllers;

use App\Models\Preafiliacion;
use Illuminate\Http\Request;

class TramiteController extends Controller
{
    public function index()
    {
        return view('tramites.index');
    }

    public function form(string $tipo)
    {
        return view("tramites.forms.$tipo");
    }

    public function store(Request $request, string $tipo)
    {
        match ($tipo) {
            'preafiliacion' => Preafiliacion::create($request->all()),
            default => abort(404),
        };

        return redirect('/tramites')->with('success', 'Trámite registrado con éxito. Pronto un analista de crédito se comunicará con usted.');
    }
}
