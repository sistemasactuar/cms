<?php

namespace App\Console\Commands;

use App\Services\OdinService;
use Illuminate\Console\Command;

class CreateOdinIncluirtecVinculacionCommand extends Command
{
    protected $signature = 'odin:incluirtec-vincular
        {payload_file : Ruta al archivo JSON con el payload}
        {--dry-run : Validar y mostrar el payload sin enviarlo}';

    protected $description = 'Envia una vinculacion Incluirtec a ODIN/LINIX usando un payload JSON.';

    public function handle(OdinService $odinService): int
    {
        $payloadFile = (string) $this->argument('payload_file');
        $resolvedPath = $this->resolvePath($payloadFile);

        if (!is_file($resolvedPath)) {
            $this->error("No existe el archivo: {$payloadFile}");
            return self::FAILURE;
        }

        $raw = file_get_contents($resolvedPath);
        if ($raw === false) {
            $this->error('No fue posible leer el archivo JSON.');
            return self::FAILURE;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $this->error('El archivo no contiene un JSON valido.');
            return self::FAILURE;
        }

        if (($payload['_t'] ?? null) !== 'LinixRequestModelComplete') {
            $this->warn('El payload no trae _t=LinixRequestModelComplete. Revisa el formato esperado por LINIX.');
        }

        $this->info('Payload cargado correctamente.');
        $this->line('Archivo: ' . $resolvedPath);
        $this->line('Documento: ' . ($payload['A_CODIGO_CLIENTE'] ?? 'N/D'));
        $this->line('Nombre: ' . trim(($payload['A_PRIMER_NOMBRE'] ?? '') . ' ' . ($payload['A_PRIMER_APELLIDO'] ?? '')));

        if ($this->option('dry-run')) {
            $this->line('');
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->comment('Dry-run finalizado. No se envio nada a ODIN.');
            return self::SUCCESS;
        }

        try {
            $response = $odinService->createIncluirtecVinculacion($payload);
        } catch (\Throwable $exception) {
            $this->error('Error enviando la vinculacion: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (($response['_failed'] ?? false) === true) {
            $this->error('ODIN respondio con error.');
            return self::FAILURE;
        }

        $this->info('Vinculacion enviada correctamente.');
        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (is_file($path)) {
            return $path;
        }

        return base_path($path);
    }
}
