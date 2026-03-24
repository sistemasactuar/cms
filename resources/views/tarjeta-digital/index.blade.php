@extends('tarjeta-digital.layout', ['title' => 'Portal Tarjeta Digital'])

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <section class="glass-card rounded-[30px] p-6 md:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-sky-800">
                        Consulta en linea
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-[-0.03em] text-slate-900 md:text-3xl">
                        Consulta tu tarjeta digital
                    </h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600 md:text-base">
                        Ingresa los datos de tu credito para encontrar tu tarjeta y descargarla en pocos pasos.
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
                    <span class="mb-2 block text-sm font-semibold text-slate-700">Valor de tu cuota</span>
                    <input
                        type="text"
                        name="valor_cuota"
                        value="{{ old('valor_cuota') }}"
                        inputmode="decimal"
                        autocomplete="off"
                        placeholder="Ej. 185000"
                        class="field-shell w-full rounded-[18px] px-4 py-4 text-sm text-slate-900 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
                    >
                    @error('valor_cuota')
                        <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <button type="submit" class="primary-btn w-full px-6 text-base">
                    Buscar mi tarjeta
                </button>
            </form>
        </section>

        <section class="space-y-6">
            <div class="glass-card rounded-[30px] p-6 md:p-8">
                <h3 class="text-xl font-bold tracking-[-0.03em] text-slate-900">
                    Lo que necesitas
                </h3>

                <div class="mt-6 space-y-4">
                    <div class="rounded-[24px] bg-slate-50 px-5 py-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Ten a la mano</span>
                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            Tu documento, el numero del credito y el valor de la cuota.
                        </p>
                    </div>

                    <div class="rounded-[24px] bg-slate-50 px-5 py-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Luego</span>
                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            Si la informacion coincide, podras ver y descargar tu tarjeta desde este mismo dispositivo.
                        </p>
                    </div>

                    <div class="rounded-[24px] bg-slate-50 px-5 py-4">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Importante</span>
                        <p class="mt-2 text-sm leading-6 text-slate-700">
                            Por seguridad, el acceso estara disponible durante {{ $accessTtlMinutes }} minutos.
                        </p>
                    </div>
                </div>
            </div>

            @if ($hasActiveAccess)
                <div class="glass-card rounded-[30px] border-sky-200 bg-sky-50/90 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-900">
                        Tu consulta sigue activa
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-700">
                        Si ya consultaste tu tarjeta en este dispositivo, puedes continuar sin volver a diligenciar los datos.
                    </p>
                    <a href="{{ route('tarjeta-digital.portal.show') }}" class="primary-btn mt-5 inline-flex items-center justify-center px-6">
                        Continuar
                    </a>
                </div>
            @endif
        </section>
    </div>
@endsection
