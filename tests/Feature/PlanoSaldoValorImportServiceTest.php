<?php

namespace Tests\Feature;

use App\Models\PlanoSaldoValor;
use App\Models\PlanoSaldoValorSaldoDiario;
use App\Services\PlanoSaldoValorImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use ZipArchive;

class PlanoSaldoValorImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('plano_saldos_valores', function (Blueprint $table): void {
            $table->id();
            $table->string('obligacion');
            $table->string('cc');
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('valor_reportar', 18, 2)->nullable();
            $table->decimal('valor_cuota', 18, 2)->nullable();
            $table->decimal('valor_vencido', 18, 2)->nullable();
            $table->string('origen_registro')->nullable();
            $table->date('fecha_entrada_plano')->nullable();
            $table->string('estado_registro')->nullable();
            $table->string('modalidad')->nullable();
            $table->string('periodo')->nullable();
            $table->string('observacion')->nullable();
            $table->decimal('saldo_capital', 18, 2)->nullable();
            $table->integer('dias_mora')->nullable();
            $table->date('fecha_vigencia')->nullable();
            $table->date('ultima_fecha_saldo_diario')->nullable();
            $table->string('ultimo_estado_saldo_diario')->nullable();
            $table->timestamps();
            $table->unique(['cc', 'obligacion'], 'uk_cc_obligacion');
        });

        Schema::create('plano_saldo_valor_saldos_diarios', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('plano_saldo_valor_id')->nullable();
            $table->string('obligacion');
            $table->string('cc');
            $table->date('fecha_archivo');
            $table->decimal('valor_vencido', 18, 2)->nullable();
            $table->decimal('saldo_capital', 18, 2)->nullable();
            $table->integer('dias_mora')->nullable();
            $table->decimal('valor_cuota', 18, 2)->nullable();
            $table->decimal('valor_reportar', 18, 2)->nullable();
            $table->string('origen_registro')->nullable();
            $table->string('estado_movimiento')->nullable();
            $table->decimal('variacion_valor_vencido', 18, 2)->nullable();
            $table->decimal('variacion_saldo_capital', 18, 2)->nullable();
            $table->timestamps();
            $table->unique(['fecha_archivo', 'cc', 'obligacion'], 'uk_psv_saldos_diarios_fecha_cc_obl');
        });
    }

    public function test_it_imports_using_cartera_quota_and_saldos_overdue_rules(): void
    {
        $carteraPath = $this->createCsv([
            ['NO_OBLIGACION', 'ID_CLIENTE', 'NOMBRE', 'VLR_CUOTA', 'SALDO_CAPITAL', 'MODALIDAD'],
            ['1001', '9001', 'JUAN PEREZ', '100000', '500000', 'LIBRE INVERSION'],
            ['1002', '9002', 'ANA GOMEZ', '150000', '700000', 'MICROCREDITO'],
        ]);

        $saldosPath = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'TOTAL_VENCIDO', 'SALDO_CAPITAL', 'DIAS_MORA'],
            ['1001', '9001', '250000', '400000', '20'],
            ['1002', '9002', '120000', '300000', '10'],
            ['1003', '9003', '90000', '0', '5'],
            ['1004', '9004', '80000', '100000', '3'],
            ['1005', '9005', '70000', '110000', '2'],
        ]);

        $postCierrePath = $this->createCsv([
            ['AP - Identificación', 'AP - Nombre 1', 'AP - Nombre 2', 'AP - Apellido 1', 'AP - Apellido 2', 'CA - Valor Cuota', 'CA - Número de Obligación', ''],
            ['9004', 'JAIRO', 'ALEXANDER', 'AGUDELO', 'GUEVARA', '65603', '1004', ''],
            ['9002', 'ANA', '', 'GOMEZ', '', '999999', '1002', ''],
        ], '|');

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $carteraPath,
            $saldosPath,
            '2026-03-24',
            $postCierrePath,
        );

        $this->assertSame(4, $resultado['procesados']);
        $this->assertSame(4, $resultado['creados']);
        $this->assertSame(0, $resultado['actualizados']);
        $this->assertSame(0, $resultado['ignorados_iguales']);
        $this->assertSame(0, $resultado['sin_coincidencia_saldos']);
        $this->assertFileExists($resultado['zip_path']);

        $creditoConVencidoMayor = PlanoSaldoValor::query()
            ->where('obligacion', '1001')
            ->firstOrFail();

        $this->assertSame('9001', $creditoConVencidoMayor->cc);
        $this->assertSame(250000.0, (float) $creditoConVencidoMayor->valor_reportar);
        $this->assertSame(100000.0, (float) $creditoConVencidoMayor->valor_cuota);
        $this->assertSame(250000.0, (float) $creditoConVencidoMayor->valor_vencido);
        $this->assertSame('mensual', $creditoConVencidoMayor->origen_registro);
        $this->assertSame('activo', $creditoConVencidoMayor->estado_registro);
        $this->assertSame('nuevo', $creditoConVencidoMayor->ultimo_estado_saldo_diario);
        $this->assertSame('Valor vencido', $creditoConVencidoMayor->observacion);

        $creditoConCuotaMayor = PlanoSaldoValor::query()
            ->where('obligacion', '1002')
            ->firstOrFail();

        $this->assertSame(270000.0, (float) $creditoConCuotaMayor->valor_reportar);
        $this->assertSame(150000.0, (float) $creditoConCuotaMayor->valor_cuota);
        $this->assertSame('Cuota + vencido', $creditoConCuotaMayor->observacion);

        $creditoPostCierre = PlanoSaldoValor::query()
            ->where('obligacion', '1004')
            ->firstOrFail();

        $this->assertSame('9004', $creditoPostCierre->cc);
        $this->assertSame('JAIRO ALEXANDER', $creditoPostCierre->nombres);
        $this->assertSame('AGUDELO GUEVARA', $creditoPostCierre->apellidos);
        $this->assertSame(80000.0, (float) $creditoPostCierre->valor_reportar);
        $this->assertSame(65603.0, (float) $creditoPostCierre->valor_cuota);
        $this->assertSame('post_cierre', $creditoPostCierre->origen_registro);
        $this->assertSame('Valor vencido', $creditoPostCierre->observacion);

        $creditoNuevoSoloEnSaldos = PlanoSaldoValor::query()
            ->where('obligacion', '1005')
            ->firstOrFail();

        $this->assertSame(70000.0, (float) $creditoNuevoSoloEnSaldos->valor_reportar);
        $this->assertSame(0.0, (float) $creditoNuevoSoloEnSaldos->valor_cuota);
        $this->assertSame('saldos_diario', $creditoNuevoSoloEnSaldos->origen_registro);
        $this->assertDatabaseMissing('plano_saldos_valores', [
            'obligacion' => '1003',
            'cc' => '9003',
        ]);

        $this->assertSame(5, PlanoSaldoValorSaldoDiario::query()->count());

        $saldoCero = PlanoSaldoValorSaldoDiario::query()
            ->where('obligacion', '1003')
            ->where('cc', '9003')
            ->firstOrFail();

        $this->assertSame(0.0, (float) $saldoCero->saldo_capital);
        $this->assertSame('nuevo', $saldoCero->estado_movimiento);
    }

    public function test_it_truncates_company_name_to_45_characters_in_generated_files(): void
    {
        $companyName = 'COMERCIALIZADORA Y DISTRIBUIDORA NACIONAL DE ALIMENTOS DEL SUR SAS';

        $carteraPath = $this->createCsv([
            ['NO_OBLIGACION', 'ID_CLIENTE', 'NOMBRE', 'VLR_CUOTA', 'SALDO_CAPITAL', 'MODALIDAD'],
            ['2001', '9010', $companyName, '185000', '900000', 'MICROCREDITO'],
        ]);

        $saldosPath = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'TOTAL_VENCIDO', 'SALDO_CAPITAL', 'DIAS_MORA'],
            ['2001', '9010', '125000', '810000', '14'],
        ]);

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $carteraPath,
            $saldosPath,
            '2026-03-24',
        );

        $expectedName = rtrim(mb_substr($companyName, 0, 45, 'UTF-8'));
        $contenidoRe = $this->readZipEntry($resultado['zip_path'], 'archivo_Re.csv');
        $contenidoGou = $this->readZipEntry($resultado['zip_path'], 'archivo_Gou.csv');

        $rowsRe = $this->parseCsvContent($contenidoRe);
        $rowsGou = $this->parseCsvContent($contenidoGou);

        $nombreIndex = array_search('NOMBRE_CLIENTE', $rowsRe[0], true);
        $apellidoIndex = array_search('APELLIDO_CLIENTE', $rowsRe[0], true);

        $this->assertNotFalse($nombreIndex);
        $this->assertNotFalse($apellidoIndex);
        $this->assertSame($expectedName, $rowsRe[1][$nombreIndex]);
        $this->assertSame('', $rowsRe[1][$apellidoIndex]);
        $this->assertSame($expectedName, $rowsGou[1][3]);
        $this->assertLessThanOrEqual(45, mb_strlen($rowsRe[1][$nombreIndex], 'UTF-8'));
        $this->assertLessThanOrEqual(45, mb_strlen($rowsGou[1][3], 'UTF-8'));
    }

    public function test_it_registers_daily_snapshots_and_detects_balance_changes(): void
    {
        $carteraPath = $this->createCsv([
            ['NO_OBLIGACION', 'ID_CLIENTE', 'NOMBRE', 'VLR_CUOTA', 'SALDO_CAPITAL', 'MODALIDAD'],
            ['3001', '9901', 'MARIA LOPEZ', '120000', '800000', 'MICROCREDITO'],
        ]);

        $saldosDiaUno = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'TOTAL_VENCIDO', 'SALDO_CAPITAL', 'DIAS_MORA'],
            ['3001', '9901', '150000', '780000', '18'],
        ]);

        $saldosDiaDos = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'TOTAL_VENCIDO', 'SALDO_CAPITAL', 'DIAS_MORA'],
            ['3001', '9901', '90000', '720000', '10'],
        ]);

        app(PlanoSaldoValorImportService::class)->import(
            $carteraPath,
            $saldosDiaUno,
            '2026-03-24',
        );

        app(PlanoSaldoValorImportService::class)->import(
            $carteraPath,
            $saldosDiaDos,
            '2026-03-25',
        );

        $record = PlanoSaldoValor::query()
            ->where('obligacion', '3001')
            ->where('cc', '9901')
            ->firstOrFail();

        $this->assertSame(90000.0, (float) $record->valor_vencido);
        $this->assertSame(720000.0, (float) $record->saldo_capital);
        $this->assertSame(210000.0, (float) $record->valor_reportar);
        $this->assertSame('disminuyo', $record->ultimo_estado_saldo_diario);

        $snapshots = PlanoSaldoValorSaldoDiario::query()
            ->where('obligacion', '3001')
            ->where('cc', '9901')
            ->orderBy('fecha_archivo')
            ->get();

        $this->assertCount(2, $snapshots);
        $this->assertSame('nuevo', $snapshots[0]->estado_movimiento);
        $this->assertSame('disminuyo', $snapshots[1]->estado_movimiento);
        $this->assertSame(210000.0, (float) $snapshots[1]->valor_reportar);
        $this->assertSame(-60000.0, (float) $snapshots[1]->variacion_valor_vencido);
        $this->assertSame(-60000.0, (float) $snapshots[1]->variacion_saldo_capital);
    }

    public function test_it_generates_complete_zip_files_for_more_than_two_thousand_rows(): void
    {
        $totalRegistros = 2050;
        $carteraRows = [
            ['NO_OBLIGACION', 'ID_CLIENTE', 'NOMBRE', 'VLR_CUOTA', 'SALDO_CAPITAL', 'MODALIDAD'],
        ];
        $saldosRows = [
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'TOTAL_VENCIDO', 'SALDO_CAPITAL', 'DIAS_MORA'],
        ];

        for ($i = 1; $i <= $totalRegistros; $i++) {
            $obligacion = (string) (700000 + $i);
            $documento = (string) (9000000 + $i);

            $carteraRows[] = [
                $obligacion,
                $documento,
                'CLIENTE ' . $i,
                '100000',
                '500000',
                'MICROCREDITO',
            ];

            $saldosRows[] = [
                $obligacion,
                $documento,
                '25000',
                '450000',
                '5',
            ];
        }

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $this->createCsv($carteraRows),
            $this->createCsv($saldosRows),
            '2026-03-24',
        );

        $contenidoRe = $this->readZipEntry($resultado['zip_path'], 'archivo_Re.csv');
        $rowsRe = $this->parseCsvContent($contenidoRe);
        $zipEntries = $this->listZipEntries($resultado['zip_path']);
        $gouEntries = array_values(array_filter(
            $zipEntries,
            fn (string $name): bool => str_starts_with($name, 'archivo_Gou')
        ));

        $this->assertCount($totalRegistros + 1, $rowsRe);
        $this->assertSame([
            'archivo_Gou_01.csv',
            'archivo_Gou_02.csv',
            'archivo_Gou_03.csv',
        ], $gouEntries);
        $this->assertSame('700001', $rowsRe[1][2]);
        $this->assertSame((string) (700000 + $totalRegistros), $rowsRe[$totalRegistros][2]);

        $totalDataRowsGou = 0;
        $expectedChunkSizes = [1000, 1000, 50];

        foreach ($gouEntries as $index => $gouEntry) {
            $rowsGou = $this->parseCsvContent($this->readZipEntry($resultado['zip_path'], $gouEntry));

            $this->assertCount($expectedChunkSizes[$index] + 1, $rowsGou);
            $this->assertSame((string) $expectedChunkSizes[$index], $rowsGou[0][4]);

            $totalDataRowsGou += count($rowsGou) - 1;
        }

        $this->assertSame($totalRegistros, $totalDataRowsGou);
    }

    public function test_it_can_export_a_zip_from_current_database_records(): void
    {
        $carteraPath = $this->createCsv([
            ['NO_OBLIGACION', 'ID_CLIENTE', 'NOMBRE', 'VLR_CUOTA', 'SALDO_CAPITAL', 'MODALIDAD'],
            ['8101', '9911', 'CLIENTE ACTUAL', '98000', '450000', 'MICROCREDITO'],
        ]);

        $saldosPath = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'TOTAL_VENCIDO', 'SALDO_CAPITAL', 'DIAS_MORA'],
            ['8101', '9911', '15000', '410000', '4'],
        ]);

        app(PlanoSaldoValorImportService::class)->import(
            $carteraPath,
            $saldosPath,
            '2026-03-24',
        );

        $resultado = app(PlanoSaldoValorImportService::class)->exportFromDatabase('2026-03-24');

        $this->assertSame(1, $resultado['procesados']);
        $this->assertSame('2026-03-24', $resultado['fecha_vigencia']);
        $this->assertFileExists($resultado['zip_path']);
        $this->assertSame([
            'archivo_Re.csv',
            'archivo_Gou.csv',
        ], $this->listZipEntries($resultado['zip_path']));
    }

    private function createCsv(array $rows, string $delimiter = ';'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'plano_saldo_valor_');

        if ($path === false) {
            $this->fail('No fue posible crear un archivo temporal para la prueba.');
        }

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            $this->fail('No fue posible abrir el archivo temporal para la prueba.');
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row, $delimiter);
        }

        fclose($handle);

        return $path;
    }

    private function readZipEntry(string $zipPath, string $entryName): string
    {
        $zip = new ZipArchive();
        $opened = $zip->open($zipPath);

        if ($opened !== true) {
            $this->fail('No fue posible abrir el ZIP generado por la importacion.');
        }

        $content = $zip->getFromName($entryName);
        $zip->close();

        if (!is_string($content)) {
            $this->fail('No fue posible leer la entrada ' . $entryName . ' del ZIP generado.');
        }

        return $content;
    }

    private function listZipEntries(string $zipPath): array
    {
        $zip = new ZipArchive();
        $opened = $zip->open($zipPath);

        if ($opened !== true) {
            $this->fail('No fue posible abrir el ZIP generado para listar sus archivos.');
        }

        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (is_string($name)) {
                $entries[] = $name;
            }
        }

        $zip->close();

        return $entries;
    }

    private function parseCsvContent(string $content): array
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            $this->fail('No fue posible preparar el lector temporal del CSV.');
        }

        fwrite($stream, preg_replace('/^\xEF\xBB\xBF/u', '', $content) ?? $content);
        rewind($stream);

        $rows = [];

        while (($row = fgetcsv($stream)) !== false) {
            if ($row === [null]) {
                continue;
            }

            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }
}
