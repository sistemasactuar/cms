<?php

namespace App\Services;

use App\Models\TipoActivo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ActivoFijoSqlImportService
{
    public function import(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException('No se encontro el archivo de importacion.');
        }

        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            throw new RuntimeException('El archivo esta vacio o no se pudo leer.');
        }

        $stats = [
            'procesados' => 0,
            'creados' => 0,
            'actualizados' => 0,
            'ignorados' => 0,
            'saltados' => 0,
        ];

        $foundInsert = false;

        foreach ($this->extractInsertStatements($content) as $statement) {
            $foundInsert = true;

            $columns = $statement['columns'];
            $rows = $statement['rows'];

            foreach ($rows as $values) {
                $assoc = $this->combineColumnsValues($columns, $values);
                $payload = $this->buildPayload($assoc);

                $id = $payload['id'] ?? null;
                if ($id === null) {
                    $stats['saltados']++;
                    continue;
                }

                $current = DB::table('proc_activofijo')->where('id', $id)->first();

                if ($current === null) {
                    DB::table('proc_activofijo')->insert($payload);
                    $stats['creados']++;
                    $stats['procesados']++;
                    continue;
                }

                $changes = $this->extractChanges($current, $payload);
                if ($changes === []) {
                    $stats['ignorados']++;
                    continue;
                }

                DB::table('proc_activofijo')
                    ->where('id', $id)
                    ->update($changes);

                $stats['actualizados']++;
                $stats['procesados']++;
            }
        }

        if (!$foundInsert) {
            throw new RuntimeException('No se encontraron sentencias INSERT INTO proc_activofijo en el archivo.');
        }

        return $stats;
    }

    private function extractInsertStatements(string $content): array
    {
        $pattern = '/INSERT\s+INTO\s+`?proc_activofijo`?\s*\((.*?)\)\s*VALUES\s*(.*?);/is';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $result = [];

        foreach ($matches as $match) {
            $columnsRaw = (string) ($match[1] ?? '');
            $valuesRaw = (string) ($match[2] ?? '');

            $columns = array_map(
                static fn(string $column): string => trim($column, " \t\n\r\0\x0B`"),
                explode(',', $columnsRaw)
            );

            $tuples = $this->splitTuples($valuesRaw);
            $rows = [];

            foreach ($tuples as $tuple) {
                $rows[] = $this->parseTupleValues($tuple);
            }

            $result[] = [
                'columns' => $columns,
                'rows' => $rows,
            ];
        }

        return $result;
    }

    private function splitTuples(string $valuesRaw): array
    {
        $tuples = [];
        $length = strlen($valuesRaw);
        $level = 0;
        $inString = false;
        $escaped = false;
        $start = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesRaw[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;
                continue;
            }

            if ($char === '(') {
                if ($level === 0) {
                    $start = $i;
                }
                $level++;
                continue;
            }

            if ($char === ')') {
                if ($level === 0) {
                    continue;
                }

                $level--;
                if ($level === 0 && $start !== null) {
                    $tuples[] = substr($valuesRaw, $start, ($i - $start) + 1);
                    $start = null;
                }
            }
        }

        return $tuples;
    }

    private function parseTupleValues(string $tuple): array
    {
        $inner = trim($tuple);
        $inner = preg_replace('/^\(/', '', $inner) ?? $inner;
        $inner = preg_replace('/\)$/', '', $inner) ?? $inner;

        $values = str_getcsv($inner, ',', "'", '\\');

        return array_map(function (mixed $value): mixed {
            if ($value === null) {
                return null;
            }

            $text = trim((string) $value);
            if (strtoupper($text) === 'NULL') {
                return null;
            }

            return $this->sanitizeText($text);
        }, $values ?: []);
    }

    private function combineColumnsValues(array $columns, array $values): array
    {
        $totalColumns = count($columns);
        $totalValues = count($values);

        if ($totalValues < $totalColumns) {
            $values = array_pad($values, $totalColumns, null);
        } elseif ($totalValues > $totalColumns) {
            $values = array_slice($values, 0, $totalColumns);
        }

        $assoc = array_combine($columns, $values);

        return $assoc === false ? [] : $assoc;
    }

    private function buildPayload(array $row): array
    {
        $id = $this->toInt($row['id'] ?? null);
        $fecadi = $this->toDate($row['fecadi'] ?? null);
        $fecmod = $this->toDate($row['fecmod'] ?? null);
        $horadi = $this->toTime($row['horadi'] ?? null);
        $hormod = $this->toTime($row['hormod'] ?? null);
        $createdAt = $this->combineDateTime($fecadi, $horadi) ?? now()->toDateTimeString();
        $updatedAt = $this->combineDateTime($fecmod, $hormod) ?? $createdAt;

        return [
            'id' => $id,
            'descripcion' => $this->sanitizeText($row['descripcion'] ?? null) ?: 'SIN DESCRIPCION',
            'tipo' => $this->resolveTipo($row['tipo'] ?? null),
            'marca' => $this->nullIfEmpty($row['marca'] ?? null),
            'modelo' => $this->nullIfEmpty($row['modelo'] ?? null),
            'serie' => $this->nullIfEmpty($row['serie'] ?? null),
            'codigo' => $this->nullIfEmpty($row['codigo'] ?? null),
            'para_sede_id' => $this->toInt($row['para_sede_id'] ?? null),
            'responsable' => $this->nullIfEmpty($row['responsable'] ?? null),
            'valor' => $this->toDecimal($row['valor'] ?? null),
            'condicion' => $this->nullIfEmpty($row['condicion'] ?? null),
            'observacion' => $this->nullIfEmpty($row['observacion'] ?? null),
            'unidad_cd' => $this->toTinyInt($row['unidad_cd'] ?? null),
            'hdd1' => $this->nullIfEmpty($row['hdd1'] ?? null),
            'tipo_disco' => $this->toTinyInt($row['tipo_disco'] ?? null),
            'hdd2' => $this->nullIfEmpty($row['hdd2'] ?? null),
            'tipo_disco2' => $this->toTinyInt($row['tipo_disco2'] ?? null),
            'fuente' => $this->nullIfEmpty($row['fuente'] ?? null),
            'cargador' => $this->nullIfEmpty($row['cargador'] ?? null),
            'procesador' => $this->nullIfEmpty($row['procesador'] ?? null),
            'ram' => $this->nullIfEmpty($row['ram'] ?? null),
            'pantalla' => $this->nullIfEmpty($row['pantalla'] ?? null),
            'pantalla_tam' => $this->nullIfEmpty($row['pantalla_tam'] ?? null),
            't_video' => $this->nullIfEmpty($row['t_video'] ?? null),
            'teclado' => $this->nullIfEmpty($row['teclado'] ?? null),
            'mouse' => $this->nullIfEmpty($row['mouse'] ?? null),
            'so' => $this->nullIfEmpty($row['so'] ?? null),
            'sof' => $this->nullIfEmpty($row['sof'] ?? null),
            'compresor' => $this->nullIfEmpty($row['compresor'] ?? null),
            'adobe' => $this->nullIfEmpty($row['adobe'] ?? null),
            'antivirus' => $this->nullIfEmpty($row['antivirus'] ?? null),
            'explorador1' => $this->nullIfEmpty($row['explorador1'] ?? null),
            'explorador2' => $this->nullIfEmpty($row['explorador2'] ?? null),
            'explorador3' => $this->nullIfEmpty($row['explorador3'] ?? null),
            'prog_adicionales' => $this->nullIfEmpty($row['prog_adicionales'] ?? null),
            'ups_capacidad' => $this->nullIfEmpty($row['ups_capacidad'] ?? null),
            'telecom_puertos' => $this->nullIfEmpty($row['telecom_puertos'] ?? null),
            'telecom_pe' => $this->nullIfEmpty($row['telecom_pe'] ?? null),
            'vigil_tipo' => $this->toTinyInt($row['vigil_tipo'] ?? null),
            'vigil_puertos' => $this->nullIfEmpty($row['vigil_puertos'] ?? null),
            'vigil_capacidad' => $this->nullIfEmpty($row['vigil_capacidad'] ?? null),
            'vigil_poe' => $this->toTinyInt($row['vigil_poe'] ?? null),
            'acces_point_rango' => $this->nullIfEmpty($row['acces_point_rango'] ?? null),
            'por' => $this->toInt($row['por'] ?? null),
            'visto' => $this->toDate($row['visto'] ?? null),
            'activo' => $this->toBool($row['activo'] ?? null),
            'usuadi' => $this->toInt($row['usuadi'] ?? null),
            'fecadi' => $fecadi,
            'horadi' => $horadi,
            'usumod' => $this->toInt($row['usumod'] ?? null),
            'fecmod' => $fecmod,
            'hormod' => $hormod,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    private function resolveTipo(mixed $value): ?int
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            return (int) $raw;
        }

        $normalized = strtoupper($this->sanitizeText($raw));
        $id = TipoActivo::query()
            ->whereRaw('UPPER(tipo) = ?', [$normalized])
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $tipo = TipoActivo::query()->create([
            'tipo' => mb_substr($normalized, 0, 120),
            'activo' => true,
        ]);

        return (int) $tipo->id;
    }

    private function extractChanges(object $current, array $payload): array
    {
        $changes = [];

        foreach ($payload as $field => $incoming) {
            if ($field === 'id') {
                continue;
            }

            $stored = $current->{$field} ?? null;
            if ($this->sameValue($field, $stored, $incoming)) {
                continue;
            }

            $changes[$field] = $incoming;
        }

        return $changes;
    }

    private function sameValue(string $field, mixed $stored, mixed $incoming): bool
    {
        if (in_array($field, ['valor'], true)) {
            return round((float) $stored, 2) === round((float) $incoming, 2);
        }

        if (in_array($field, [
            'tipo',
            'para_sede_id',
            'unidad_cd',
            'tipo_disco',
            'tipo_disco2',
            'vigil_tipo',
            'vigil_poe',
            'por',
            'activo',
            'usuadi',
            'usumod',
        ], true)) {
            return (int) ($stored ?? 0) === (int) ($incoming ?? 0);
        }

        if (in_array($field, ['visto', 'fecadi', 'fecmod'], true)) {
            return $this->toDate($stored) === $this->toDate($incoming);
        }

        if (in_array($field, ['horadi', 'hormod'], true)) {
            return $this->toTime($stored) === $this->toTime($incoming);
        }

        if (in_array($field, ['created_at', 'updated_at'], true)) {
            return $this->toDateTime($stored) === $this->toDateTime($incoming);
        }

        return trim((string) ($stored ?? '')) === trim((string) ($incoming ?? ''));
    }

    private function toInt(mixed $value): ?int
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (!is_numeric($text)) {
            return null;
        }

        return (int) $text;
    }

    private function toTinyInt(mixed $value): ?int
    {
        $int = $this->toInt($value);
        if ($int === null) {
            return null;
        }

        if ($int < 0) {
            $int = 0;
        }

        if ($int > 255) {
            $int = 255;
        }

        return $int;
    }

    private function toBool(mixed $value): bool
    {
        $text = strtolower(trim((string) $value));
        if ($text === '' || $text === 'null') {
            return true;
        }

        if (in_array($text, ['0', 'false', 'no', 'n'], true)) {
            return false;
        }

        return true;
    }

    private function toDecimal(mixed $value): ?float
    {
        $text = trim((string) $value);
        if ($text === '' || strtolower($text) === 'null') {
            return null;
        }

        $text = str_replace(['$', ' '], '', $text);
        $text = preg_replace('/[^0-9,\.\-]/', '', $text) ?? '';
        if ($text === '' || $text === '-' || $text === '.' || $text === ',') {
            return null;
        }

        if (str_contains($text, ',') && str_contains($text, '.')) {
            if (strrpos($text, ',') > strrpos($text, '.')) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif (str_contains($text, ',')) {
            $text = str_replace('.', '', $text);
            $text = str_replace(',', '.', $text);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $text) === 1) {
            $text = str_replace('.', '', $text);
        }

        return is_numeric($text) ? (float) $text : null;
    }

    private function toDate(mixed $value): ?string
    {
        $text = trim((string) $value);
        if ($text === '' || $text === '0000-00-00' || strtolower($text) === 'null') {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $text);
                if ($date !== false) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                // Continue trying formats.
            }
        }

        try {
            return Carbon::parse($text)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function toTime(mixed $value): ?string
    {
        $text = trim((string) $value);
        if ($text === '' || strtolower($text) === 'null') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $text) === 1) {
            if (strlen($text) === 5) {
                return $text . ':00';
            }
            return $text;
        }

        return null;
    }

    private function toDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function combineDateTime(?string $date, ?string $time): ?string
    {
        if ($date === null) {
            return null;
        }

        return trim($date . ' ' . ($time ?? '00:00:00'));
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $text = $this->sanitizeText($value);

        if ($text === '') {
            return null;
        }

        return $text;
    }

    private function sanitizeText(mixed $value): string
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
}

