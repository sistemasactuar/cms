<?php

namespace Tests\Feature;

use App\Http\Controllers\PlanoSaldoValorExportDownloadController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PlanoSaldoValorExportDownloadControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('cache.default', 'array');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken()->nullable();
            $table->timestamps();
        });
    }

    public function test_it_downloads_zip_without_deleting_it_immediately(): void
    {
        $directory = storage_path('app/temp/plano-saldo-valors-tests');

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            $this->fail('No fue posible preparar el directorio temporal para la prueba.');
        }

        $zipPath = tempnam($directory, 'zip_');

        if ($zipPath === false) {
            $this->fail('No fue posible crear el archivo temporal para la prueba.');
        }

        file_put_contents($zipPath, 'contenido-prueba');

        $token = 'zip-test-token';

        Cache::put(PlanoSaldoValorExportDownloadController::makeCacheKey($token), [
            'path' => $zipPath,
            'name' => 'archivos.zip',
        ], now()->addMinutes(5));

        $user = User::query()->create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => bcrypt('secret'),
        ]);

        $url = URL::signedRoute('admin.plano-saldo-valors.download', ['token' => $token]);

        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertHeader('content-type', 'application/zip')
            ->assertHeader('content-disposition', 'attachment; filename=archivos.zip');

        $this->assertFileExists($zipPath);

        @unlink($zipPath);
    }
}
