<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OdinService
{
    public function authenticate(): ?array
    {
        $cacheKey = 'odin_auth_data_' . md5($this->baseUrl() . '|' . $this->empresaUuid() . '|' . $this->realm());

        return Cache::remember($cacheKey, 3300, function () {
            $request = $this->baseHttp()
                ->acceptJson()
                ->asForm()
                ->withBasicAuth(
                    (string) config('services.odin.client_id'),
                    (string) config('services.odin.client_secret')
                );

            if ($this->realm() !== '') {
                $request = $request->withHeaders([
                    'realm' => $this->realm(),
                ]);
            }

            $response = $request->post($this->baseUrl() . '/servicio/identidad/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                return [
                    'token' => $response->json('access_token'),
                    'realm' => $response->json('realm') ?: config('services.odin.realm'),
                ];
            }

            logger()->error('Error autenticando con ODIN', [
                'response' => $response->body(),
                'status' => $response->status(),
                'base_url' => $this->baseUrl(),
                'empresa_uuid' => $this->empresaUuid(),
                'realm' => $this->realm(),
            ]);

            return null;
        });
    }

    public function getTerceros(): array
    {
        return $this->request('GET', '/RUTA/TERCEROS');
    }

    public function getClienteByIdentificacion(string $identificacion): array
    {
        return $this->request('GET', $this->crmClientePath(), [
            'identificacion' => $identificacion,
        ]);
    }

    public function getClienteInformacionBasica(string $idCliente): array
    {
        return $this->request('GET', $this->crmClientePath("/{$idCliente}/informacion-basica"));
    }

    public function getClienteEmpresa(): array
    {
        return $this->request('GET', $this->crmClientePath());
    }

    public function getClienteDireccion(string $idCliente): array
    {
        return $this->request('GET', $this->crmClientePath("/{$idCliente}/informacion-contactos"));
    }

    public function getClienteVinculacion(string $idCliente): array
    {
        return $this->request('GET', $this->crmClientePath("/{$idCliente}/vinculacion"));
    }

    public function getClienteInfoLaboral(string $idCliente): array
    {
        return $this->request('GET', $this->crmClientePath("/{$idCliente}/informacion-laboral"));
    }

    public function getEstatutaria(string $idCliente): array
    {
        return $this->request('GET', "/datos/crm/aportes/estatutario/empresa/{$this->empresaUuid()}/cliente/{$idCliente}/obligacion");
    }

    public function getObligacion(string $idCliente): array
    {
        return $this->request('GET', "/datos/crm/cartera/empresa/{$this->empresaUuid()}/cliente/{$idCliente}/obligacion");
    }

    public function createIncluirtecVinculacion(array $payload): array
    {
        return $this->request(
            'POST',
            "/erp/servicio/workflow/funcionalidad/{$this->workflowFuncionalidadVinculacion()}/programacion",
            payload: $payload
        );
    }

    protected function request(
        string $method,
        string $endpoint,
        array $query = [],
        array $payload = [],
        array $headers = []
    ): array {
        $authData = $this->authenticate();

        if (! $authData || empty($authData['token'])) {
            throw new \RuntimeException('Token de autenticación ODIN no disponible.');
        }

        $request = $this->authorizedHttp((string) $authData['token'], (string) ($authData['realm'] ?? ''));
        if ($headers !== []) {
            $request = $request->withHeaders($headers);
        }

        $url = $this->baseUrl() . $endpoint;
        $method = strtoupper($method);

        $response = match ($method) {
            'GET' => $request->get($url, $query),
            'POST' => $request->asJson()->post($url, $payload),
            'PUT' => $request->asJson()->put($url, $payload),
            'PATCH' => $request->asJson()->patch($url, $payload),
            'DELETE' => $request->delete($url, $query),
            default => throw new \InvalidArgumentException("Metodo HTTP no soportado: {$method}"),
        };

        return $this->decodeResponse($response, $endpoint, $method);
    }

    protected function decodeResponse(Response $response, string $endpoint, string $method): array
    {
        $json = $response->json();

        if ($response->failed()) {
            logger()->error("Error en solicitud ODIN: {$method} {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (is_array($json)) {
                return array_merge($json, [
                    '_failed' => true,
                    '_status' => $response->status(),
                ]);
            }

            return [
                '_failed' => true,
                '_status' => $response->status(),
                '_body' => $response->body(),
            ];
        }

        if (is_array($json)) {
            return $json;
        }

        return [
            '_status' => $response->status(),
            '_body' => $response->body(),
        ];
    }

    protected function authorizedHttp(string $token, string $realm = ''): PendingRequest
    {
        $headers = [];
        $realm = trim($realm) !== '' ? $realm : (string) config('services.odin.realm', '');

        if ($realm !== '') {
            $headers['realm'] = $realm;
        }

        return $this->baseHttp()
            ->acceptJson()
            ->withToken($token)
            ->withHeaders($headers);
    }

    protected function baseHttp(): PendingRequest
    {
        $request = Http::timeout(60);

        if (! $this->verifySsl()) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    protected function crmClientePath(string $suffix = ''): string
    {
        return "/datos/crm/empresa/{$this->empresaUuid()}/cliente{$suffix}";
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.odin.base_url'), '/');
    }

    protected function empresaUuid(): string
    {
        return (string) config('services.odin.empresa_uuid');
    }

    protected function workflowFuncionalidadVinculacion(): string
    {
        return (string) config('services.odin.workflow_funcionalidad_vinculacion', '6');
    }

    protected function realm(): string
    {
        return trim((string) config('services.odin.realm', ''));
    }

    protected function verifySsl(): bool
    {
        return (bool) config('services.odin.verify_ssl', false);
    }
}
