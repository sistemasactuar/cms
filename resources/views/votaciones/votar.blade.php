@extends('votaciones.layout', [
    'title' => $votacion->titulo,
    'subtitle' => 'Paso 3 de 3. Marca tu seleccion y registra el voto una sola vez.',
    'step' => 3,
    'aportante' => $aportante,
    'logoUrl' => $votacion->logo_url,
])

@section('content')
    @php
        $maxSeleccion = $votacion->maxSeleccionesPermitidas();
    @endphp

    <form method="POST" action="{{ route('votaciones.portal.vote.submit', $votacion) }}" class="space-y-6">
        @csrf

        <section class="surface-card rounded-[30px] p-6 md:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span class="badge-soft {{ $votacion->tipo_votacion === 'planilla' ? 'orange' : 'blue' }}">
                        {{ $votacion->tipo_votacion === 'planilla' ? 'Voto por planilla' : 'Voto nominal' }}
                    </span>
                    <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Marca tu seleccion</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                        @if ($votacion->tipo_votacion === 'planilla')
                            Selecciona una planilla. El sistema registrara un unico voto para la opcion elegida.
                        @else
                            Puedes seleccionar hasta {{ $maxSeleccion }} candidato(s). Cuando termines, presiona el boton de registrar voto.
                        @endif
                    </p>
                </div>

                <div class="rounded-[22px] bg-slate-50 px-5 py-4 text-sm text-slate-700">
                    <p class="font-bold text-slate-900">Recordatorio</p>
                    <p class="mt-1">Una vez se registre el voto, no podra cambiarse.</p>
                    @if ($votacion->tipo_votacion === 'nominal')
                        <p class="mt-3 font-bold text-slate-900" id="selection-counter">0 / {{ $maxSeleccion }} seleccionados</p>
                    @endif
                </div>
            </div>
        </section>

        @if ($votacion->tipo_votacion === 'planilla')
            <section class="grid gap-5 md:grid-cols-2">
                @forelse ($votacion->planillas as $planilla)
                    <label class="selection-card" style="--accent: {{ $planilla->color ?: '#0f4c81' }};" data-select-card>
                        <input type="radio" name="planilla_id" value="{{ $planilla->id }}" class="sr-only vote-radio" @checked((int) old('planilla_id') === $planilla->id)>

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($planilla->numero)
                                        <span class="badge-soft blue">Planilla {{ $planilla->numero }}</span>
                                    @endif
                                    <span class="badge-soft green">{{ $planilla->candidatos->count() }} integrante(s)</span>
                                </div>
                                <h3 class="mt-4 text-xl font-extrabold text-slate-900">{{ $planilla->nombre }}</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-600">{{ $planilla->descripcion ?: 'Planilla habilitada para votacion.' }}</p>
                            </div>
                            @if ($planilla->logo_path)
                                <div class="mini-logo-card h-20 w-20 rounded-[22px] p-3">
                                    <img src="{{ $planilla->logo_url }}" alt="Logo {{ $planilla->nombre }}">
                                </div>
                            @endif
                        </div>

                        @if ($planilla->candidatos->isNotEmpty())
                            <div class="mt-5 rounded-[20px] bg-slate-50 p-4">
                                <p class="text-sm font-bold text-slate-900">Integrantes</p>
                                <div class="mt-3 space-y-3">
                                    @foreach ($planilla->candidatos as $candidato)
                                        <div class="flex items-start gap-3 rounded-[16px] bg-white px-4 py-3">
                                            <div class="avatar-round h-11 w-11 text-sm">
                                                {{ \Illuminate\Support\Str::of($candidato->nombre)->explode(' ')->take(2)->map(fn ($fragment) => \Illuminate\Support\Str::substr($fragment, 0, 1))->implode('') }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $candidato->nombre }}</p>
                                                <p class="text-sm text-slate-500">{{ $candidato->cargo ?: 'Candidato' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </label>
                @empty
                    <div class="surface-card rounded-[30px] p-8 text-center text-slate-500 md:col-span-2">
                        Aun no hay planillas activas para esta votacion.
                    </div>
                @endforelse
            </section>
        @else
            <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($votacion->candidatos as $candidato)
                    <label class="selection-card" data-select-card>
                        <input type="checkbox" name="candidatos[]" value="{{ $candidato->id }}" class="sr-only vote-checkbox" @checked(collect(old('candidatos', []))->map(fn ($item) => (int) $item)->contains($candidato->id))>

                        <div class="flex items-start gap-4">
                            <div class="avatar-round">
                                @if ($candidato->foto_path)
                                    <img src="{{ Storage::disk('public')->url($candidato->foto_path) }}" alt="Foto de {{ $candidato->nombre }}" class="h-full w-full object-cover">
                                @else
                                    {{ \Illuminate\Support\Str::of($candidato->nombre)->explode(' ')->take(2)->map(fn ($fragment) => \Illuminate\Support\Str::substr($fragment, 0, 1))->implode('') }}
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-wrap gap-2">
                                    @if ($candidato->numero)
                                        <span class="badge-soft blue">Nro {{ $candidato->numero }}</span>
                                    @endif
                                    @if ($candidato->planilla)
                                        <span class="badge-soft orange">{{ $candidato->planilla->nombre }}</span>
                                    @endif
                                </div>
                                <h3 class="mt-4 text-lg font-extrabold text-slate-900">{{ $candidato->nombre }}</h3>
                                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $candidato->cargo ?: 'Candidato' }}</p>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $candidato->descripcion ?: 'Opcion habilitada para votacion nominal.' }}</p>
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="surface-card rounded-[30px] p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                        Aun no hay candidatos activos para esta votacion.
                    </div>
                @endforelse
            </section>
        @endif

        <div class="sticky-action">
            <div class="surface-card flex flex-col gap-4 rounded-[28px] p-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[.18em] text-slate-400">Confirmacion final</p>
                    <p class="mt-2 text-base font-bold text-slate-900">Revisa tu seleccion y registra el voto una sola vez.</p>
                </div>
                <div class="flex flex-col gap-3 md:flex-row">
                    <a href="{{ route('votaciones.portal.dashboard') }}" class="secondary-btn inline-flex items-center justify-center px-6">Volver al panel</a>
                    <button class="primary-btn inline-flex items-center justify-center px-8 text-lg">Registrar voto</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cardSelector = '[data-select-card]';
            const checkboxSelector = '.vote-checkbox';
            const radioSelector = '.vote-radio';
            const counter = document.getElementById('selection-counter');
            const maxAllowed = {{ $maxSeleccion }};

            const refreshSelectedStyles = () => {
                document.querySelectorAll(cardSelector).forEach((card) => {
                    const input = card.querySelector('input');
                    card.classList.toggle('is-selected', !!input?.checked);
                });

                if (counter) {
                    const total = [...document.querySelectorAll(checkboxSelector)].filter((input) => input.checked).length;
                    counter.textContent = `${total} / ${maxAllowed} seleccionados`;
                }
            };

            document.querySelectorAll(checkboxSelector).forEach((input) => {
                input.addEventListener('change', (event) => {
                    const checked = [...document.querySelectorAll(checkboxSelector)].filter((item) => item.checked);

                    if (checked.length > maxAllowed) {
                        event.target.checked = false;
                        refreshSelectedStyles();
                        window.alert(`Solo puedes seleccionar hasta ${maxAllowed} candidato(s).`);
                        return;
                    }

                    refreshSelectedStyles();
                });
            });

            document.querySelectorAll(radioSelector).forEach((input) => {
                input.addEventListener('change', refreshSelectedStyles);
            });

            refreshSelectedStyles();
        });
    </script>
@endsection
