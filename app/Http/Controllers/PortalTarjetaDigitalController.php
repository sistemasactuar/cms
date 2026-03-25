<?php

namespace App\Http\Controllers;

use App\Models\PlanoSaldoValor;
use App\Services\PlanoSaldoValorCardImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            'hasActiveAccess' => $this->getAuthorizedRecords($request)->isNotEmpty(),
            'accessTtlMinutes' => self::ACCESS_TTL_MINUTES,
        ]);
    }

    public function validateAccess(Request $request): RedirectResponse
    {
        $this->ensureIsNotRateLimited($request);

        $validator = Validator::make($request->all(), [
            'documento' => ['required', 'string', 'max:40'],
            'fecha_nacimiento' => ['required', 'string', 'max:20'],
        ], [
            'documento.required' => 'Ingresa tu documento para continuar.',
            'fecha_nacimiento.required' => 'Ingresa tu fecha de nacimiento.',
        ]);

        if ($validator->fails()) {
            $this->hitRateLimit($request);

            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $documento = $this->normalizeKey($data['documento']);
        $fechaNacimiento = $this->normalizeDateInput($data['fecha_nacimiento']);

        if ($fechaNacimiento === null) {
            $this->hitRateLimit($request);

            throw ValidationException::withMessages([
                'fecha_nacimiento' => 'Ingresa una fecha valida en formato dia/mes/ano.',
            ]);
        }

        $records = PlanoSaldoValor::query()
            ->where('cc', $documento)
            ->whereDate('fecha_nacimiento', $fechaNacimiento->toDateString())
            ->orderByDesc('fecha_vigencia')
            ->orderBy('obligacion')
            ->get();

        if ($records->isEmpty()) {
            $this->hitRateLimit($request);

            $hasDocumentMatchesWithoutBirthDate = PlanoSaldoValor::query()
                ->where('cc', $documento)
                ->whereNull('fecha_nacimiento')
                ->exists();

            if ($hasDocumentMatchesWithoutBirthDate) {
                throw ValidationException::withMessages([
                    'fecha_nacimiento' => 'Aun no tenemos tu fecha de nacimiento registrada para esta consulta. Comunicate con Actuar para actualizar tus datos.',
                ]);
            }

            throw ValidationException::withMessages([
                'documento' => 'No pudimos encontrar una tarjeta con la informacion ingresada. Revisa tu documento y tu fecha de nacimiento.',
            ]);
        }

        RateLimiter::clear($this->rateLimitKey($request));

        $request->session()->put(self::ACCESS_SESSION_KEY, [
            'record_ids' => $records->pluck('id')->all(),
            'authorized_at' => now()->toIso8601String(),
        ]);

        return redirect()
            ->route('tarjeta-digital.portal.show')
            ->with('success', $records->count() === 1
                ? 'Listo. Tu tarjeta ya esta disponible para descarga.'
                : 'Listo. Encontramos tus tarjetas disponibles para descarga.');
    }

    public function accessPage(Request $request): View|RedirectResponse
    {
        $records = $this->getAuthorizedRecords($request);

        if ($records->isEmpty()) {
            return redirect()
                ->route('tarjeta-digital.portal.index')
                ->with('error', 'Tu consulta ya vencio o aun no has encontrado una tarjeta.');
        }

        return view('tarjeta-digital.show', [
            'records' => $records,
            'accessTtlMinutes' => self::ACCESS_TTL_MINUTES,
        ]);
    }

    public function downloadCard(Request $request, PlanoSaldoValor $record)
    {
        if (!$this->isAuthorizedRecord($request, $record->id)) {
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

    private function getAuthorizedRecords(Request $request): Collection
    {
        $access = $request->session()->get(self::ACCESS_SESSION_KEY);

        if (!is_array($access) || empty($access['record_ids']) || empty($access['authorized_at'])) {
            return collect();
        }

        try {
            $authorizedAt = Carbon::parse($access['authorized_at']);
        } catch (\Throwable) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return collect();
        }

        if ($authorizedAt->copy()->addMinutes(self::ACCESS_TTL_MINUTES)->isPast()) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return collect();
        }

        $recordIds = collect($access['record_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        if ($recordIds->isEmpty()) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return collect();
        }

        $records = PlanoSaldoValor::query()
            ->whereIn('id', $recordIds->all())
            ->orderByDesc('fecha_vigencia')
            ->orderBy('obligacion')
            ->get();

        if ($records->isEmpty()) {
            $request->session()->forget(self::ACCESS_SESSION_KEY);

            return collect();
        }

        return $records;
    }

    private function isAuthorizedRecord(Request $request, int $recordId): bool
    {
        return $this->getAuthorizedRecords($request)
            ->contains(fn (PlanoSaldoValor $record): bool => $record->id === $recordId);
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
        $fechaNacimiento = $this->normalizeDateInput((string) $request->input('fecha_nacimiento', ''))?->toDateString() ?? '';
        $ip = (string) $request->ip();

        return implode('|', ['tarjeta-digital', $ip, $documento, $fechaNacimiento]);
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

    private function normalizeDateInput(string $value): ?Carbon
    {
        $date = trim($value);

        if ($date === '') {
            return null;
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);

                if ($parsed !== false) {
                    return $parsed->startOfDay();
                }
            } catch (\Throwable) {
                // Continue with the next format.
            }
        }

        return null;
    }
}
