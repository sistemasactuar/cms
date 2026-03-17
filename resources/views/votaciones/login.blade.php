@extends('votaciones.layout', [
    'title' => 'Ingreso de aportantes',
    'subtitle' => 'Accede con tu documento y tu clave para revisar el orden del dia y votar sin perderte en el proceso.',
    'step' => 1,
])

@section('content')
    <div class="grid gap-6 mobile-stack md:grid-cols-[1.1fr_.9fr]">
        <section class="surface-card rounded-[30px] p-6 md:p-8">
            <span class="badge-soft orange">Antes de ingresar</span>
            <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Asi funciona el proceso</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-[24px] bg-slate-50 p-5">
                    <p class="text-sm font-black uppercase tracking-[.2em] text-slate-400">Paso 1</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Ingresa</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Usa tu documento y la clave asignada. Si es tu primera vez, la clave inicial es el mismo documento.</p>
                </div>
                <div class="rounded-[24px] bg-slate-50 p-5">
                    <p class="text-sm font-black uppercase tracking-[.2em] text-slate-400">Paso 2</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Lee y acepta</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Antes de votar veras el orden del dia y deberas marcar la aceptacion para continuar.</p>
                </div>
                <div class="rounded-[24px] bg-slate-50 p-5">
                    <p class="text-sm font-black uppercase tracking-[.2em] text-slate-400">Paso 3</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Vota</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Entra, elige tu opcion y vota.</p>
                </div>
            </div>

            <div class="mt-8 rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[.18em] text-slate-500">Votaciones disponibles</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Resumen publicado</h3>
                    </div>
                    <span class="badge-soft green">{{ $votaciones->count() }} activas</span>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($votaciones as $votacion)
                        <article class="rounded-[22px] border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">{{ $votacion->titulo }}</h4>
                                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ $votacion->descripcion_publica ?: 'Votacion lista para participacion de aportantes.' }}</p>
                                </div>
                                <div class="space-y-2 text-right">
                                    <span class="badge-soft {{ $votacion->tipo_votacion === 'planilla' ? 'orange' : 'blue' }}">
                                        {{ $votacion->tipo_votacion === 'planilla' ? 'Planilla' : 'Nominal' }}
                                    </span>
                                    <p class="text-sm font-semibold text-slate-500">{{ $votacion->cupos }} cupo(s)</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-500">
                                <span>{{ $votacion->candidatos_count }} candidato(s)</span>
                                <span>{{ $votacion->planillas_count }} planilla(s)</span>
                                @if ($votacion->fecha_inicio)
                                    <span>Inicia {{ $votacion->fecha_inicio->format('d/m/Y H:i') }}</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[22px] border border-dashed border-slate-300 bg-white p-6 text-center text-slate-500">
                            En este momento no hay votaciones publicadas para aportantes.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="surface-card rounded-[30px] p-6 md:p-8">
            <span class="badge-soft blue">Ingreso seguro</span>
            <h2 class="mt-4 text-2xl font-extrabold text-slate-900">Entra al portal</h2>
            <p class="mt-2 text-sm leading-7 text-slate-600">Si necesitas ayuda, verifica primero que estes digitando bien tu documento. El acceso fue pensado para que puedas hacerlo sin complicaciones desde el celular.</p>

            <form method="POST" action="{{ route('votaciones.portal.authenticate') }}" class="mt-8 space-y-5">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-slate-700">Documento</span>
                    <input
                        type="text"
                        name="documento"
                        value="{{ old('documento') }}"
                        class="w-full rounded-[18px] border border-slate-200 px-4 py-4 text-lg shadow-sm outline-none transition focus:border-sky-700 focus:ring-4 focus:ring-sky-100"
                        placeholder="Escribe tu documento"
                        autocomplete="username"
                        required>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-slate-700">Contrasena</span>
                    <input
                        type="password"
                        name="password"
                        class="w-full rounded-[18px] border border-slate-200 px-4 py-4 text-lg shadow-sm outline-none transition focus:border-sky-700 focus:ring-4 focus:ring-sky-100"
                        placeholder="Escribe tu contrasena"
                        autocomplete="current-password"
                        required>
                </label>

                <div class="rounded-[20px] bg-amber-50 px-4 py-4 text-sm leading-7 text-amber-950">
                    <strong>Recuerda:</strong> al crear un aportante, la clave inicial corresponde al mismo documento.
                </div>

                <button class="primary-btn w-full px-6 text-lg">Ingresar al portal</button>
            </form>
        </section>
    </div>
@endsection
