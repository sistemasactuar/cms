@extends('votaciones.layout', [
    'title' => 'Resultados - ' . $votacion->titulo,
    'subtitle' => 'Resultados finales de la votacion por Plancha.',
    'step' => 3,
    'aportante' => $aportante,
    'logoUrl' => $votacion->logo_url,
])

@section('content')
    <section class="surface-card rounded-[30px] p-6 md:p-8 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="badge-soft orange">Resultado por Listas</span>
                <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Distribucion de puestos</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Total de personas que han votado: <strong class="text-slate-900">{{ $totalVotosValidos }}</strong></p>
                <p class="mt-1 text-sm leading-7 text-slate-600">Puestos a elegir: <strong class="text-slate-900">{{ $votacion->cupos }}</strong></p>
            </div>
            <a href="{{ route('votaciones.portal.dashboard') }}" class="secondary-btn inline-flex items-center justify-center px-6">Volver al panel</a>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2">
        @foreach ($planillas as $planilla)
            @php
                $datos = $distribucion[$planilla->id] ?? ['votos' => 0, 'porcentaje' => 0, 'cupos' => 0];
            @endphp
            <article class="surface-card rounded-[30px] p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($planilla->numero)
                                <span class="badge-soft blue">Lista {{ $planilla->numero }}</span>
                            @endif
                            <span class="badge-soft green">{{ $datos['cupos'] }} cargo(s) obtenido(s)</span>
                        </div>
                        <h3 class="mt-4 text-xl font-extrabold text-slate-900">{{ $planilla->nombre }}</h3>
                    </div>
                    @if ($planilla->logo_path)
                        <div class="mini-logo-card h-20 w-20 rounded-[22px] p-3">
                            <img src="{{ $planilla->logo_url }}" alt="Logo {{ $planilla->nombre }}">
                        </div>
                    @endif
                </div>

                <div class="mt-6 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                    <div class="rounded-[20px] bg-slate-50 px-4 py-3">
                        <p class="font-bold text-slate-900">Votos Obtenidos</p>
                        <p class="mt-1 text-2xl font-black text-indigo-600">{{ $datos['votos'] }}</p>
                    </div>
                    <div class="rounded-[20px] bg-slate-50 px-4 py-3">
                        <p class="font-bold text-slate-900">Porcentaje</p>
                        <p class="mt-1 text-2xl font-black text-indigo-600">{{ $datos['porcentaje'] }}%</p>
                    </div>
                </div>

                @if ($planilla->candidatos->isNotEmpty())
                    <div class="mt-5 rounded-[20px] bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-900">Integrantes</p>
                        <div class="mt-3 space-y-3">
                            @foreach ($planilla->candidatos as $candidato)
                                <div class="flex items-start gap-3 rounded-[16px] bg-white px-4 py-3">
                                    <div class="avatar-round h-11 w-11 text-sm bg-slate-100">
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
            </article>
        @endforeach
    </section>
@endsection
