@extends('tarjeta-digital.layout', ['title' => 'Descargar Tarjeta Digital'])

@section('content')
    @php
        $nombreCompleto = trim(($record->nombres ?? '') . ' ' . ($record->apellidos ?? ''));
        $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : 'Cliente Actuar';
        $documento = (string) $record->cc;
        $maskedDocumento = strlen($documento) > 4
            ? str_repeat('*', max(strlen($documento) - 4, 0)) . substr($documento, -4)
            : $documento;
        $valorTarjeta = $record->valor_cuota ?? $record->valor_reportar ?? 0;
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="glass-card rounded-[30px] p-6 md:p-8">
            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-800">
                Consulta confirmada
            </span>

            <h2 class="mt-4 text-2xl font-bold tracking-[-0.03em] text-slate-900 md:text-3xl">
                Tu tarjeta digital esta lista para descargar
            </h2>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 md:text-base">
                Durante los proximos {{ $accessTtlMinutes }} minutos podras descargar tu tarjeta desde este dispositivo.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cliente</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $nombreCompleto }}</p>
                </div>

                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documento</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $maskedDocumento }}</p>
                </div>

                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Credito</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $record->obligacion }}</p>
                </div>

                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Valor de la cuota</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">${{ number_format((float) $valorTarjeta, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                <a href="{{ route('tarjeta-digital.portal.download') }}" class="primary-btn inline-flex items-center justify-center px-6 text-base">
                    Descargar mi tarjeta
                </a>

                <form method="POST" action="{{ route('tarjeta-digital.portal.logout') }}">
                    @csrf
                    <button type="submit" class="secondary-btn w-full px-6 text-base sm:w-auto">
                        Hacer otra consulta
                    </button>
                </form>
            </div>
        </section>

        <section class="glass-card rounded-[30px] p-6 md:p-8">
            <div class="rounded-[28px] bg-[linear-gradient(135deg,#0b213c_0%,#12365f_45%,#1d5fa8_100%)] p-6 text-white shadow-[0_24px_50px_rgba(11,33,60,0.22)]">
                <span class="inline-flex rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/85">
                    Resumen
                </span>

                <div class="mt-6">
                    <p class="text-sm text-white/75">Valor de la tarjeta</p>
                    <p class="mt-2 text-4xl font-extrabold tracking-[-0.05em]">
                        ${{ number_format((float) $valorTarjeta, 0, ',', '.') }}
                    </p>
                </div>

                <div class="mt-6 space-y-3 text-sm text-white/80">
                    <p>La descarga se realizara como una imagen.</p>
                    <p>Puedes guardarla en tu celular o compartirla cuando la necesites.</p>
                    <p>Si el acceso vence, solo debes volver a hacer la consulta.</p>
                </div>
            </div>

            <div class="mt-6 rounded-[24px] bg-slate-50 px-5 py-5">
                <h3 class="text-base font-bold text-slate-900">Sugerencia</h3>
                <p class="mt-3 text-sm leading-6 text-slate-700">
                    Descarga tu tarjeta ahora y mantenla guardada en tu dispositivo para tenerla disponible cuando la necesites.
                </p>
            </div>
        </section>
    </div>
@endsection
