<?php

namespace App\Console\Commands;

use App\Services\OdinService;
use Illuminate\Console\Command;

class TestOdinIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odin:test {identificacion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test integration with Odin Service';

    /**
     * Execute the console command.
     */
    public function handle(OdinService $odinService)
    {
        $identificacion = $this->argument('identificacion');

        $this->info('Testing Odin Integration...');

        try {
            // 1. Authenticate
            $this->info('Authenticating...');
            $token = $odinService->authenticate();
            if ($token) {
                $this->info('Authentication successful.');
            } else {
                $this->error('Authentication failed.');
                return;
            }

            // 2. Get Cliente by Identificacion
            $this->info("Fetching Cliente with Identificacion: {$identificacion}");
            $clienteData = $odinService->getClienteByIdentificacion($identificacion);
            $this->info('Result: ' . json_encode($clienteData, JSON_PRETTY_PRINT));

            // Extract Cliente ID if available
            // Assuming structure, but will fallback if not found
            // This part depends on the response structure of getClienteByIdentificacion
            // For now, I'll just print the output of the first call as it's the entry point.

            // If we knew the response ID, we could chain tests:
            // $clienteId = $clienteData['id'] ?? null;
            // if ($clienteId) {
            //     $this->info("Fetching Basic Info for Cliente ID: {$clienteId}");
            //     $info = $odinService->getClienteInformacionBasica($clienteId);
            //     $this->info('Result: ' . json_encode($info));
            // }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
