<?php

namespace App\Services;

use App\Models\PlanoSaldoValor;
use App\Models\PlanoSaldoValorSaldoDiario;
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
            'mensual',
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
                'post_cierre',
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
                'saldos_diario',
            );

            $this->recordSaldoDiario(
                $resultadoFila['record'] ?? $this->findCurrentRecordForRow(null, $saldosRow),
                null,
                $saldosRow,
                $fechaVigencia,
                'saldos_diario',
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
        string $sourceType,
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
                $sourceType,
            );

            if ($saldosRow !== null) {
                $this->recordSaldoDiario(
                    $resultadoFila['record'] ?? $this->findCurrentRecordForRow($referenceRow, $saldosRow),
                    $referenceRow,
                    $saldosRow,
                    $fechaVigencia,
                    $sourceType,
                );
            }

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
        Carbon $fechaVigencia,
        string $sourceType
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
        $record = PlanoSaldoValor::firstOrNew(['cc' => $cc, 'obligacion' => $obligacion]);
        $alreadyExists = $record->exists;
        $origenRegistro = $this->resolveOriginRegistro($record->origen_registro, $sourceType);
        $fechaEntradaPlano = $record->fecha_entrada_plano
            ? $this->parseDate($record->fecha_entrada_plano)?->toDateString()
            : $fechaVigencia->toDateString();

        $payload = [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'valor_reportar' => $valorReportar,
            'valor_cuota' => $valorCuota,
            'valor_vencido' => $valorVencido,
            'origen_registro' => $origenRegistro,
            'fecha_entrada_plano' => $fechaEntradaPlano,
            'estado_registro' => 'activo',
            'modalidad' => $modalidad,
            'periodo' => $periodo,
            'observacion' => $observacion,
            'saldo_capital' => $saldoCapital,
            'dias_mora' => $diasMora,
            'fecha_vigencia' => $fechaVigencia->toDateString(),
        ];

        if ($alreadyExists && $this->isSameAsStored($record, $payload)) {
            return [
                'status' => 'ignored',
                'row_key' => $cc . '|' . $obligacion,
                'record' => $record,
            ];
        }

        $record->fill($payload);
        $record->save();

        $valorReportarEntero = (int) round($valorReportar);
        $rowKey = $cc . '|' . $obligacion;
        $exportNames = $this->prepareExportNames($nombres, $apellidos, $carteraRow, $saldosRow);

        return [
            'status' => $alreadyExists ? 'updated' : 'created',
            'row_key' => $rowKey,
            'record' => $record,
            'datos_re' => [
                'ID_ENTIDAD' => 9,
                'ID_SUCURSAL' => 1,
                'A_OBLIGA' => $obligacion,
                'NOMBRE_CLIENTE' => $exportNames['re_nombre'],
                'APELLIDO_CLIENTE' => $exportNames['re_apellido'],
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
                'nombres' => $exportNames['gou_nombre'],
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

    private function recordSaldoDiario(
        ?PlanoSaldoValor $record,
        ?array $referenceRow,
        array $saldosRow,
        Carbon $fechaArchivo,
        string $sourceType
    ): void {
        $obligacion = $this->extractObligacion($referenceRow, $saldosRow);
        $cc = $this->extractDocumento($referenceRow, $saldosRow);

        if ($obligacion === '' || $cc === '') {
            return;
        }

        $valorVencido = $this->extractValorVencido($saldosRow);
        $saldoCapital = $this->extractSaldoCapital($saldosRow, $referenceRow);
        $diasMora = $this->extractDiasMora($saldosRow, $referenceRow);
        $valorCuota = $record?->valor_cuota !== null
            ? (float) $record->valor_cuota
            : $this->extractValorCuota($referenceRow);
        $valorReportar = $saldoCapital > 0
            ? $this->calcularValorReportar($valorCuota, $valorVencido)[0]
            : 0.0;
        $origenRegistro = $this->resolveOriginRegistro($record?->origen_registro, $sourceType);

        $previousSnapshot = PlanoSaldoValorSaldoDiario::query()
            ->where('cc', $cc)
            ->where('obligacion', $obligacion)
            ->whereDate('fecha_archivo', '<', $fechaArchivo->toDateString())
            ->orderByDesc('fecha_archivo')
            ->orderByDesc('id')
            ->first();

        $variacionValorVencido = $previousSnapshot !== null
            ? round($valorVencido - (float) $previousSnapshot->valor_vencido, 2)
            : null;
        $variacionSaldoCapital = $previousSnapshot !== null
            ? round($saldoCapital - (float) $previousSnapshot->saldo_capital, 2)
            : null;

        $snapshot = PlanoSaldoValorSaldoDiario::firstOrNew([
            'fecha_archivo' => $fechaArchivo->toDateString(),
            'cc' => $cc,
            'obligacion' => $obligacion,
        ]);

        $snapshot->fill([
            'plano_saldo_valor_id' => $record?->id,
            'valor_vencido' => $valorVencido,
            'saldo_capital' => $saldoCapital,
            'dias_mora' => $diasMora,
            'valor_cuota' => $valorCuota,
            'valor_reportar' => $valorReportar,
            'origen_registro' => $origenRegistro,
            'estado_movimiento' => $this->resolveEstadoMovimiento($variacionValorVencido, $variacionSaldoCapital),
            'variacion_valor_vencido' => $variacionValorVencido,
            'variacion_saldo_capital' => $variacionSaldoCapital,
        ]);
        $snapshot->save();

        if ($record === null) {
            return;
        }

        $record->fill([
            'origen_registro' => $origenRegistro,
            'ultima_fecha_saldo_diario' => $fechaArchivo->toDateString(),
            'ultimo_estado_saldo_diario' => $snapshot->estado_movimiento,
            'valor_vencido' => $valorVencido,
            'saldo_capital' => $saldoCapital,
            'dias_mora' => $diasMora,
            'estado_registro' => $saldoCapital > 0 ? 'activo' : 'saldo_cero',
            'observacion' => $saldoCapital > 0 ? $record->observacion : 'Saldo capital 0',
            'valor_reportar' => $saldoCapital > 0 ? $record->valor_reportar : 0,
            'fecha_vigencia' => $fechaArchivo->toDateString(),
        ]);
        $record->save();

        if ($snapshot->plano_saldo_valor_id !== $record->id) {
            $snapshot->plano_saldo_valor_id = $record->id;
            $snapshot->save();
        }
    }

    private function findCurrentRecordForRow(?array $referenceRow, ?array $saldosRow): ?PlanoSaldoValor
    {
        $obligacion = $this->extractObligacion($referenceRow, $saldosRow);
        $cc = $this->extractDocumento($referenceRow, $saldosRow);

        if ($obligacion === '' || $cc === '') {
            return null;
        }

        return PlanoSaldoValor::query()
            ->where('cc', $cc)
            ->where('obligacion', $obligacion)
            ->first();
    }

    private function resolveOriginRegistro(?string $currentOrigin, string $incomingOrigin): string
    {
        $priority = [
            'saldos_diario' => 1,
            'post_cierre' => 2,
            'mensual' => 3,
        ];

        $currentOrigin = is_string($currentOrigin) ? trim($currentOrigin) : '';

        if ($currentOrigin === '') {
            return $incomingOrigin;
        }

        return ($priority[$incomingOrigin] ?? 0) >= ($priority[$currentOrigin] ?? 0)
            ? $incomingOrigin
            : $currentOrigin;
    }

    private function resolveEstadoMovimiento(?float $variacionValorVencido, ?float $variacionSaldoCapital): string
    {
        if ($variacionValorVencido === null && $variacionSaldoCapital === null) {
            return 'nuevo';
        }

        $hasDecrease = ($variacionValorVencido ?? 0) < 0 || ($variacionSaldoCapital ?? 0) < 0;
        $hasIncrease = ($variacionValorVencido ?? 0) > 0 || ($variacionSaldoCapital ?? 0) > 0;

        if ($hasDecrease && $hasIncrease) {
            return 'mixto';
        }

        if ($hasDecrease) {
            return 'disminuyo';
        }

        if ($hasIncrease) {
            return 'aumento';
        }

        return 'sin_cambio';
    }

    private function calcularValorReportar(float $valorCuota, float $valorVencido): array
    {
        if ($valorVencido > $valorCuota) {
            return [round($valorVencido, 2), 'Valor vencido'];
        }

        if ($valorVencido < $valorCuota) {
            return [round($valorCuota + $valorVencido, 2), 'Cuota + vencido'];
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

            if (in_array($field, ['valor_reportar', 'valor_cuota', 'valor_vencido', 'saldo_capital'], true)) {
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

            if (in_array($field, ['fecha_vigencia', 'fecha_entrada_plano'], true)) {
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

    private function prepareExportNames(string $nombres, string $apellidos, ?array ...$rows): array
    {
        $nombres = $this->normalizeExportText($nombres);
        $apellidos = $this->normalizeExportText($apellidos);
        $nombreBase = $this->normalizeExportText((string) $this->pickValue($rows, [
            'RAZON_SOCIAL',
            'RAZON SOCIAL',
            'NOMBRE',
            'NOMBRE_COMPLETO',
        ]));

        if ($nombreBase === '') {
            $nombreBase = $this->normalizeExportText(trim($nombres . ' ' . $apellidos));
        }

        if ($this->looksLikeCompanyName($nombreBase, $nombres, $apellidos, ...$rows)) {
            $companyName = $this->truncateExportText($nombreBase !== '' ? $nombreBase : trim($nombres . ' ' . $apellidos), 45);

            return [
                're_nombre' => $companyName,
                're_apellido' => '',
                'gou_nombre' => $companyName,
            ];
        }

        return [
            're_nombre' => $nombres,
            're_apellido' => $apellidos,
            'gou_nombre' => $this->normalizeExportText(trim($nombres . ' ' . $apellidos)),
        ];
    }

    private function looksLikeCompanyName(string $nombreBase, string $nombres, string $apellidos, ?array ...$rows): bool
    {
        if ($this->pickValue($rows, ['RAZON_SOCIAL', 'RAZON SOCIAL']) !== null) {
            return true;
        }

        if ($nombres === 'EMPRESA' || $apellidos === 'EMPRESA') {
            return true;
        }

        $candidate = ' ' . mb_strtoupper(trim($nombreBase !== '' ? $nombreBase : $nombres . ' ' . $apellidos), 'UTF-8') . ' ';

        foreach ([
            ' SAS ',
            ' S.A.S ',
            ' S A S ',
            ' LTDA ',
            ' LIMITADA ',
            ' S.A ',
            ' SA ',
            ' E.U ',
            ' COOPERATIVA ',
            ' ASOCIACION ',
            ' ASOCIACIÓN ',
            ' FUNDACION ',
            ' FUNDACIÓN ',
            ' CORPORACION ',
            ' CORPORACIÓN ',
            ' CONSORCIO ',
            ' UNION TEMPORAL ',
            ' UNIÓN TEMPORAL ',
            ' COMERCIAL ',
            ' SERVICIOS ',
            ' EMPRESA ',
            ' INDUSTRIAS ',
        ] as $keyword) {
            if (str_contains($candidate, $keyword)) {
                return true;
            }
        }

        $hasStructuredPersonName = $this->pickValue($rows, [
            'APELLIDOS',
            'APELLIDO_CLIENTE',
            'AP - APELLIDO 1',
            'AP - APELLIDO 2',
            'AP - NOMBRE 1',
            'AP - NOMBRE 2',
        ]) !== null;

        return !$hasStructuredPersonName && mb_strlen(trim($nombreBase), 'UTF-8') > 45;
    }

    private function normalizeExportText(string $text): string
    {
        return preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
    }

    private function truncateExportText(string $text, int $maxLength): string
    {
        $normalized = $this->normalizeExportText($text);

        if ($maxLength <= 0 || mb_strlen($normalized, 'UTF-8') <= $maxLength) {
            return $normalized;
        }

        return rtrim(mb_substr($normalized, 0, $maxLength, 'UTF-8'));
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
