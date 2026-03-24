@extends('tarjeta-digital.layout', ['title' => 'Portal Tarjeta Digital'])

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <section class="glass-card rounded-[30px] p-6 md:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-sky-800">
                        Validacion simple
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-[-0.03em] text-slate-900 md:text-3xl">
                        Ingresa los datos del credito
                    </h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600 md:text-base">
                        Para proteger la descarga no usamos usuario y contrasena. Validamos tu documento, el numero del credito y la fecha de pago de la tarjeta.
                    </p>
                </div>

                <div class="hidden h-16 w-16 shrink-0 place-items-center rounded-[22px] bg-sky-100 text-3xl lg:grid">
                    01
                </div>
            </div>

            <form method="POST" action="{{ route('tarjeta-digital.portal.validate') }}" class="mt-8 space-y-5">
                @csrf

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Documento</span>
                    <input
                        type="text"
                        name="documento"
                        value="{{ old('documento') }}"
                        inputmode="numeric"
                        autocomplete="off"
                        placeholder="Ej. 123456789"
                        class="field-shell w-full rounded-[18px] px-4 py-4 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
                    >
                    @error('documento')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Numero de credito</span>
                    <input
                        type="text"
                        name="credito"
                        value="{{ old('credito') }}"
                        autocomplete="off"
                        placeholder="Ej. 10023456"
                        class="field-shell w-full rounded-[18px] px-4 py-4 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
                    >
                    @error('credito')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Fecha de pago</span>
                    <input
                        type="date"
                        name="fecha_vigencia"
                        value="{{ old('fecha_vigencia') }}"
                        class="field-shell w-full rounded-[18px] px-4 py-4 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
                    >
                    @error('fecha_vigencia')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit" class="primary-btn w-full px-6 text-base">
                    Validar y continuar
                </button>
            </form>
        </section>

        <section class="space-y-6">
            <div class="glass-card rounded-[30px] p-6 md:p-8">
                <h3 class="text-xl font-bold tracking-[-0.03em] text-slate-900">
                    Como funciona
                </h3>

                <div class="mt-6 space-y-4">
                    <div class="rounded-[24px] bg-slate-50 px-5 py-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Paso 1</span>
                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            Diligencias tres datos del credito que ya conoces.
                        </p>
                    </div>

                    <div class="rounded-[24px] bg-slate-50 px-5 py-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Paso 2</span>
                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            Si la informacion coincide, activamos una sesion corta de {{ $accessTtlMinutes }} minutos para la descarga.
                        </p>
                    </div>

                    <div class="rounded-[24px] bg-slate-50 px-5 py-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Seguridad</span>
                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            El portal limita los intentos repetidos y no deja acceso permanente a la tarjeta.
                        </p>
                    </div>
                </div>
            </div>

            @if ($hasActiveAccess)
                <div class="glass-card rounded-[30px] border-sky-200 bg-sky-50/90 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-900">
                        Tienes una validacion activa
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-700">
                        Si ya validaste tus datos en este dispositivo, puedes ir directo a la pantalla de descarga.
                    </p>
                    <a href="{{ route('tarjeta-digital.portal.show') }}" class="primary-btn mt-5 inline-flex items-center justify-center px-6">
                        Continuar con la descarga
                    </a>
                </div>
            @endif
        </section>
    </div>
@endsection
