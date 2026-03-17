@extends('votaciones.layout', [
    'title' => 'Ingreso de participantes',
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
                    <p class="mt-2 text-sm leading-7 text-slate-600">Usa tu numero de documento. Por seguridad, la primera vez tu clave es el mismo documento.</p>
                </div>
                <div class="rounded-[24px] bg-slate-50 p-5">
                    <p class="text-sm font-black uppercase tracking-[.2em] text-slate-400">Paso 2</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Lee y acepta</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Antes de votar, deberas leer y aceptar el orden del dia para continuar.</p>
                </div>
                <div class="rounded-[24px] bg-slate-50 p-5">
                    <p class="text-sm font-black uppercase tracking-[.2em] text-slate-400">Paso 3</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Vota</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Elige tu opcion preferida y registra tu voto de forma segura.</p>
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

                <div class="mt-8 grid gap-4 grid-cols-1 sm:grid-cols-3">
                    <article class="rounded-[22px] border border-slate-200 bg-white p-5 text-center">
                        <p class="text-xs font-bold uppercase tracking-[.1em] text-slate-400">Votaciones</p>
                        <h4 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total_procesos'] }}</h4>
                        <p class="mt-1 text-sm text-slate-600 font-medium">Activas hoy</p>
                    </article>
                    <article class="rounded-[22px] border border-slate-200 bg-white p-5 text-center">
                        <p class="text-xs font-bold uppercase tracking-[.1em] text-slate-400">Participantes</p>
                        <h4 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total_participantes'] }}</h4>
                        <p class="mt-1 text-sm text-slate-600 font-medium">Habilitados</p>
                    </article>
                    <article class="rounded-[22px] border border-slate-200 bg-white p-5 text-center">
                        <p class="text-xs font-bold uppercase tracking-[.1em] text-slate-400">Votos</p>
                        <h4 class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total_registrados'] }}</h4>
                        <p class="mt-1 text-sm text-slate-600 font-medium">Procesados</p>
                    </article>
                </div>

                <div class="mt-8 rounded-[22px] bg-slate-100 p-5">
                    <p class="text-sm font-bold text-slate-900">Procesos en curso</p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($votaciones as $v)
                            <li class="flex items-center gap-3 text-sm text-slate-600">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="font-semibold text-slate-800">{{ $v->titulo }}</span>
                            </li>
                        @endforeach
                    </ul>
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
                    <strong>Recuerda:</strong> Al ingresar, si es tu primera vez la clave inicial corresponde a tu mismo numero de documento.
                </div>

                <button class="primary-btn w-full px-6 text-lg">Ingresar al portal</button>
            </form>
        </section>
    </div>
@endsection
