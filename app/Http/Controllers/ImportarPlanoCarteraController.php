<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\PlanoCarteraImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportarPlanoCarteraController extends Controller
{
    public function form()
    {
        return view('import.plano-cartera');
    }

    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls'
        ]);

        Excel::import(new PlanoCarteraImport, $request->file('archivo'));

        return redirect()->route('filament.admin.resources.erp-plano-cartera.index')
            ->with('success', 'Importación exitosa');
    }
}
