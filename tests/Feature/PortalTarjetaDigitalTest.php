<?php

namespace Tests\Feature;

use App\Models\PlanoSaldoValor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalTarjetaDigitalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('session.driver', 'array');
        config()->set('cache.default', 'array');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->withoutMiddleware(ValidateCsrfToken::class);

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

    public function test_clients_can_validate_and_download_their_card(): void
    {
        $record = PlanoSaldoValor::query()->create([
            'obligacion' => '100200300',
            'cc' => '123456789',
            'nombres' => 'Laura',
            'apellidos' => 'Ramirez',
            'valor_reportar' => 185000,
            'valor_cuota' => 185000,
            'saldo_capital' => 620000,
            'fecha_vigencia' => '2026-03-24',
        ]);

        $this->get('/tarjeta-digital')
            ->assertOk()
            ->assertSee('Consulta y descarga tu tarjeta digital');

        $this->post('/tarjeta-digital/validar', [
            'documento' => '123456789',
            'credito' => '100200300',
        ])->assertRedirect(route('tarjeta-digital.portal.show'));

        $this->get('/tarjeta-digital/descarga')
            ->assertOk()
            ->assertSee('Laura Ramirez')
            ->assertSee('100200300');

        $this->get('/tarjeta-digital/descargar')
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('content-disposition', 'attachment; filename=tarjeta_digital_100200300.png');

        $this->assertSame($record->id, session('tarjeta_digital_portal.record_id'));
    }

    public function test_validation_fails_when_data_does_not_match(): void
    {
        PlanoSaldoValor::query()->create([
            'obligacion' => '200300400',
            'cc' => '987654321',
            'nombres' => 'Carlos',
            'apellidos' => 'Lopez',
            'valor_reportar' => 99000,
            'valor_cuota' => 99000,
            'saldo_capital' => 550000,
            'fecha_vigencia' => '2026-03-24',
        ]);

        $this->from('/tarjeta-digital')
            ->post('/tarjeta-digital/validar', [
                'documento' => '987654321',
                'credito' => '999999999',
            ])
            ->assertRedirect('/tarjeta-digital')
            ->assertSessionHasErrors('documento');

        $this->get('/tarjeta-digital/descarga')
            ->assertRedirect(route('tarjeta-digital.portal.index'));
    }

    public function test_download_requires_active_session_access(): void
    {
        $record = PlanoSaldoValor::query()->create([
            'obligacion' => '300400500',
            'cc' => '1122334455',
            'nombres' => 'Marta',
            'apellidos' => 'Diaz',
            'valor_reportar' => 75000,
            'valor_cuota' => 75000,
            'saldo_capital' => 410000,
            'fecha_vigencia' => '2026-03-24',
        ]);

        $expiredSession = [
            'record_id' => $record->id,
            'authorized_at' => Carbon::now()->subMinutes(16)->toIso8601String(),
        ];

        $this->withSession(['tarjeta_digital_portal' => $expiredSession])
            ->get('/tarjeta-digital/descargar')
            ->assertRedirect(route('tarjeta-digital.portal.index'));
    }
}
