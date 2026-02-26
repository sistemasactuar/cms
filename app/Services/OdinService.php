<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OdinService
{
    protected string $baseUrl = 'https://odin.selsacloud.com/linix/v7/38eb463e-cf8a-4c31-ab2e-eb18674726ed';

    public function authenticate(): ?array
    {
        return Cache::remember('odin_auth_data', 3600, function () {
            // Reverting to standard Basic Auth + Empty Body
            $response = Http::withoutVerifying()->asForm()
                ->withBasicAuth(
                    config('services.odin.client_id'),
                    config('services.odin.client_secret')
                )
                ->post("{$this->baseUrl}/servicio/identidad/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return [
                    'token' => $response->json('access_token'),
                    'realm' => $response->json('realm'), // Capture Realm
                ];
            }

            logger()->error('Error autenticando con ODIN', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return null;
        });
    }

    public function getTerceros()
    {
        $authData = $this->authenticate();

        if (! $authData || empty($authData['token'])) {
            throw new \Exception('Token de autenticación ODIN no disponible.');
        }

        $request = Http::withoutVerifying()->withToken($authData['token']);

        // Add Realm header if available
        if (!empty($authData['realm'])) {
            $request->withHeaders([
                'realm' => $authData['realm'],
            ]);
        }

        $response = $request->get("{$this->baseUrl}/RUTA/TERCEROS");

        return $response->json();
    }

    public function getClienteByIdentificacion(string $identificacion)
    {
        return $this->get('/datos/crm/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente', [
            'identificacion' => $identificacion,
        ]);
    }

    public function getClienteInformacionBasica(string $idCliente)
    {
        return $this->get("/datos/crm/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente/{$idCliente}/informacion-basica");
    }

    public function getClienteEmpresa()
    {
        return $this->get('/datos/crm/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente');
    }

    public function getClienteDireccion(string $idCliente)
    {
        return $this->get("/datos/crm/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente/{$idCliente}/informacion-contactos");
    }

    public function getClienteVinculacion(string $idCliente)
    {
        return $this->get("/datos/crm/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente/{$idCliente}/vinculacion");
    }

    public function getClienteInfoLaboral(string $idCliente)
    {
        return $this->get("/datos/crm/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente/{$idCliente}/informacion-laboral");
    }

    public function getEstatutaria(string $idCliente)
    {
        return $this->get("/datos/crm/aportes/estatutario/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente/{$idCliente}/obligacion");
    }

    public function getObligacion(string $idCliente)
    {
        return $this->get("/datos/crm/cartera/empresa/38eb463e-cf8a-4c31-ab2e-eb18674726ed/cliente/{$idCliente}/obligacion");
    }

    protected function get(string $endpoint, array $query = [])
    {
        $authData = $this->authenticate();

        if (! $authData || empty($authData['token'])) {
            throw new \Exception('Token de autenticación ODIN no disponible.');
        }

        $request = Http::withoutVerifying()->withToken($authData['token']);

        // Add Realm header if available
        if (!empty($authData['realm'])) {
            $request->withHeaders([
                'realm' => $authData['realm'],
            ]);
        }

        $response = $request->get($this->baseUrl . $endpoint, $query);

        if ($response->failed()) {
            logger()->error("Error en solicitud ODIN: {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }
}
