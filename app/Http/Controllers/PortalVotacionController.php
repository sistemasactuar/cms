<?php

namespace App\Http\Controllers;

use App\Models\Aportante;
use App\Models\Votacion;
use App\Models\VotacionVoto;
use App\Models\VotacionVotoDetalle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalVotacionController extends Controller
{
    public function login(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('aportante_id')) {
            return redirect()->route('votaciones.portal.dashboard');
        }

        $votaciones = Votacion::query()
            ->disponiblesPortal()
            ->withCount([
                'candidatos',
                'planillas',
                'votos as votos_emitidos_count' => fn ($query) => $query->whereNotNull('voto_emitido_at'),
            ])
            ->get();

        return view('votaciones.login', compact('votaciones'));
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'documento' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $aportante = Aportante::query()
            ->where('documento', $data['documento'])
            ->where('activo', true)
            ->first();

        if (!$aportante || !Hash::check($data['password'], $aportante->password)) {
            throw ValidationException::withMessages([
                'documento' => 'Los datos de acceso no coinciden con un aportante habilitado.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('aportante_id', $aportante->id);

        $aportante->forceFill([
            'ultimo_ingreso_at' => now(),
        ])->save();

        return redirect()
            ->route('votaciones.portal.dashboard')
            ->with('success', 'Ingreso correcto. Ya puedes revisar las votaciones disponibles.');
    }

    public function dashboard(Request $request): View
    {
        /** @var Aportante $aportante */
        $aportante = $request->attributes->get('aportante');

        $votaciones = Votacion::query()
            ->disponiblesPortal()
            ->withCount([
                'candidatos',
                'planillas',
                'votos as votos_emitidos_count' => fn ($query) => $query->whereNotNull('voto_emitido_at'),
            ])
            ->get();

        $registros = VotacionVoto::query()
            ->with(['planilla', 'detalles.candidato'])
            ->where('aportante_id', $aportante->id)
            ->whereIn('votacion_id', $votaciones->pluck('id'))
            ->get()
            ->keyBy('votacion_id');

        return view('votaciones.dashboard', compact('aportante', 'votaciones', 'registros'));
    }

    public function agenda(Request $request, Votacion $votacion): View|RedirectResponse
    {
        /** @var Aportante $aportante */
        $aportante = $request->attributes->get('aportante');

        if ($response = $this->ensureVotingAvailable($votacion)) {
            return $response;
        }

        $registro = $this->getOrCreateRegistro($votacion, $aportante);

        if ($registro->voto_emitido_at) {
            return redirect()
                ->route('votaciones.portal.dashboard')
                ->with('success', 'Tu voto ya fue registrado para esta votacion.');
        }

        if (!$votacion->aceptacion_obligatoria) {
            return redirect()->route('votaciones.portal.vote.form', $votacion);
        }

        return view('votaciones.agenda', compact('aportante', 'votacion', 'registro'));
    }

    public function acceptAgenda(Request $request, Votacion $votacion): RedirectResponse
    {
        /** @var Aportante $aportante */
        $aportante = $request->attributes->get('aportante');

        if ($response = $this->ensureVotingAvailable($votacion)) {
            return $response;
        }

        $request->validate([
            'acepta_orden_dia' => ['accepted'],
        ], [
            'acepta_orden_dia.accepted' => 'Debes aceptar el orden del dia para continuar al voto.',
        ]);

        $registro = $this->getOrCreateRegistro($votacion, $aportante);

        $registro->forceFill([
            'acepto_orden_dia_at' => $registro->acepto_orden_dia_at ?: now(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ])->save();

        return redirect()
            ->route('votaciones.portal.vote.form', $votacion)
            ->with('success', 'Orden del dia aceptado. Ahora puedes registrar tu voto.');
    }

    public function voteForm(Request $request, Votacion $votacion): View|RedirectResponse
    {
        /** @var Aportante $aportante */
        $aportante = $request->attributes->get('aportante');

        if ($response = $this->ensureVotingAvailable($votacion)) {
            return $response;
        }

        $registro = $this->getOrCreateRegistro($votacion, $aportante);

        if ($votacion->aceptacion_obligatoria && !$registro->acepto_orden_dia_at) {
            return redirect()->route('votaciones.portal.agenda', $votacion);
        }

        if ($registro->voto_emitido_at) {
            return redirect()
                ->route('votaciones.portal.dashboard')
                ->with('success', 'Tu voto ya fue registrado para esta votacion.');
        }

        $votacion->load([
            'candidatos' => fn ($query) => $query->where('activo', true)->with('planilla'),
            'planillas' => fn ($query) => $query->where('activo', true)->with([
                'candidatos' => fn ($candidateQuery) => $candidateQuery->where('activo', true),
            ]),
        ]);

        return view('votaciones.votar', compact('aportante', 'votacion', 'registro'));
    }

    public function submitVote(Request $request, Votacion $votacion): RedirectResponse
    {
        /** @var Aportante $aportante */
        $aportante = $request->attributes->get('aportante');

        if ($response = $this->ensureVotingAvailable($votacion)) {
            return $response;
        }

        $registro = $this->getOrCreateRegistro($votacion, $aportante);

        if ($registro->voto_emitido_at) {
            return redirect()
                ->route('votaciones.portal.dashboard')
                ->with('success', 'Tu voto ya estaba registrado y no puede modificarse.');
        }

        if ($votacion->aceptacion_obligatoria && !$registro->acepto_orden_dia_at) {
            return redirect()->route('votaciones.portal.agenda', $votacion);
        }

        DB::transaction(function () use ($request, $votacion, $aportante, $registro): void {
            $freshRegistro = VotacionVoto::query()
                ->whereKey($registro->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($freshRegistro->voto_emitido_at) {
                throw ValidationException::withMessages([
                    'votacion' => 'Este voto ya fue registrado previamente.',
                ]);
            }

            $freshRegistro->detalles()->delete();
            $freshRegistro->planilla_id = null;

            if ($votacion->tipo_votacion === 'planilla') {
                $data = $request->validate([
                    'planilla_id' => ['required', 'integer'],
                ], [
                    'planilla_id.required' => 'Debes seleccionar una planilla antes de continuar.',
                ]);

                $planillaId = $votacion->planillas()
                    ->where('activo', true)
                    ->whereKey($data['planilla_id'])
                    ->value('id');

                if (!$planillaId) {
                    throw ValidationException::withMessages([
                        'planilla_id' => 'La planilla seleccionada ya no esta disponible.',
                    ]);
                }

                $freshRegistro->planilla_id = $planillaId;
            } else {
                $data = $request->validate([
                    'candidatos' => ['required', 'array', 'min:1'],
                    'candidatos.*' => ['integer'],
                ], [
                    'candidatos.required' => 'Debes seleccionar al menos un candidato.',
                    'candidatos.min' => 'Debes seleccionar al menos un candidato.',
                ]);

                $selecciones = collect($data['candidatos'])
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $maxPermitido = $votacion->maxSeleccionesPermitidas();

                if ($selecciones->count() > $maxPermitido) {
                    throw ValidationException::withMessages([
                        'candidatos' => "Solo puedes seleccionar hasta {$maxPermitido} candidato(s).",
                    ]);
                }

                $candidatosValidos = $votacion->candidatos()
                    ->where('activo', true)
                    ->whereIn('id', $selecciones)
                    ->pluck('id');

                if ($candidatosValidos->count() !== $selecciones->count()) {
                    throw ValidationException::withMessages([
                        'candidatos' => 'Una o varias opciones ya no estan disponibles.',
                    ]);
                }

                $detalles = $candidatosValidos
                    ->map(fn ($candidatoId) => [
                        'voto_id' => $freshRegistro->id,
                        'candidato_id' => $candidatoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all();

                VotacionVotoDetalle::query()->insert($detalles);
            }

            $freshRegistro->forceFill([
                'aportante_id' => $aportante->id,
                'acepto_orden_dia_at' => $freshRegistro->acepto_orden_dia_at ?: now(),
                'voto_emitido_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ])->save();
        });

        return redirect()
            ->route('votaciones.portal.dashboard')
            ->with('success', 'Tu voto fue registrado correctamente. Gracias por participar.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('aportante_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('votaciones.portal.login')
            ->with('success', 'Sesion cerrada correctamente.');
    }

    private function getOrCreateRegistro(Votacion $votacion, Aportante $aportante): VotacionVoto
    {
        return VotacionVoto::query()->firstOrCreate([
            'votacion_id' => $votacion->id,
            'aportante_id' => $aportante->id,
        ]);
    }

    private function ensureVotingAvailable(Votacion $votacion): ?RedirectResponse
    {
        if (!$votacion->activo || $votacion->estado !== 'publicada') {
            return redirect()
                ->route('votaciones.portal.dashboard')
                ->with('error', 'La votacion solicitada no se encuentra habilitada.');
        }

        if (!$votacion->estaAbiertaAhora()) {
            return redirect()
                ->route('votaciones.portal.dashboard')
                ->with('error', 'La votacion no se encuentra disponible en este momento.');
        }

        return null;
    }
}
