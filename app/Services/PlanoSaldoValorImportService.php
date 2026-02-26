<?php

namespace App\Services;

use App\Models\PlanoSaldoValor;
use Illuminate\Support\Carbon;
use RuntimeException;
use SplFileObject;
use ZipArchive;

class PlanoSaldoValorImportService
{
    public function import(?string $quincenalPath, string $diarioPath, mixed $fechaArchivo): array
    {
        $usarArchivoBase = is_string($quincenalPath) && trim($quincenalPath) !== '';
        if ($usarArchivoBase && !is_file($quincenalPath)) {
            throw new RuntimeException('No se encontro el archivo base (quincenal/mensual).');
        }

        if (!is_file($diarioPath)) {
            throw new RuntimeException('No se encontro el archivo diario.');
        }

        [$diarioByCredito, $diarioByDocumento, $registrosDiario] = $this->indexDiario($diarioPath);

        $fechaVigencia = $this->parseDate($fechaArchivo) ?? now();
        $periodo = $fechaVigencia->format('Ym');
        $fechaConvArchivo = $fechaVigencia->format('Ymd');
        $periodoFin = now()->addDay()->format('Ymd');

        $datosRe = [];
        $datosGou = [];
        $procesados = 0;
        $creados = 0;
        $actualizados = 0;
        $ignoradosIguales = 0;
        $sinCoincidenciaDiario = 0;

        $sourceRows = $usarArchivoBase
            ? $this->readCsvAssoc($quincenalPath)
            : $this->readCsvAssoc($diarioPath);

        foreach ($sourceRows as $row) {
            $obligacion = $this->normalizeKey($row['NUMERO_CREDITO'] ?? '');
            $cc = $this->normalizeKey($row['NUMERO_DOCUMENTO'] ?? '');

            if ($obligacion === '' && $cc === '') {
                continue;
            }

            $diarioRow = [];
            if ($usarArchivoBase) {
                if ($obligacion !== '' && isset($diarioByCredito[$obligacion])) {
                    $diarioRow = $diarioByCredito[$obligacion];
                } elseif ($cc !== '' && isset($diarioByDocumento[$cc])) {
                    $diarioRow = $diarioByDocumento[$cc];
                } else {
                    $sinCoincidenciaDiario++;
                }
            } else {
                $diarioRow = $row;
            }

            $valorCuota = $this->toFloat($row['VALOR_CUOTA'] ?? $diarioRow['VALOR_CUOTA'] ?? 0);
            $diasMora = (int) round($this->toFloat($diarioRow['DIAS_MORA'] ?? $row['DIAS_MORA'] ?? 0));
            $saldoCapital = $this->toFloat($diarioRow['SALDO_CAPITAL'] ?? $row['SALDO_CAPITAL'] ?? 0);
            $totalVencido = $this->toFloat($diarioRow['TOTAL_VENCIDO'] ?? $row['TOTAL_VENCIDO'] ?? 0);
            $fechaCuota = $this->parseDate($diarioRow['FECHA_CUOTA'] ?? $row['FECHA_CUOTA'] ?? null);

            if ($saldoCapital <= 0) {
                continue;
            }

            [$valorReportar, $observacion] = $this->calcularValorReportar(
                $diasMora,
                $valorCuota,
                $totalVencido,
                $fechaCuota,
            );

            if ($valorReportar <= 0 && $totalVencido <= 0 && $valorCuota <= 0) {
                continue;
            }

            $nombres = trim((string) ($row['NOMBRES'] ?? ''));
            $apellidos = trim((string) ($row['APELLIDOS'] ?? ''));

            if ($nombres === '') {
                $nombres = 'EMPRESA';
            }

            if ($apellidos === '') {
                $apellidos = 'EMPRESA';
            }

            $modalidad = trim((string) ($row['MODALIDAD'] ?? $row['DETALLE_MODALIDAD'] ?? ''));
            $obligacion = $obligacion !== '' ? $obligacion : $this->normalizeKey($diarioRow['NUMERO_CREDITO'] ?? '');
            $cc = $cc !== '' ? $cc : $this->normalizeKey($diarioRow['NUMERO_DOCUMENTO'] ?? '');

            if ($obligacion === '' || $cc === '') {
                continue;
            }

            $payload = [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'valor_reportar' => $valorReportar,
                'valor_cuota' => $valorCuota,
                'modalidad' => $modalidad,
                'periodo' => $periodo,
                'observacion' => $observacion,
                'saldo_capital' => $saldoCapital,
                'dias_mora' => $diasMora,
                'fecha_vigencia' => $fechaVigencia->toDateString(),
            ];

            $record = PlanoSaldoValor::firstOrNew(['cc' => $cc, 'obligacion' => $obligacion]);
            $alreadyExists = $record->exists;

            if ($alreadyExists && $this->isSameAsStored($record, $payload)) {
                $ignoradosIguales++;
                continue;
            }

            $record->fill($payload);
            $record->save();

            if ($alreadyExists) {
                $actualizados++;
            } else {
                $creados++;
            }

            $valorReportarEntero = (int) round($valorReportar);
            $nombreCompleto = trim($nombres . ' ' . $apellidos);
            $rowKey = $cc . '|' . $obligacion;

            $datosRe[$rowKey] = [
                'ID_ENTIDAD' => 9,
                'ID_SUCURSAL' => 1,
                'A_OBLIGA' => $obligacion,
                'NOMBRE_CLIENTE' => $nombres,
                'APELLIDO_CLIENTE' => $apellidos,
                'GRADO' => ' ',
                'V_CUOTA' => $valorReportarEntero,
                'RECARGO' => ' ',
                'PERIODO' => $periodo,
                'DIA_CORTE' => ' ',
                'TIPO_PAGO' => 3,
                'C.C' => $cc,
                'observacion' => $observacion,
            ];

            $datosGou[$rowKey] = [
                'obligacion' => $obligacion,
                'cc' => $cc,
                'cc1' => $cc,
                'nombres' => $nombreCompleto,
                'valor_reportar' => $valorReportarEntero,
                'periodo' => $fechaConvArchivo,
                'valor_recargo' => '00000',
                'periodofin' => $periodoFin,
                'tipo_pago' => 0,
            ];

            $procesados = $creados + $actualizados;
        }

        $zipPath = null;

        if (count($datosRe) > 0) {
            $zipPath = $this->createZip(array_values($datosRe), array_values($datosGou), $fechaConvArchivo);
        }

        return [
            'procesados' => $procesados,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'ignorados_iguales' => $ignoradosIguales,
            'registros_diario' => $registrosDiario,
            'sin_coincidencia_diario' => $sinCoincidenciaDiario,
            'zip_path' => $zipPath,
        ];
    }

    private function indexDiario(string $path): array
    {
        $byCredito = [];
        $byDocumento = [];
        $count = 0;

        foreach ($this->readCsvAssoc($path) as $row) {
            $count++;

            $credito = $this->normalizeKey($row['NUMERO_CREDITO'] ?? '');
            $documento = $this->normalizeKey($row['NUMERO_DOCUMENTO'] ?? '');

            if ($credito !== '') {
                $byCredito[$credito] = $row;
            }

            if ($documento !== '') {
                $byDocumento[$documento] = $row;
            }
        }

        return [$byCredito, $byDocumento, $count];
    }

    private function createZip(array $datosRe, array $datosGou, string $fechaConvArchivo): string
    {
        $zip = new ZipArchive();
        $zipFileName = 'planos_procesados_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('No fue posible crear el archivo ZIP de salida.');
        }

        $csvRe = fopen('php://temp', 'r+');
        fwrite($csvRe, "\xEF\xBB\xBF");
        fputcsv($csvRe, array_keys($datosRe[0]));
        foreach ($datosRe as $row) {
            fputcsv($csvRe, $row);
        }
        rewind($csvRe);
        $zip->addFromString('archivo_Re.csv', stream_get_contents($csvRe));
        fclose($csvRe);

        $csvGou = fopen('php://temp', 'r+');
        fwrite($csvGou, "\xEF\xBB\xBF");

        $sumaTotal = 0;
        foreach ($datosGou as $dg) {
            $sumaTotal += (int) $dg['valor_reportar'];
        }

        $headerGou = [
            $fechaConvArchivo,
            '1000',
            'A',
            '8000803428',
            count($datosGou),
            '0',
            'RECAUDOS MICROSITIO CERRADO',
            $sumaTotal . '00',
        ];

        fputcsv($csvGou, $headerGou);

        foreach ($datosGou as $dg) {
            $dg['valor_reportar'] = ((int) $dg['valor_reportar']) . '00';
            fputcsv($csvGou, $dg);
        }

        rewind($csvGou);
        $zip->addFromString('archivo_Gou.csv', stream_get_contents($csvGou));
        fclose($csvGou);

        $zip->close();

        return $zipPath;
    }

    private function calcularValorReportar(
        int $diasMora,
        float $valorCuota,
        float $totalVencido,
        ?Carbon $fechaCuota
    ): array {
        $valorReportar = 0;
        $observacion = '';

        if ($diasMora <= 0) {
            $valorReportar = $valorCuota;
            $observacion = 'Valores 0';
        } elseif ($diasMora < 30) {
            $diaCuota = $fechaCuota?->day;
            $diaActual = now()->day;

            if ($diaCuota !== null && $diaCuota <= $diaActual) {
                $valorReportar = $valorCuota;
                $observacion = 'Vencido <30 con dias';
            } else {
                if ($totalVencido < $valorCuota) {
                    $valorReportar = $valorCuota + $totalVencido;
                } else {
                    $valorReportar = $totalVencido;
                }
                $observacion = 'Vencido <30';
            }
        } else {
            $valorReportar = $totalVencido;
            $observacion = 'Vencido > 29';
        }

        if ($valorReportar <= 0) {
            $valorReportar = max($totalVencido, $valorCuota);
        }

        return [round($valorReportar, 2), $observacion];
    }

    private function readCsvAssoc(string $path): \Generator
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(';');

        $headers = null;

        foreach ($file as $row) {
            if ($row === false || $row === [null]) {
                continue;
            }

            $row = array_map(fn($value) => $this->sanitizeCell($value), $row);

            if ($this->isEmptyRow($row)) {
                continue;
            }

            if ($headers === null) {
                $headers = [];
                foreach ($row as $index => $value) {
                    $header = $this->normalizeHeader($value);
                    if ($header === '') {
                        $header = 'COL_' . $index;
                    }
                    if (in_array($header, $headers, true)) {
                        $header .= '_' . $index;
                    }
                    $headers[] = $header;
                }
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            } elseif (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }

            $assoc = array_combine($headers, $row);
            if ($assoc === false) {
                continue;
            }

            yield $assoc;
        }

        if ($headers === null) {
            throw new RuntimeException('El CSV no contiene encabezados validos: ' . $path);
        }
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = $this->sanitizeCell($value);
        $header = preg_replace('/^\xEF\xBB\xBF/u', '', $header);
        $header = strtoupper($header);

        return trim($header);
    }

    private function sanitizeCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }

        return str_replace(["\r", "\n"], ' ', $text);
    }

    private function normalizeKey(mixed $value): string
    {
        $key = trim((string) $value);

        if ($key === '') {
            return '';
        }

        if (preg_match('/^\d+\.0+$/', $key) === 1) {
            $key = substr($key, 0, (int) strpos($key, '.'));
        }

        return str_replace(' ', '', $key);
    }

    private function toFloat(mixed $value): float
    {
        $number = trim((string) $value);
        if ($number === '') {
            return 0;
        }

        $number = str_replace(['$', ' '], '', $number);
        $number = preg_replace('/[^0-9,\.\-]/', '', $number);

        if ($number === '' || $number === '-' || $number === '.' || $number === ',') {
            return 0;
        }

        if (str_contains($number, ',') && str_contains($number, '.')) {
            if (strrpos($number, ',') > strrpos($number, '.')) {
                $number = str_replace('.', '', $number);
                $number = str_replace(',', '.', $number);
            } else {
                $number = str_replace(',', '', $number);
            }
        } elseif (str_contains($number, ',')) {
            $number = str_replace('.', '', $number);
            $number = str_replace(',', '.', $number);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $number) === 1) {
            $number = str_replace('.', '', $number);
        }

        return (float) $number;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTime::createFromInterface($value));
        }

        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Ymd'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);
                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (\Throwable) {
                // Continue trying formats.
            }
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isSameAsStored(PlanoSaldoValor $record, array $payload): bool
    {
        foreach ($payload as $field => $incoming) {
            $current = $record->{$field};

            if (in_array($field, ['valor_reportar', 'valor_cuota', 'saldo_capital'], true)) {
                if (round((float) $current, 2) !== round((float) $incoming, 2)) {
                    return false;
                }
                continue;
            }

            if ($field === 'dias_mora') {
                if ((int) $current !== (int) $incoming) {
                    return false;
                }
                continue;
            }

            if ($field === 'fecha_vigencia') {
                $currentDate = $this->parseDate($current)?->toDateString();
                $incomingDate = $this->parseDate($incoming)?->toDateString();

                if ($currentDate !== $incomingDate) {
                    return false;
                }
                continue;
            }

            if (trim((string) $current) !== trim((string) $incoming)) {
                return false;
            }
        }

        return true;
    }
}
