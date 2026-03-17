@extends('votaciones.layout', [
    'title' => 'Panel del participante',
    'subtitle' => 'Aqui encuentras las votaciones disponibles y el estado de tu participacion.',
    'step' => 2,
    'aportante' => $aportante,
    'logoUrl' => $votaciones->count() === 1 ? $votaciones->first()->logo_url : asset('images/LOGO-03.png'),
])

@section('content')
    <div class="grid gap-6">
        <section class="surface-card rounded-[30px] p-6 md:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="badge-soft green">Bienvenido</span>
                    <h2 class="mt-4 text-2xl font-extrabold text-slate-900">{{ $aportante->nombre }}</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Revisa cada votacion. El sistema te ira guiando primero por la aceptacion del orden del dia y luego al registro del voto.</p>
                </div>
                <div class="rounded-[24px] bg-slate-50 px-5 py-4 text-sm text-slate-600">
                    <p class="font-bold text-slate-900">Documento</p>
                    <p>{{ $aportante->documento }}</p>
                    <p class="mt-3 font-bold text-slate-900">Ultimo ingreso</p>
                    <p>{{ optional($aportante->ultimo_ingreso_at)->format('d/m/Y H:i') ?: 'Primer ingreso registrado' }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2">
            @forelse ($votaciones as $votacion)
                @php
                    $registro = $registros->get($votacion->id);
                    $yaVoto = filled($registro?->voto_emitido_at);
                    $acepto = $aceptoAgendaGlobal || filled($registro?->acepto_orden_dia_at);
                    $abierta = $votacion->estaAbiertaAhora();
                @endphp
                <article class="vote-card rounded-[30px] p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <span class="badge-soft {{ $votacion->tipo_votacion === 'planilla' ? 'orange' : 'blue' }}">
                                    {{ $votacion->tipo_votacion === 'planilla' ? 'Plancha' : 'Nominal' }}
                                </span>
                                @if($yaVoto)
                                    <span class="badge-soft green">Voto registrado</span>
                                @elseif(!$abierta)
                                    <span class="badge-soft red">No disponible ahora</span>
                                @elseif($acepto)
                                    <span class="badge-soft blue">Lista para votar</span>
                                @else
                                    <span class="badge-soft orange">Pendiente aceptar orden del dia</span>
                                @endif
                            </div>
                            <h3 class="mt-4 text-2xl font-extrabold text-slate-900">{{ $votacion->titulo }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ $votacion->descripcion_publica ?: 'Votacion habilitada para tu participacion.' }}</p>
                        </div>

                        <div class="mini-logo-card">
                            <img src="{{ $votacion->logo_url }}" alt="Logo {{ $votacion->titulo }}">
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                        <div class="rounded-[20px] bg-slate-50 px-4 py-3">
                            <p class="font-bold text-slate-900">{{ $votacion->tipo_votacion === 'planilla' ? 'Planchas postuladas' : 'Total Puestos' }}</p>
                            <p>
                                @if($votacion->tipo_votacion === 'planilla')
                                    {{ $votacion->planillas_count }} plancha(s) disponibles
                                @else
                                    {{ $votacion->cupos }} persona(s) a elegir
                                @endif
                            </p>
                        </div>
                        <div class="rounded-[20px] bg-slate-50 px-4 py-3">
                            <p class="font-bold text-slate-900">Participacion actual</p>
                            <p>{{ $votacion->votos_emitidos_count }} voto(s) registrados</p>
                        </div>
                        <div class="rounded-[20px] bg-slate-50 px-4 py-3">
                            <p class="font-bold text-slate-900">Inicio</p>
                            <p>{{ $votacion->fecha_inicio?->format('d/m/Y H:i') ?: 'Sin definir' }}</p>
                        </div>
                        <div class="rounded-[20px] bg-slate-50 px-4 py-3">
                            <p class="font-bold text-slate-900">Cierre</p>
                            <p>{{ $votacion->fecha_fin?->format('d/m/Y H:i') ?: 'Sin definir' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        @if ($yaVoto)
                            <div class="rounded-[22px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm leading-7 text-emerald-950">
                                Tu participacion ya quedo registrada el {{ $registro->voto_emitido_at->format('d/m/Y H:i') }}.
                            </div>
                            <a href="{{ route('votaciones.portal.resultados', $votacion) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-[24px] bg-emerald-600 px-6 py-3 text-lg font-bold text-white transition-all hover:bg-emerald-700">
                                Ver resultados
                            </a>
                        @elseif (!$abierta)
                            <div class="rounded-[22px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm leading-7 text-rose-950">
                                Esta votacion no se encuentra abierta en este momento. Si aun no inicia, aqui mismo se habilitara cuando llegue la fecha.
                            </div>
                            @if($votacion->estado === 'cerrada')
                                <a href="{{ route('votaciones.portal.resultados', $votacion) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-[24px] bg-slate-800 px-6 py-3 text-lg font-bold text-white transition-all hover:bg-slate-900">
                                    Ver resultados detallados
                                </a>
                            @endif
                        @elseif ($votacion->aceptacion_obligatoria && !$acepto)
                            <a href="{{ route('votaciones.portal.agenda', $votacion) }}" class="primary-btn inline-flex w-full items-center justify-center px-6 text-lg">
                                Leer y aceptar orden del dia
                            </a>
                        @else
                            <a href="{{ route('votaciones.portal.vote.form', $votacion) }}" class="primary-btn inline-flex w-full items-center justify-center px-6 text-lg">
                                Ir a votar ahora
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <article class="surface-card rounded-[30px] p-8 text-center">
                    <span class="badge-soft blue">Sin votaciones activas</span>
                    <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Aun no hay procesos publicados</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600">Cuando inicien las votaciones, las veras aqui listadas junto a su orden del dia y el boton para participar.</p>
                </article>
            @endforelse
        </section>
    </div>
@endsection
