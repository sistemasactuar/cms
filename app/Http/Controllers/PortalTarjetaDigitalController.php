<?php

namespace App\Http\Controllers;

use App\Models\PlanoSaldoValor;
use App\Services\PlanoSaldoValorCardImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalTarjetaDigitalController extends Controller
{
    private const ACCESS_SESSION_KEY = 'tarjeta_digital_portal';
    private const ACCESS_TTL_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 600;

    public function landing(Request $request): View
    {
        return view('tarjeta-digital.index', [
            'hasActiveAccess' => $this->getAuthorizedRecord($request) !== null,
            'accessTtlMinutes' => self::ACCESS_TTL_MINUTES,
        ]);
    }

    public function validateAccess(Request $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $validator = Validator::make($request->all(), [
            'documento' => ['required', 'string', 'max:40'],
            'credito' => ['required', 'string', 'max:40'],
        ], [
            'documento.required' => 'Ingresa tu documento para continuar.',
            'credito.required' => 'Ingresa tu numero de credito.',
        ]);

        if ($validator->fails()) {
            $this->hitRateLimit($request);

            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $documento = $this->normalizeKey($data['documento']);
        $credito = $this->normalizeKey($data['credito']);

        $record = PlanoSaldoValor::query()
            ->where('cc', $documento)
            ->where('obligacion', $credito)
            ->first();

        if ($record === null) {
            $this->hitRateLimit($request);

            throw ValidationException::withMessages([
                'documento' => 'No pudimos encontrar una tarjeta con la informacion ingresada. Revisa tu documento y numero de credito.',
            ]);
        }

        RateLimiter::clear($this->rateLimitKey($request));

        $request->session()->put(self::ACCESS_SESSION_KEY, [
            'record_id' => $record->id,
            'authorized_at' => now()->toIso8601String(),
        ]);

        return redirect()
            ->route('tarjeta-digital.portal.show')
            ->with('success', 'Listo. Tu tarjeta ya esta disponible para descarga.');
    }

    public function accessPage(Request $request): View|RedirectResponse
    {
        $record = $this->getAuthorizedRecord($request);

        if ($record === null) {
            return redirect()
                ->route('tarjeta-digital.portal.index')
                ->with('error', 'Tu consulta ya vencio o aun no has encontrado una tarjeta.');
        }

        return view('tarjeta-digital.show', [
            'record' => $record,
            'accessTtlMinutes' => self::ACCESS_TTL_MINUTES,
        ]);
    }

    public function downloadCard(Request $request)
    {
        $record = $this->getAuthorizedRecord($request);

        if ($record === null) {
            return redirect()
                ->route('tarjeta-digital.portal.index')
                ->with('error', 'Tu consulta ya vencio. Vuelve a ingresar los datos para descargar la tarjeta.');
        }

        $png = app(PlanoSaldoValorCardImageService::class)->generate($record);
        $obligacion = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $record->obligacion);
        $fileSuffix = $obligacion !== '' ? $obligacion : (string) $record->id;
        $fileName = "tarjeta_digital_{$fileSuffix}.png";

        return response()->streamDownload(
            fn () => print($png),
            $fileName,
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ],
        );
    }

    public function clearAccess(Request $request): RedirectResponse
    {
        $request->session()->forget(self::ACCESS_SESSION_KEY);

        return redirect()
            ->route('tarjeta-digital.portal.index')
            ->with('success', 'Puedes hacer otra consulta cuando quieras.');
    }

    private function getAuthorizedRecord(Request $request): ?PlanoSaldoValor
    {
        $access = $request->session()->get(self::ACCESS_SESSION_KEY);

        if (!is_array($access) || empty($access['record_id']) || empty($access['authorized_at'])) {
            return null;
        }

        try {
            $authorizedAt = Carbon::parse($access['authorized_at']);
        } catch (\Throwable) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return null;
        }

        if ($authorizedAt->copy()->addMinutes(self::ACCESS_TTL_MINUTES)->isPast()) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return null;
        }

        $record = PlanoSaldoValor::query()->find($access['record_id']);

        if ($record === null) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return null;
        }

        return $record;
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->rateLimitKey($request);

        if (!RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'documento' => "Superaste el numero de intentos permitidos. Intenta de nuevo en {$seconds} segundos.",
        ]);
    }

    private function hitRateLimit(Request $request): void
    {
        RateLimiter::hit($this->rateLimitKey($request), self::DECAY_SECONDS);
    }

    private function rateLimitKey(Request $request): string
    {
        $documento = $this->normalizeKey((string) $request->input('documento', ''));
        $credito = $this->normalizeKey((string) $request->input('credito', ''));
        $ip = (string) $request->ip();

        return implode('|', ['tarjeta-digital', $ip, $documento, $credito]);
    }

    private function normalizeKey(string $value): string
    {
        $key = trim($value);

        if ($key === '') {
            return '';
        }

        if (preg_match('/^\d+\.0+$/', $key) === 1) {
            $key = substr($key, 0, (int) strpos($key, '.'));
        }

        return str_replace(' ', '', $key);
    }
}
