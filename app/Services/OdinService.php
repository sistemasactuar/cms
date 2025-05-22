<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OdinService
{
    protected string $baseUrl = 'https://odin.selsacloud.com/linix/v7/38eb463e-cf8a-4c31-ab2e-eb18674726ed';

    public function authenticate(): ?string
    {
        return Cache::remember('odin_token', 3600, function () {
            $response = Http::asForm()
                ->withBasicAuth(
                    config('services.odin.client_id'),
                    config('services.odin.client_secret')
                )
                ->post("{$this->baseUrl}/servicio/identidad/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
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
        $token = $this->authenticate();

        if (! $token) {
            throw new \Exception('Token de autenticación ODIN no disponible.');
        }

        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/RUTA/TERCEROS");

        return $response->json();
    }
}
