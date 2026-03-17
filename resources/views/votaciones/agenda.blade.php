@extends('votaciones.layout', [
    'title' => $votacion->titulo,
    'subtitle' => 'Paso 2 de 3. Lee el orden del dia y marca la aceptacion para habilitar el voto.',
    'step' => 2,
    'aportante' => $aportante,
    'logoUrl' => $votacion->logo_url,
])

@section('content')
    <div class="grid gap-6 mobile-stack md:grid-cols-[.9fr_1.1fr]">
        <section class="surface-card rounded-[30px] p-6 md:p-8">
            <span class="badge-soft orange">Confirmacion previa</span>
            <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Acepta el orden del dia</h2>
            <p class="mt-2 text-sm leading-7 text-slate-600">Este paso es importante para que conozcas los detalles del proceso. Una vez aceptes, el sistema te mostrara las opciones de votacion para que registres tu eleccion.</p>

            <div class="mt-6 grid gap-4">
                <div class="rounded-[22px] bg-slate-50 px-5 py-4">
                    <p class="text-sm font-bold text-slate-900">Modo de eleccion</p>
                    <p class="mt-1 text-slate-600">{{ $votacion->tipo_votacion === 'planilla' ? 'Voto por Plancha (Lista)' : 'Voto individual (Candidatos)' }}</p>
                </div>
                <div class="rounded-[22px] bg-slate-50 px-5 py-4">
                    <p class="text-sm font-bold text-slate-900">Cupos disponibles</p>
                    <p class="mt-1 text-slate-600">{{ $votacion->cupos }} persona(s) a elegir</p>
                </div>
                <div class="rounded-[22px] bg-slate-50 px-5 py-4">
                    <p class="text-sm font-bold text-slate-900">Horario de participacion</p>
                    <p class="mt-1 text-slate-600">
                        {{ $votacion->fecha_inicio?->format('d/m/Y H:i') ?: 'Desde este momento' }}
                        al
                        {{ $votacion->fecha_fin?->format('d/m/Y H:i') ?: 'Hasta nuevo aviso' }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('votaciones.portal.agenda.accept', $votacion) }}" class="mt-8 space-y-5">
                @csrf
                <label class="flex items-start gap-4 rounded-[22px] border border-slate-200 bg-slate-50 px-5 py-5">
                    <input type="checkbox" name="acepta_orden_dia" value="1" class="mt-1 h-6 w-6 rounded border-slate-300 text-teal-700 focus:ring-teal-600" required>
                    <span class="text-sm leading-7 text-slate-700">
                        Confirmo que he leido el orden del dia y deseo continuar para registrar mi voto.
                    </span>
                </label>

                <button class="primary-btn w-full px-6 text-lg">Confirmar y continuar</button>
            </form>
        </section>

        <section class="surface-card rounded-[30px] p-6 md:p-8">
            <span class="badge-soft blue">Orden del dia</span>
            <div class="order-day-content mt-5 space-y-4 rounded-[24px] bg-slate-50 p-5">
                {!! $votacion->orden_del_dia ?: '<p>La administracion aun no ha cargado el orden del dia para este proceso.</p>' !!}
            </div>
        </section>
    </div>
@endsection
