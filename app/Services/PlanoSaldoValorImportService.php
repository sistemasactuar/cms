<?php

namespace App\Services;

use App\Models\PlanoSaldoValor;
use Illuminate\Support\Carbon;
use RuntimeException;
use SplFileObject;
use ZipArchive;

class PlanoSaldoValorImportService
{
    public function import(
        string $carteraPath,
        string $saldosPath,
        mixed $fechaArchivo,
        ?string $postCierrePath = null
    ): array
    {
        if (!is_file($carteraPath)) {
            throw new RuntimeException('No se encontro el archivo de cartera.');
        }

        if (!is_file($saldosPath)) {
            throw new RuntimeException('No se encontro el archivo de saldos Aicoll.');
        }

        if (is_string($postCierrePath) && trim($postCierrePath) !== '' && !is_file($postCierrePath)) {
            throw new RuntimeException('No se encontro el archivo de creditos posteriores al cierre.');
        }

        $carteraRows = $this->readCsvAssoc($carteraPath);
        $postCierreRows = is_string($postCierrePath) && trim($postCierrePath) !== ''
            ? $this->readCsvAssoc($postCierrePath, '|')
            : null;
        [$saldosByCredito, $saldosByDocumento, $saldosRows, $registrosSaldos] = $this->indexRows($saldosPath);

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
        $sinCoincidenciaSaldos = 0;
        $matchedSaldosRowIds = [];
        $processedReferenceKeys = [];

        $this->importReferenceRows(
            $carteraRows,
            $processedReferenceKeys,
            $matchedSaldosRowIds,
            $datosRe,
            $datosGou,
            $creados,
            $actualizados,
            $ignoradosIguales,
            $sinCoincidenciaSaldos,
            $saldosByCredito,
            $saldosByDocumento,
            $periodo,
            $fechaConvArchivo,
            $periodoFin,
            $fechaVigencia,
        );

        if ($postCierreRows !== null) {
            $this->importReferenceRows(
                $postCierreRows,
                $processedReferenceKeys,
                $matchedSaldosRowIds,
                $datosRe,
                $datosGou,
                $creados,
                $actualizados,
                $ignoradosIguales,
                $sinCoincidenciaSaldos,
                $saldosByCredito,
                $saldosByDocumento,
                $periodo,
                $fechaConvArchivo,
                $periodoFin,
                $fechaVigencia,
            );
        }

        foreach ($saldosRows as $saldosRow) {
            if (isset($matchedSaldosRowIds[$saldosRow['__row_id']])) {
                continue;
            }

            $resultadoFila = $this->processRow(
                null,
                $saldosRow,
                $periodo,
                $fechaConvArchivo,
                $periodoFin,
                $fechaVigencia,
            );

            if ($resultadoFila === null) {
                continue;
            }

            if ($resultadoFila['status'] === 'ignored') {
                $ignoradosIguales++;
                continue;
            }

            if ($resultadoFila['status'] === 'created') {
                $creados++;
            } else {
                $actualizados++;
            }

            $rowKey = $resultadoFila['row_key'];
            $datosRe[$rowKey] = $resultadoFila['datos_re'];
            $datosGou[$rowKey] = $resultadoFila['datos_gou'];
        }

        $procesados = $creados + $actualizados;
        $zipPath = null;

        if (count($datosRe) > 0) {
            $zipPath = $this->createZip(array_values($datosRe), array_values($datosGou), $fechaConvArchivo);
        }

        return [
            'procesados' => $procesados,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'ignorados_iguales' => $ignoradosIguales,
            'registros_saldos' => $registrosSaldos,
            'sin_coincidencia_saldos' => $sinCoincidenciaSaldos,
            'zip_path' => $zipPath,
        ];
    }

    private function importReferenceRows(
        iterable $referenceRows,
        array &$processedReferenceKeys,
        array &$matchedSaldosRowIds,
        array &$datosRe,
        array &$datosGou,
        int &$creados,
        int &$actualizados,
        int &$ignoradosIguales,
        int &$sinCoincidenciaSaldos,
        array $saldosByCredito,
        array $saldosByDocumento,
        string $periodo,
        string $fechaConvArchivo,
        string $periodoFin,
        Carbon $fechaVigencia
    ): void {
        foreach ($referenceRows as $referenceRow) {
            $referenceKey = $this->buildReferenceKey($referenceRow);

            if ($referenceKey !== '' && isset($processedReferenceKeys[$referenceKey])) {
                continue;
            }

            if ($referenceKey !== '') {
                $processedReferenceKeys[$referenceKey] = true;
            }

            $saldosRow = $this->findMatchingRow(
                $referenceRow,
                $saldosByCredito,
                $saldosByDocumento,
            );

            if ($saldosRow === null) {
                $sinCoincidenciaSaldos++;
            } else {
                $matchedSaldosRowIds[$saldosRow['__row_id']] = true;
            }

            $resultadoFila = $this->processRow(
                $referenceRow,
                $saldosRow,
                $periodo,
                $fechaConvArchivo,
                $periodoFin,
                $fechaVigencia,
            );

            if ($resultadoFila === null) {
                continue;
            }

            if ($resultadoFila['status'] === 'ignored') {
                $ignoradosIguales++;
                continue;
            }

            if ($resultadoFila['status'] === 'created') {
                $creados++;
            } else {
                $actualizados++;
            }

            $rowKey = $resultadoFila['row_key'];
            $datosRe[$rowKey] = $resultadoFila['datos_re'];
            $datosGou[$rowKey] = $resultadoFila['datos_gou'];
        }
    }

    private function processRow(
        ?array $carteraRow,
        ?array $saldosRow,
        string $periodo,
        string $fechaConvArchivo,
        string $periodoFin,
        Carbon $fechaVigencia
    ): ?array {
        $obligacion = $this->extractObligacion($carteraRow, $saldosRow);
        $cc = $this->extractDocumento($carteraRow, $saldosRow);

        if ($obligacion === '' || $cc === '') {
            return null;
        }

        $valorCuota = $this->extractValorCuota($carteraRow);
        $valorVencido = $this->extractValorVencido($saldosRow);
        $saldoCapital = $this->extractSaldoCapital($saldosRow, $carteraRow);

        if ($saldoCapital <= 0) {
            return null;
        }

        [$valorReportar, $observacion] = $this->calcularValorReportar(
            $valorCuota,
            $valorVencido,
        );

        if ($valorReportar <= 0) {
            return null;
        }

        [$nombres, $apellidos] = $this->extractNombrePartes($carteraRow, $saldosRow);
        $modalidad = $this->extractModalidad($carteraRow, $saldosRow);
        $diasMora = $this->extractDiasMora($saldosRow, $carteraRow);

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
            return [
                'status' => 'ignored',
                'row_key' => $cc . '|' . $obligacion,
            ];
        }

        $record->fill($payload);
        $record->save();

        $valorReportarEntero = (int) round($valorReportar);
        $rowKey = $cc . '|' . $obligacion;
        $nombreCompleto = trim($nombres . ' ' . $apellidos);

        return [
            'status' => $alreadyExists ? 'updated' : 'created',
            'row_key' => $rowKey,
            'datos_re' => [
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
            ],
            'datos_gou' => [
                'obligacion' => $obligacion,
                'cc' => $cc,
                'cc1' => $cc,
                'nombres' => $nombreCompleto,
                'valor_reportar' => $valorReportarEntero,
                'periodo' => $fechaConvArchivo,
                'valor_recargo' => '00000',
                'periodofin' => $periodoFin,
                'tipo_pago' => 0,
            ],
        ];
    }

    private function indexRows(string $path): array
    {
        $byCredito = [];
        $byDocumento = [];
        $rows = [];
        $count = 0;

        foreach ($this->readCsvAssoc($path) as $row) {
            $row['__row_id'] = $count;
            $count++;
            $rows[] = $row;

            $credito = $this->extractObligacion($row);
            $documento = $this->extractDocumento($row);

            if ($credito !== '') {
                $byCredito[$credito] = $row;
            }

            if ($documento !== '') {
                $byDocumento[$documento] = $row;
            }
        }

        return [$byCredito, $byDocumento, $rows, $count];
    }

    private function findMatchingRow(
        array $referenceRow,
        array $rowsByCredito,
        array $rowsByDocumento
    ): ?array {
        $credito = $this->extractObligacion($referenceRow);
        $documento = $this->extractDocumento($referenceRow);

        if ($credito !== '' && isset($rowsByCredito[$credito])) {
            return $rowsByCredito[$credito];
        }

        if ($documento !== '' && isset($rowsByDocumento[$documento])) {
            return $rowsByDocumento[$documento];
        }

        return null;
    }

    private function createZip(array $datosRe, array $datosGou, string $fechaConvArchivo): string
    {
        $zip = new ZipArchive();
        $directory = storage_path('app/public');

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('No fue posible preparar el directorio de salida para el ZIP.');
        }

        $zipFileName = 'planos_procesados_' . now()->format('Ymd_His_u') . '_' . bin2hex(random_bytes(4)) . '.zip';
        $zipPath = $directory . DIRECTORY_SEPARATOR . $zipFileName;

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

    private function calcularValorReportar(float $valorCuota, float $valorVencido): array
    {
        if ($valorVencido > $valorCuota) {
            return [round($valorVencido, 2), 'Valor vencido'];
        }

        return [round($valorCuota, 2), 'Valor cuota'];
    }

    private function readCsvAssoc(string $path, string $delimiter = ';'): \Generator
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter);

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
        $header = mb_strtoupper($header, 'UTF-8');

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

    private function extractObligacion(?array ...$rows): string
    {
        return $this->normalizeKey($this->pickValue($rows, [
            'NUMERO_CREDITO',
            'NO_OBLIGACION',
            'A_OBLIGA',
            'CA - NUMERO DE OBLIGACION',
            'CA - NÚMERO DE OBLIGACIÓN',
            'OBLIGACION',
        ]));
    }

    private function extractDocumento(?array ...$rows): string
    {
        return $this->normalizeKey($this->pickValue($rows, [
            'NUMERO_DOCUMENTO',
            'ID_CLIENTE',
            'AP - IDENTIFICACION',
            'AP - IDENTIFICACIÓN',
            'CC',
            'C.C',
            'DOCUMENTO',
        ]));
    }

    private function extractValorCuota(?array $carteraRow): float
    {
        return $this->toFloat($this->pickValue([$carteraRow], [
            'VALOR_CUOTA',
            'VLR_CUOTA',
            'CA - VALOR CUOTA',
            'V_CUOTA',
        ]));
    }

    private function extractValorVencido(?array $saldosRow): float
    {
        return $this->toFloat($this->pickValue([$saldosRow], [
            'TOTAL_VENCIDO',
            'VALOR_VENCIDO',
            'VENC_CAPITAL',
            'VENCIDO_CAPITAL',
        ]));
    }

    private function extractSaldoCapital(?array ...$rows): float
    {
        return $this->toFloat($this->pickValue($rows, [
            'SALDO_CAPITAL',
            'SLD_CAPITAL',
        ]));
    }

    private function extractDiasMora(?array ...$rows): int
    {
        return (int) round($this->toFloat($this->pickValue($rows, [
            'DIAS_MORA',
            'DIAS_VENCIDOS',
            'DIAS_VENCIDOS_CAPITAL',
        ])));
    }

    private function extractModalidad(?array ...$rows): string
    {
        return trim((string) $this->pickValue($rows, [
            'MODALIDAD',
            'DETALLE_MODALIDAD',
        ]));
    }

    private function extractNombrePartes(?array ...$rows): array
    {
        $nombres = trim((string) $this->pickValue($rows, [
            'NOMBRES',
            'NOMBRE_CLIENTE',
        ]));
        $apellidos = trim((string) $this->pickValue($rows, [
            'APELLIDOS',
            'APELLIDO_CLIENTE',
        ]));

        if ($nombres === '') {
            $nombres = trim(implode(' ', array_filter([
                trim((string) $this->pickValue($rows, ['AP - NOMBRE 1'])),
                trim((string) $this->pickValue($rows, ['AP - NOMBRE 2'])),
            ])));
        }

        if ($apellidos === '') {
            $apellidos = trim(implode(' ', array_filter([
                trim((string) $this->pickValue($rows, ['AP - APELLIDO 1'])),
                trim((string) $this->pickValue($rows, ['AP - APELLIDO 2'])),
            ])));
        }

        if ($nombres === '' && $apellidos === '') {
            $nombreCompleto = trim((string) $this->pickValue($rows, [
                'NOMBRE',
                'NOMBRE_COMPLETO',
            ]));

            if ($nombreCompleto !== '') {
                $partes = preg_split('/\s+/', $nombreCompleto) ?: [];

                if (count($partes) >= 2) {
                    $apellidos = implode(' ', array_slice($partes, -2));
                    $nombres = implode(' ', array_slice($partes, 0, -2));
                } else {
                    $nombres = $nombreCompleto;
                }
            }
        }

        if ($nombres === '') {
            $nombres = 'EMPRESA';
        }

        if ($apellidos === '') {
            $apellidos = 'EMPRESA';
        }

        return [$nombres, $apellidos];
    }

    private function buildReferenceKey(?array ...$rows): string
    {
        $obligacion = $this->extractObligacion(...$rows);

        if ($obligacion !== '') {
            return 'obl:' . $obligacion;
        }

        $documento = $this->extractDocumento(...$rows);

        if ($documento !== '') {
            return 'doc:' . $documento;
        }

        return '';
    }

    private function pickValue(array $rows, array $keys): mixed
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($keys as $key) {
                if (!array_key_exists($key, $row)) {
                    continue;
                }

                $value = $row[$key];

                if ($value === null) {
                    continue;
                }

                if (is_string($value) && trim($value) === '') {
                    continue;
                }

                return $value;
            }
        }

        return null;
    }
}
