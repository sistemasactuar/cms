<?php

namespace Tests\Feature;

use App\Models\PlanoSaldoValor;
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
            $table->decimal('valor_reportar', 18, 2)->nullable();
            $table->decimal('valor_cuota', 18, 2)->nullable();
            $table->string('modalidad')->nullable();
            $table->string('periodo')->nullable();
            $table->string('observacion')->nullable();
            $table->decimal('saldo_capital', 18, 2)->nullable();
            $table->integer('dias_mora')->nullable();
            $table->date('fecha_vigencia')->nullable();
            $table->timestamps();
            $table->unique(['cc', 'obligacion'], 'uk_cc_obligacion');
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
        $this->assertSame('Valor vencido', $creditoConVencidoMayor->observacion);

        $creditoConCuotaMayor = PlanoSaldoValor::query()
            ->where('obligacion', '1002')
            ->firstOrFail();

        $this->assertSame(150000.0, (float) $creditoConCuotaMayor->valor_reportar);
        $this->assertSame(150000.0, (float) $creditoConCuotaMayor->valor_cuota);
        $this->assertSame('Valor cuota', $creditoConCuotaMayor->observacion);

        $creditoPostCierre = PlanoSaldoValor::query()
            ->where('obligacion', '1004')
            ->firstOrFail();

        $this->assertSame('9004', $creditoPostCierre->cc);
        $this->assertSame('JAIRO ALEXANDER', $creditoPostCierre->nombres);
        $this->assertSame('AGUDELO GUEVARA', $creditoPostCierre->apellidos);
        $this->assertSame(80000.0, (float) $creditoPostCierre->valor_reportar);
        $this->assertSame(65603.0, (float) $creditoPostCierre->valor_cuota);
        $this->assertSame('Valor vencido', $creditoPostCierre->observacion);

        $creditoNuevoSoloEnSaldos = PlanoSaldoValor::query()
            ->where('obligacion', '1005')
            ->firstOrFail();

        $this->assertSame(70000.0, (float) $creditoNuevoSoloEnSaldos->valor_reportar);
        $this->assertSame(0.0, (float) $creditoNuevoSoloEnSaldos->valor_cuota);
        $this->assertDatabaseMissing('plano_saldos_valores', [
            'obligacion' => '1003',
            'cc' => '9003',
        ]);
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
