<?php

namespace Tests\Feature;

use App\Models\PlanoSaldoValor;
use App\Services\PlanoSaldoValorImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

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
        ]);

        $resultado = app(PlanoSaldoValorImportService::class)->import(
            $carteraPath,
            $saldosPath,
            '2026-03-24',
        );

        $this->assertSame(3, $resultado['procesados']);
        $this->assertSame(3, $resultado['creados']);
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

        $creditoNuevoSoloEnSaldos = PlanoSaldoValor::query()
            ->where('obligacion', '1004')
            ->firstOrFail();

        $this->assertSame(80000.0, (float) $creditoNuevoSoloEnSaldos->valor_reportar);
        $this->assertSame(0.0, (float) $creditoNuevoSoloEnSaldos->valor_cuota);
        $this->assertDatabaseMissing('plano_saldos_valores', [
            'obligacion' => '1003',
            'cc' => '9003',
        ]);
    }

    private function createCsv(array $rows): string
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
            fputcsv($handle, $row, ';');
        }

        fclose($handle);

        return $path;
    }
}
