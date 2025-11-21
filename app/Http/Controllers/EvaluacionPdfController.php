<?php

namespace App\Http\Controllers;

use App\Models\EvaluacionProveedor;
use Barryvdh\DomPDF\Facade\Pdf;   // <— IMPORTANTE
use Illuminate\Http\Request;

class EvaluacionPdfController extends Controller
{
    public function pdf($id)
    {
        $evaluacion = EvaluacionProveedor::with(['proveedor', 'responsable'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.evaluacion', compact('evaluacion'))
            ->setPaper('letter');

        return $pdf->download('evaluacion_'.$evaluacion->id.'.pdf');
    }
}
