<?php

namespace App\Http\Controllers;

use App\Models\EvaluacionProveedor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class EvaluacionPdfController extends Controller
{
    public function pdf($id)
    {
        $evaluacion = EvaluacionProveedor::with(['proveedor', 'responsable'])
            ->findOrFail($id);

        // LOGO (usa base64 para evitar problemas)
        $logoPath = public_path('images/LOGO-03.png');
        $logoSrc = File::exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(File::get($logoPath))
            : null;

        // FIRMA (usa base64 directo si ya viene así)
        $firmaSrc = null;

        if ($evaluacion->firma) {
            if (str_starts_with($evaluacion->firma, 'data:image')) {
                // Ya está en base64
                $firmaSrc = $evaluacion->firma;
            } else {
                // Firma antigua guardada como archivo
                $firmaPath = storage_path('app/public/firmas/' . $evaluacion->firma);
                if (File::exists($firmaPath)) {
                    $firmaSrc = 'data:image/png;base64,' . base64_encode(File::get($firmaPath));
                }
            }
        }

        // FIRMA VOBO (solo si está aprobado)
        $voboSrc = null;
        if ($evaluacion->vobo_fecha) {
            $voboPath = public_path('images/firmavb.jpg');
            if (File::exists($voboPath)) {
                $voboSrc = 'data:image/jpeg;base64,' . base64_encode(File::get($voboPath));
            }
        }

        $pdf = Pdf::setOption('isRemoteEnabled', true)
            ->loadView('pdf.evaluacion', compact('evaluacion', 'logoSrc', 'firmaSrc', 'voboSrc'))
            ->setPaper('letter');

        return $pdf->download('evaluacion_' . $evaluacion->id . '.pdf');
    }
}
