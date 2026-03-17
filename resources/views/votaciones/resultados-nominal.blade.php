@extends('votaciones.layout', [
    'title' => 'Resultados - ' . $votacion->titulo,
    'subtitle' => 'Resultados de la votacion nominal.',
    'step' => 3,
    'aportante' => $aportante,
    'logoUrl' => $votacion->logo_url,
])

@section('content')
    <section class="surface-card rounded-[30px] p-6 md:p-8 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="badge-soft blue">Resultado Individual</span>
                <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Votacion por Candidatos</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Personas que han participado: <strong class="text-slate-900">{{ $totalParticipantes }}</strong></p>
                <p class="mt-1 text-sm leading-7 text-slate-600">Total de votos marcados: <strong class="text-slate-900">{{ $totalVotosValidos }}</strong></p>
            </div>
            <a href="{{ route('votaciones.portal.dashboard') }}" class="secondary-btn inline-flex items-center justify-center px-6">Volver al panel</a>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($candidatos as $candidato)
            @php
                $porcentaje = $totalParticipantes > 0 ? round(($candidato->total_votos / $totalParticipantes) * 100, 2) : 0;
            @endphp
            <article class="surface-card rounded-[30px] p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-start gap-4">
                        <div class="avatar-round flex-shrink-0 bg-slate-100">
                            @if ($candidato->foto_path)
                                <img src="{{ Storage::disk('public')->url($candidato->foto_path) }}" alt="Foto de {{ $candidato->nombre }}" class="h-full w-full object-cover">
                            @else
                                {{ \Illuminate\Support\Str::of($candidato->nombre)->explode(' ')->take(2)->map(fn ($fragment) => \Illuminate\Support\Str::substr($fragment, 0, 1))->implode('') }}
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-2 mb-2">
                                @if ($candidato->numero)
                                    <span class="badge-soft blue">Nro {{ $candidato->numero }}</span>
                                @endif
                                @if ($candidato->planilla)
                                    <span class="badge-soft orange">{{ $candidato->planilla->nombre }}</span>
                                @endif
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900 leading-tight">{{ $candidato->nombre }}</h3>
                            <p class="text-sm font-medium text-slate-500">{{ $candidato->cargo ?: 'Candidato' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-[20px] bg-slate-50 px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Votos Obtenidos</p>
                        <p class="mt-1 text-2xl font-black text-indigo-600">{{ $candidato->total_votos }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">% Part.</p>
                        <p class="mt-1 text-lg font-bold text-slate-700">{{ $porcentaje }}%</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
@endsection
