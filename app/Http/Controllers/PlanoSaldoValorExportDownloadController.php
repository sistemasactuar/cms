<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlanoSaldoValorExportDownloadController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $payload = Cache::get($this->cacheKey($token));

        if (!is_array($payload) || empty($payload['path'])) {
            abort(404, 'El archivo temporal ya no esta disponible.');
        }

        $path = (string) $payload['path'];
        $downloadName = trim((string) ($payload['name'] ?? ''));

        if (!is_file($path)) {
            Cache::forget($this->cacheKey($token));

            abort(404, 'El archivo temporal ya no esta disponible.');
        }

        return response()
            ->download($path, $downloadName !== '' ? $downloadName : basename($path), [
                'Content-Type' => 'application/zip',
            ])
            ->deleteFileAfterSend(true);
    }

    public static function makeCacheKey(string $token): string
    {
        return 'plano-saldo-valors.download.' . $token;
    }

    private function cacheKey(string $token): string
    {
        return self::makeCacheKey($token);
    }
}
