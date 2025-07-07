<?php

namespace App\Jobs;

use App\Models\Plano4111;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarPlano4111Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public UploadedFile $archivo;

    public function __construct(UploadedFile $archivo)
    {
        $this->archivo = $archivo;
    }

    public function handle(): void
    {
        $spreadsheet = IOFactory::load($this->archivo->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            // Saltar encabezados y filas vacías
            if (empty($row[0]) || strtolower(trim($row[0])) === 'cedula') {
                continue;
            }

            // Verificar existencia por "obligacion"
            $registro = Plano4111::where('obligacion', $row[4] ?? null)->first();

            $datos = [
                'cedula'          => $row[0] ?? null,
                'asociado'        => $row[1] ?? null,
                'modalidad'       => $row[2] ?? null,
                'calificacion'    => $row[3] ?? null,
                'telefono'        => $row[5] ?? null,
                'celular'         => $row[6] ?? null,
                'ciudad'          => $row[7] ?? null,
                'saldo_capital'   => $row[8] ?? null,
                'capital_vencido' => $row[9] ?? null,
                'dias_vencidos'   => $row[10] ?? null,
                'asesor'          => $row[11] ?? null,
            ];

            if ($registro) {
                $registro->update($datos);
            } else {
                Plano4111::create(array_merge($datos, [
                    'obligacion' => $row[4] ?? null,
                ]));
            }
        }
    }
}
