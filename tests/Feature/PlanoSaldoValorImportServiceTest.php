<?php

namespace Tests\Feature;

use App\Models\PlanoSaldoValor;
use App\Models\PlanoSaldoValorSaldoDiario;
use App\Services\PlanoSaldoValorImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public function test_it_imports_using_saldos_quota_and_complementary_identity_rules(): void
    {
        $complementarioPath = $this->createCsv([
            $this->complementarioHeaders(),
            $this->makeComplementarioRow('9001', 'JUAN', '', 'PEREZ', '', '999999', '1001', 'LIBRE INVERSION'),
            $this->makeComplementarioRow('9002', 'ANA', '', 'GOMEZ', '', '888888', '1002', 'MICROCREDITO'),
            $this->makeComplementarioRow('9004', 'JAIRO', 'ALEXANDER', 'AGUDELO', 'GUEVARA', '65603', '', 'ROTATIVO'),
        ], '|');

        $saldosPath = $this->createCsv([
            $this->saldosHeaders(),
            $this->makeSaldosRow('1001', '9001', '20', '100000', '50000', '400000', '180000', '20000', '250000'),
            $this->makeSaldosRow('1002', '9002', '10', '150000', '0', '300000', '100000', '20000', '120000'),
            $this->makeSaldosRow('1003', '9003', '5', '80000', '0', '0', '0', '0', '90000'),
            $this->makeSaldosRow('1004', '9004', '4', '150000', '5000', '100000', '5000', '5000', '10000'),
            $this->makeSaldosRow('1005', '9005', '2', '70000', '0', '110000', '50000', '20000', '70000'),
        ]);

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $complementarioPath,
            $saldosPath,
            '2026-03-24',
        );

        $this->assertSame(4, $resultado['procesados']);
        $this->assertSame(4, $resultado['creados']);
        $this->assertSame(0, $resultado['actualizados']);
        $this->assertSame(0, $resultado['ignorados_iguales']);
        $this->assertSame(5, $resultado['registros_saldos']);
        $this->assertSame(0, $resultado['sin_coincidencia_saldos']);
        $this->assertFileExists($resultado['zip_path']);

        $creditoConVencidoMayor = PlanoSaldoValor::query()
            ->where('obligacion', '1001')
            ->firstOrFail();

        $this->assertSame('9001', $creditoConVencidoMayor->cc);
        $this->assertSame(250000.0, (float) $creditoConVencidoMayor->valor_reportar);
        $this->assertSame(100000.0, (float) $creditoConVencidoMayor->valor_cuota);
        $this->assertSame(250000.0, (float) $creditoConVencidoMayor->valor_vencido);
        $this->assertSame('complementario', $creditoConVencidoMayor->origen_registro);
        $this->assertSame('LIBRE INVERSION', $creditoConVencidoMayor->modalidad);
        $this->assertSame('Valor vencido', $creditoConVencidoMayor->observacion);

        $creditoConCuotaMayor = PlanoSaldoValor::query()
            ->where('obligacion', '1002')
            ->firstOrFail();

        $this->assertSame(150000.0, (float) $creditoConCuotaMayor->valor_reportar);
        $this->assertSame(150000.0, (float) $creditoConCuotaMayor->valor_cuota);
        $this->assertSame('MICROCREDITO', $creditoConCuotaMayor->modalidad);
        $this->assertSame('Valor cuota', $creditoConCuotaMayor->observacion);

        $creditoComplementadoPorDocumento = PlanoSaldoValor::query()
            ->where('obligacion', '1004')
            ->firstOrFail();

        $this->assertSame('9004', $creditoComplementadoPorDocumento->cc);
        $this->assertSame('JAIRO ALEXANDER', $creditoComplementadoPorDocumento->nombres);
        $this->assertSame('AGUDELO GUEVARA', $creditoComplementadoPorDocumento->apellidos);
        $this->assertSame(160000.0, (float) $creditoComplementadoPorDocumento->valor_reportar);
        $this->assertSame(150000.0, (float) $creditoComplementadoPorDocumento->valor_cuota);
        $this->assertSame('ROTATIVO', $creditoComplementadoPorDocumento->modalidad);
        $this->assertSame('complementario', $creditoComplementadoPorDocumento->origen_registro);
        $this->assertSame('Cuota + vencido', $creditoComplementadoPorDocumento->observacion);

        $creditoNuevoSoloEnSaldos = PlanoSaldoValor::query()
            ->where('obligacion', '1005')
            ->firstOrFail();

        $this->assertSame(70000.0, (float) $creditoNuevoSoloEnSaldos->valor_reportar);
        $this->assertSame(70000.0, (float) $creditoNuevoSoloEnSaldos->valor_cuota);
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

    public function test_it_uses_the_ten_percent_threshold_when_overdue_is_lower_than_quota(): void
    {
        $complementarioPath = $this->createCsv([
            $this->complementarioHeaders(),
            $this->makeComplementarioRow('9101', 'CARLOS', '', 'RUIZ', '', '999999', '1101', 'MICROCREDITO'),
            $this->makeComplementarioRow('9102', 'LINA', '', 'TORRES', '', '999999', '1102', 'MICROCREDITO'),
        ], '|');

        $saldosPath = $this->createCsv([
            $this->saldosHeaders(),
            $this->makeSaldosRow('1101', '9101', '4', '150000', '0', '450000', '5000', '5000', '10000'),
            $this->makeSaldosRow('1102', '9102', '5', '150000', '0', '470000', '10000', '5000', '15000'),
        ]);

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $complementarioPath,
            $saldosPath,
            '2026-03-24',
        );

        $this->assertSame(2, $resultado['procesados']);

        $creditoDebajoDelUmbral = PlanoSaldoValor::query()
            ->where('obligacion', '1101')
            ->firstOrFail();

        $this->assertSame(160000.0, (float) $creditoDebajoDelUmbral->valor_reportar);
        $this->assertSame(150000.0, (float) $creditoDebajoDelUmbral->valor_cuota);
        $this->assertSame(10000.0, (float) $creditoDebajoDelUmbral->valor_vencido);
        $this->assertSame('Cuota + vencido', $creditoDebajoDelUmbral->observacion);

        $creditoEnElUmbral = PlanoSaldoValor::query()
            ->where('obligacion', '1102')
            ->firstOrFail();

        $this->assertSame(150000.0, (float) $creditoEnElUmbral->valor_reportar);
        $this->assertSame(150000.0, (float) $creditoEnElUmbral->valor_cuota);
        $this->assertSame(15000.0, (float) $creditoEnElUmbral->valor_vencido);
        $this->assertSame('Valor cuota', $creditoEnElUmbral->observacion);
    }

    public function test_it_accepts_legacy_semicolon_complementario_and_falls_back_to_its_quota(): void
    {
        $complementarioPath = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'NOMBRES', 'APELLIDOS', 'VLR_CUOTA', 'MODALIDAD'],
            ['5101', '95101', 'LUZ', 'MENA', '125000', 'MICROCREDITO'],
        ]);

        $saldosPath = $this->createCsv([
            ['NUMERO_CREDITO', 'NUMERO_DOCUMENTO', 'DIAS_MORA', 'SALDO_CAPITAL', 'CAPITAL_VENCIDO', 'INTERES_VENCIDO', 'VALOR_MORA', 'TOTAL_VENCIDO'],
            ['5101', '95101', '3', '420000', '30000', '5000', '0', '35000'],
        ]);

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $complementarioPath,
            $saldosPath,
            '2026-03-24',
        );

        $this->assertSame(1, $resultado['procesados']);

        $record = PlanoSaldoValor::query()
            ->where('obligacion', '5101')
            ->where('cc', '95101')
            ->firstOrFail();

        $this->assertSame('LUZ', $record->nombres);
        $this->assertSame('MENA', $record->apellidos);
        $this->assertSame('MICROCREDITO', $record->modalidad);
        $this->assertSame(125000.0, (float) $record->valor_cuota);
        $this->assertSame(125000.0, (float) $record->valor_reportar);
        $this->assertSame('Valor cuota', $record->observacion);
    }

    public function test_it_preserves_existing_personal_data_when_a_saldos_row_has_no_complement_match(): void
    {
        PlanoSaldoValor::query()->create([
            'obligacion' => '2001',
            'cc' => '9010',
            'nombres' => 'LAURA',
            'apellidos' => 'RAMIREZ',
            'fecha_nacimiento' => '1990-04-12',
            'modalidad' => 'LIBRE INVERSION',
            'valor_reportar' => 50000,
            'valor_cuota' => 50000,
            'valor_vencido' => 0,
            'saldo_capital' => 300000,
            'dias_mora' => 0,
            'fecha_vigencia' => '2026-03-20',
            'fecha_entrada_plano' => '2026-03-20',
            'origen_registro' => 'mensual',
            'estado_registro' => 'activo',
        ]);

        $complementarioPath = $this->createCsv([
            $this->complementarioHeaders(),
        ], '|');

        $saldosPath = $this->createCsv([
            $this->saldosHeaders(),
            $this->makeSaldosRow('2001', '9010', '4', '98000', '4000', '410000', '5000', '10000', '15000'),
        ]);

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $complementarioPath,
            $saldosPath,
            '2026-03-24',
        );

        $this->assertSame(1, $resultado['procesados']);
        $this->assertSame(0, $resultado['creados']);
        $this->assertSame(1, $resultado['actualizados']);

        $record = PlanoSaldoValor::query()
            ->where('obligacion', '2001')
            ->where('cc', '9010')
            ->firstOrFail();

        $this->assertSame('LAURA', $record->nombres);
        $this->assertSame('RAMIREZ', $record->apellidos);
        $this->assertSame('1990-04-12', $record->fecha_nacimiento?->toDateString());
        $this->assertSame('LIBRE INVERSION', $record->modalidad);
        $this->assertSame('mensual', $record->origen_registro);
        $this->assertSame(98000.0, (float) $record->valor_cuota);
        $this->assertSame(15000.0, (float) $record->valor_vencido);
        $this->assertSame(98000.0, (float) $record->valor_reportar);
        $this->assertSame('Valor cuota', $record->observacion);
    }

    public function test_it_truncates_company_name_to_35_characters_in_generated_files(): void
    {
        $companyName = 'COMERCIALIZADORA Y DISTRIBUIDORA NACIONAL DE ALIMENTOS DEL SUR SAS';

        Carbon::setTestNow('2026-04-10 08:00:00');

        try {
            $complementarioPath = $this->createCsv([
                ['AP - Identificación', 'CA - Valor Cuota', 'CA - Número de Obligación', 'NOMBRE', 'CA - Código Modalidad'],
                ['9010', '185000', '2001', $companyName, 'MICROCREDITO'],
            ], '|');

            $saldosPath = $this->createCsv([
                $this->saldosHeaders(),
                $this->makeSaldosRow('2001', '9010', '14', '185000', '0', '810000', '100000', '25000', '125000'),
            ]);

            $resultado = app(PlanoSaldoValorImportService::class)->import(
                $complementarioPath,
                $saldosPath,
                '2026-03-24',
            );
        } finally {
            Carbon::setTestNow();
        }

        $expectedName = rtrim(mb_substr($companyName, 0, 35, 'UTF-8'));
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
        $this->assertSame('20260324', $rowsGou[1][5]);
        $this->assertSame('20260425', $rowsGou[1][7]);
        $this->assertLessThanOrEqual(35, mb_strlen($rowsRe[1][$nombreIndex], 'UTF-8'));
        $this->assertLessThanOrEqual(35, mb_strlen($rowsGou[1][3], 'UTF-8'));
    }

    public function test_it_registers_daily_snapshots_and_detects_balance_changes(): void
    {
        $complementarioPath = $this->createCsv([
            $this->complementarioHeaders(),
            $this->makeComplementarioRow('9901', 'MARIA', '', 'LOPEZ', '', '120000', '3001', 'MICROCREDITO'),
        ], '|');

        $saldosDiaUno = $this->createCsv([
            $this->saldosHeaders(),
            $this->makeSaldosRow('3001', '9901', '18', '120000', '0', '780000', '120000', '30000', '150000'),
        ]);

        $saldosDiaDos = $this->createCsv([
            $this->saldosHeaders(),
            $this->makeSaldosRow('3001', '9901', '10', '120000', '0', '720000', '60000', '30000', '90000'),
        ]);

        app(PlanoSaldoValorImportService::class)->import(
            $complementarioPath,
            $saldosDiaUno,
            '2026-03-24',
        );

        app(PlanoSaldoValorImportService::class)->import(
            $complementarioPath,
            $saldosDiaDos,
            '2026-03-25',
        );

        $record = PlanoSaldoValor::query()
            ->where('obligacion', '3001')
            ->where('cc', '9901')
            ->firstOrFail();

        $this->assertSame(90000.0, (float) $record->valor_vencido);
        $this->assertSame(720000.0, (float) $record->saldo_capital);
        $this->assertSame(120000.0, (float) $record->valor_reportar);
        $this->assertSame('disminuyo', $record->ultimo_estado_saldo_diario);

        $snapshots = PlanoSaldoValorSaldoDiario::query()
            ->where('obligacion', '3001')
            ->where('cc', '9901')
            ->orderBy('fecha_archivo')
            ->get();

        $this->assertCount(2, $snapshots);
        $this->assertSame('nuevo', $snapshots[0]->estado_movimiento);
        $this->assertSame('disminuyo', $snapshots[1]->estado_movimiento);
        $this->assertSame(120000.0, (float) $snapshots[1]->valor_reportar);
        $this->assertSame(-60000.0, (float) $snapshots[1]->variacion_valor_vencido);
        $this->assertSame(-60000.0, (float) $snapshots[1]->variacion_saldo_capital);
    }

    public function test_it_still_generates_zip_when_all_rows_match_existing_records(): void
    {
        $complementarioRows = [
            $this->complementarioHeaders(),
            $this->makeComplementarioRow('9951', 'LUISA', '', 'GARCIA', '', '110000', '4001', 'MICROCREDITO'),
            $this->makeComplementarioRow('9952', 'PEDRO', '', 'MARTINEZ', '', '95000', '4002', 'MICROCREDITO'),
        ];

        $saldosRows = [
            $this->saldosHeaders(),
            $this->makeSaldosRow('4001', '9951', '7', '110000', '0', '650000', '55000', '20000', '75000'),
            $this->makeSaldosRow('4002', '9952', '3', '95000', '0', '500000', '15000', '20000', '35000'),
        ];

        $complementarioPath = $this->createCsv($complementarioRows, '|');
        $saldosPath = $this->createCsv($saldosRows);

        Carbon::setTestNow('2026-03-24 09:00:00');

        try {
            app(PlanoSaldoValorImportService::class)->import(
                $complementarioPath,
                $saldosPath,
                '2026-03-24',
            );

            $recordAntes = PlanoSaldoValor::query()
                ->where('cc', '9951')
                ->where('obligacion', '4001')
                ->firstOrFail();

            $snapshotAntes = PlanoSaldoValorSaldoDiario::query()
                ->where('cc', '9951')
                ->where('obligacion', '4001')
                ->whereDate('fecha_archivo', '2026-03-24')
                ->firstOrFail();

            Carbon::setTestNow('2026-03-24 10:00:00');

            $resultado = app(PlanoSaldoValorImportService::class)->import(
                $complementarioPath,
                $saldosPath,
                '2026-03-24',
            );

            $recordDespues = $recordAntes->fresh();
            $snapshotDespues = $snapshotAntes->fresh();
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(0, $resultado['procesados']);
        $this->assertSame(0, $resultado['creados']);
        $this->assertSame(0, $resultado['actualizados']);
        $this->assertSame(2, $resultado['ignorados_iguales']);
        $this->assertFileExists($resultado['zip_path']);
        $this->assertSame([
            'archivo_Re.csv',
            'archivo_Gou.csv',
        ], $this->listZipEntries($resultado['zip_path']));

        $rowsRe = $this->parseCsvContent($this->readZipEntry($resultado['zip_path'], 'archivo_Re.csv'));
        $rowsGou = $this->parseCsvContent($this->readZipEntry($resultado['zip_path'], 'archivo_Gou.csv'));

        $this->assertCount(3, $rowsRe);
        $this->assertCount(3, $rowsGou);
        $this->assertSame('4001', $rowsRe[1][2]);
        $this->assertSame('4002', $rowsRe[2][2]);
        $this->assertSame(
            $recordAntes->updated_at?->toDateTimeString(),
            $recordDespues?->updated_at?->toDateTimeString(),
        );
        $this->assertSame(
            $snapshotAntes->updated_at?->toDateTimeString(),
            $snapshotDespues?->updated_at?->toDateTimeString(),
        );
    }

    public function test_it_generates_complete_zip_files_for_more_than_two_thousand_rows(): void
    {
        $totalRegistros = 2050;
        $complementarioRows = [$this->complementarioHeaders()];
        $saldosRows = [$this->saldosHeaders()];

        for ($i = 1; $i <= $totalRegistros; $i++) {
            $obligacion = (string) (700000 + $i);
            $documento = (string) (9000000 + $i);

            $complementarioRows[] = $this->makeComplementarioRow(
                $documento,
                'CLIENTE',
                (string) $i,
                'APELLIDO',
                (string) $i,
                '100000',
                $obligacion,
                'MICROCREDITO'
            );

            $saldosRows[] = $this->makeSaldosRow(
                $obligacion,
                $documento,
                '5',
                '100000',
                '5000',
                '450000',
                '10000',
                '10000',
                '25000',
            );
        }

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $this->createCsv($complementarioRows, '|'),
            $this->createCsv($saldosRows),
            '2026-03-24',
        );

        $contenidoRe = $this->readZipEntry($resultado['zip_path'], 'archivo_Re.csv');
        $contenidoGou = $this->readZipEntry($resultado['zip_path'], 'archivo_Gou.csv');
        $rowsRe = $this->parseCsvContent($contenidoRe);
        $rowsGou = $this->parseCsvContent($contenidoGou);

        $this->assertCount($totalRegistros + 1, $rowsRe);
        $this->assertCount($totalRegistros + 1, $rowsGou);
        $this->assertSame((string) $totalRegistros, $rowsGou[0][4]);
        $this->assertSame('700001', $rowsRe[1][2]);
        $this->assertSame((string) (700000 + $totalRegistros), $rowsRe[$totalRegistros][2]);
    }

    public function test_it_can_export_a_zip_from_current_database_records(): void
    {
        Carbon::setTestNow('2026-04-10 08:00:00');

        try {
            $complementarioPath = $this->createCsv([
                $this->complementarioHeaders(),
                $this->makeComplementarioRow('9911', 'CLIENTE', 'ACTUAL', '', '', '98000', '8101', 'MICROCREDITO'),
            ], '|');

            $saldosPath = $this->createCsv([
                $this->saldosHeaders(),
                $this->makeSaldosRow('8101', '9911', '4', '98000', '0', '410000', '5000', '10000', '15000'),
            ]);

            app(PlanoSaldoValorImportService::class)->import(
                $complementarioPath,
                $saldosPath,
                '2026-03-24',
            );

            $resultado = app(PlanoSaldoValorImportService::class)->exportFromDatabase('2026-03-24');
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame(1, $resultado['procesados']);
        $this->assertSame('2026-03-24', $resultado['fecha_vigencia']);
        $this->assertFileExists($resultado['zip_path']);
        $this->assertSame([
            'archivo_Re.csv',
            'archivo_Gou.csv',
        ], $this->listZipEntries($resultado['zip_path']));

        $rowsGou = $this->parseCsvContent($this->readZipEntry($resultado['zip_path'], 'archivo_Gou.csv'));
        $this->assertSame('20260324', $rowsGou[1][5]);
        $this->assertSame('20260425', $rowsGou[1][7]);
    }

    private function complementarioHeaders(): array
    {
        return [
            'AP - Identificación',
            'AP - Nombre 1',
            'AP - Nombre 2',
            'AP - Apellido 1',
            'AP - Apellido 2',
            'CA - Valor Cuota',
            'CA - Número de Obligación',
            'CA - Código Modalidad',
        ];
    }

    private function makeComplementarioRow(
        string $documento,
        string $nombreUno,
        string $nombreDos,
        string $apellidoUno,
        string $apellidoDos,
        string $valorCuota,
        string $obligacion,
        string $modalidad
    ): array {
        return [
            $documento,
            $nombreUno,
            $nombreDos,
            $apellidoUno,
            $apellidoDos,
            $valorCuota,
            $obligacion,
            $modalidad,
        ];
    }

    private function saldosHeaders(): array
    {
        return [
            'NUMERO_CREDITO',
            'NUMERO_DOCUMENTO',
            'DIAS_MORA',
            'DIAS_LINIX',
            'TOTAL_T_1_ALIVIO',
            'T_1_ALIVIO',
            'VALOR_CUOTA',
            'VALOR_MORA',
            'SALDO_CAPITAL',
            'CAPITAL_VENCIDO',
            'INTERES_VENCIDO',
            'SALDO_TOTAL',
            'TOTAL_VENCIDO',
            'NOVEDADES',
            'ESTADO_COBRO',
            'FECHA_CUOTA',
        ];
    }

    private function makeSaldosRow(
        string $obligacion,
        string $documento,
        string $diasMora,
        string $valorCuota,
        string $valorMora,
        string $saldoCapital,
        string $capitalVencido,
        string $interesVencido,
        string $totalVencido,
        string $fechaCuota = '2026-03-30'
    ): array {
        return [
            $obligacion,
            $documento,
            $diasMora,
            '0',
            '0',
            '0',
            $valorCuota,
            $valorMora,
            $saldoCapital,
            $capitalVencido,
            $interesVencido,
            (string) ((int) $saldoCapital + (int) $totalVencido),
            $totalVencido,
            '',
            'NORMAL',
            $fechaCuota,
        ];
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
